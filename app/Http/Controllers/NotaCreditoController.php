<?php

namespace App\Http\Controllers;
use App\Models\Dte;
use App\Services\DteService;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Storage;


use Illuminate\Http\Request;

class NotaCreditoController extends Controller
{
    public function create(Request $request)
{
    // Usamos el nombre de la función que definiste en el modelo Dte
    $dteOriginal = Dte::with(['customer', 'details.product'])->findOrFail($request->dte_id);
    
    if($dteOriginal->tipo_dte != '03'){
        return back()->withErrors('Solo se pueden aplicar Notas de Crédito a Créditos Fiscales.');
    }

    return view('notas.create', compact('dteOriginal'));
}
public function store(Request $request, DteService $dteService)
{
    $request->validate([
        'dte_origen_id' => 'required|exists:dtes,id',
        'monto_ajuste' => 'required|numeric|min:0.01',
        'motivo_detalle' => 'required|string|min:5',
    ]);

    $dteOriginal = \App\Models\Dte::with('customer')->findOrFail($request->dte_origen_id);
    
    // Cálculo de montos
    $montoGravado = round($request->monto_ajuste, 2);
    $iva = round($montoGravado * 0.13, 2);
    $totalNota = $montoGravado + $iva;

    $itemsParaHacienda = [
        [
            "numItem" => 1,
            "tipoItem" => 2, 
            "cantidad" => 1,
            "codigo" => "AJUSTE",
            "descripcion" => $request->motivo_detalle,
            "precioUni" => $montoGravado,
            "montoDescu" => 0,
            "ventaNoGravada" => 0,
            "ventaExenta" => 0,
            "ventaGravada" => $montoGravado,
            "tributos" => ["20"],
            "noGravado" => 0
        ]
    ];

    try {
        // 1. Generar Estructura JSON
        $jsonNota = $dteService->generarEstructuraNotaCreditoManual($dteOriginal, $itemsParaHacienda, $totalNota);

        // 2. Crear el registro en la BD como BORRADOR antes de enviar
        $nuevaNota = \App\Models\Dte::create([
            'company_id' => auth()->user()->company_id,
            'customer_id' => $dteOriginal->customer_id,
            'user_id' => auth()->id(),
            'tipo_dte' => '05',
            'fecha_emision' => now(),
            'codigo_generacion' => $jsonNota['identificacion']['codigoGeneracion'],
            'numero_control' => $jsonNota['identificacion']['numeroControl'],
            'total_pagar' => $totalNota,
            'estado' => 'BORRADOR',
            'dte_origen_id' => $dteOriginal->id, // Para saber a qué CCF afecta
        ]);
$company = $dteOriginal->company;
        // 3. Preparar Payload para la API
        $payload = [
            'Usuario' => $company->api_usuario,
            'Password' => $company->api_password,
            'Ambiente' => $company->ambiente,
            'DteJson' => json_encode($jsonNota),
            'Nit' => $company->nit,
            'PasswordPrivado' => $company->password_privado,
            'TipoDte' => '05',
            'CodigoGeneracion' => $nuevaNota->codigo_generacion,
            'NumControl' => $nuevaNota->numero_control,
            'VersionDte' => 3, // Notas de Crédito suelen ser versión 3
            'CorreoCliente' => $dteOriginal->customer->email
        ];

        // 4. Enviar a la API
        $response = Http::timeout(60)->post('http://163.245.212.103:7122/api/procesar-dte', $payload);

        if ($response->successful()) {
            $respuestaAPI = $response->object();

            $selloRecibido = $respuestaAPI->selloRecibido ?? $respuestaAPI->SelloRecepcion ?? null;
            $jwsFirmado = $respuestaAPI->dteFirmado ?? null;

            // 5. Construir JSON Legible
            $legible = $jsonNota;
            if ($jwsFirmado) { $legible['firmaElectronica'] = $jwsFirmado; }
            if ($selloRecibido) {
                $legible = array_merge($legible, ['selloRecibido' => $selloRecibido]);
            }

            // 6. Guardar archivo físico en la carpeta private
            $rutaLegible = "dtes_json/legible_{$nuevaNota->codigo_generacion}.json";
            Storage::put($rutaLegible, json_encode($legible, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            // 7. Actualizar Nota a PROCESADO
            $nuevaNota->update([
                'estado' => 'PROCESADO',
                'sello_recepcion' => $selloRecibido,
                'ruta_json' => $rutaLegible,
                //'json_legible_path' => $rutaLegible // Usamos el campo que definimos para el ZIP
            ]);

            return redirect()->route('dtes.index')->with('success', 'Nota de Crédito procesada y enviada a Hacienda correctamente.');
        }

        return back()->withErrors('Error de Hacienda: ' . $response->body());

    } catch (\Exception $e) {
        return back()->withErrors("Error en el proceso: " . $e->getMessage());
    }
}

}
