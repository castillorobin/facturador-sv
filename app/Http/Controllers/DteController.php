<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Product;

use App\Models\Dte;
use App\Models\Dte_item;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Services\DteService;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DteController extends Controller
{
    public function index()
    {
        // Cargamos los DTEs con su cliente para evitar múltiples consultas (Eager Loading)
        $dtes = Dte::with('customer')
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('dtes.index', compact('dtes'));
    }
    public function create()
    {
        $customers = Customer::orderBy('nombre')->get();
        $products = Product::orderBy('nombre')->get();
        
        return view('dtes.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        // 1. Validar la entrada
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'tipo_dte' => 'required|string',
            'items' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            // 2. Generar Correlativo Oficial (Número de Control MH)
            $establecimiento = "M001"; // Esto podría venir de la tabla 'companies' después
            $puntoVenta = "P001";

            // Buscamos el último DTE de este tipo para esta empresa
            $ultimoDte = Dte::where('tipo_dte', $request->tipo_dte)
                            ->where('company_id', auth()->user()->company_id)
                            ->latest()
                            ->first();

            // Extraemos los últimos 15 dígitos del número de control anterior para sumar 1
            $correlativoNumero = $ultimoDte ? (int) substr($ultimoDte->numero_control, -15) + 1 : 1;

            // Formateamos: DTE - Tipo - EstablecimientoPuntoVenta - 15 dígitos
            $numeroControl = "DTE-" . 
                            $request->tipo_dte . "-" . 
                            $establecimiento . $puntoVenta . "-" . 
                            str_pad($correlativoNumero, 15, '0', STR_PAD_LEFT);

            // 3. Crear la Cabecera del DTE
            $dte = Dte::create([
                'company_id' => auth()->user()->company_id, // Multitenant
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'tipo_dte' => $request->tipo_dte,
                'codigo_generacion' => strtoupper(Str::uuid()), // UUID v4 en mayúsculas
                'numero_control' => $numeroControl,
                'fecha_emision' => Carbon::now(),
                'estado' => 'BORRADOR',
                'total_pagar' => 0, // Lo calcularemos sumando los ítems
            ]);

            $totalGravado = 0;
            $totalExento = 0;
            $totalIva = 0;

            // 4. Guardar los Ítems y calcular totales
            foreach ($request->items as $item) {
                $subtotal = $item['cantidad'] * $item['precio_unitario'];
                
                // Lógica de impuestos: buscamos si el producto original es exento
                // (Podrías pasar 'es_exento' en el request o consultarlo aquí)
                $esExento = \App\Models\Product::find($item['product_id'])->es_exento;

                if ($esExento) {
                    $ventaExenta = $subtotal;
                    $ventaGravada = 0;
                    $totalExento += $ventaExenta;
                } else {
                    $ventaExenta = 0;
                    // Desglose de IVA (13%)
                    $base = $subtotal / 1.13;
                    $iva = $subtotal - $base;
                    $ventaGravada = $base;
                    
                    $totalGravado += $base;
                    $totalIva += $iva;
                }

                Dte_item::create([
                    'dte_id' => $dte->id,
                    'descripcion' => \App\Models\Product::find($item['product_id'])->nombre,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'venta_gravada' => $ventaGravada,
                    'venta_exenta' => $ventaExenta,
                ]);
            }

            // 5. Actualizar totales finales en la cabecera
            $dte->update([
                'monto_gravado' => $totalGravado,
                'monto_exento' => $totalExento,
                'iva' => $totalIva,
                'total_pagar' => $totalGravado + $totalExento + $totalIva,
            ]);

            DB::commit();

            return redirect()->route('dtes.index')
                ->with('success', 'DTE generado exitosamente con el número: ' . $numeroControl);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error al generar el DTE: ' . $e->getMessage())->withInput();
        }
    }

    public function enviarAHacienda($id, DteService $dteService)
{
    $dte = Dte::findOrFail($id);
    
    // Selección dinámica de estructura y versión
    if ($dte->tipo_dte === '03') {
        $dteJson = $dteService->generarEstructura03($dte);
        $version = 3;
    } else {
        $dteJson = $dteService->generarEstructura01($dte);
        $version = 1;
    }

    $payload = [
        'Usuario' => "032267824",
        'Password' => "Alexan24.",
        'Ambiente' => '00',
        'DteJson' => json_encode($dteJson),
        'Nit' => "05152308851012",
        'PasswordPrivado' => 'Pw6r$LbMw93',
        'TipoDte' => $dte->tipo_dte,
        'CodigoGeneracion' => $dte->codigo_generacion,
        'NumControl' => $dte->numero_control,
        'VersionDte' => $version,
        'CorreoCliente' => $dte->customer->email
    ];

    try {
        // Usamos el cliente HTTP de Laravel (más limpio que cURL)
        $response = Http::timeout(30)->post('http://163.245.212.103:7122/api/procesar-dte', $payload);

        if ($response->successful()) {
            $respuestaAPI = $response->object(); // Usamos object para acceder como $respuestaAPI->campo

            // 1. Datos de la respuesta (usando tus mismos nombres de variable)
            $codigoGeneracion = $respuestaAPI->codigoGeneracion ?? $dte->codigo_generacion;
            $numControl       = $respuestaAPI->numControl ?? $dte->numero_control;
            $selloRecibido    = $respuestaAPI->selloRecibido ?? $respuestaAPI->SelloRecepcion ?? null;
            $jwsFirmado       = $respuestaAPI->dteFirmado ?? null;

            // 2. Construir el JSON LEGIBLE (basado en el array original)
            $legible = $dteJson; 
            $legible['identificacion']['codigoGeneracion'] = $codigoGeneracion;
            
            if ($numControl) {
                $legible['identificacion']['numeroControl'] = $numControl;
            }

            // 3. Ordenar: Primero firmaElectronica, luego selloRecibido al final
            if ($jwsFirmado) {
                $legible['firmaElectronica'] = $jwsFirmado;
            }

            if ($selloRecibido) {
                unset($legible['selloRecibido']); // Limpiamos por si existe
                // Usamos array_merge para forzar que el sello sea el último campo del JSON
                $legible = array_merge($legible, ['selloRecibido' => $selloRecibido]);
            }

            // 4. Guardar físicamente con el formato exacto que pide el contador
            $rutaLegible = "dtes_json/legible_{$codigoGeneracion}.json";
            
            // IMPORTANTE: UNESCAPED_UNICODE para tildes y UNESCAPED_SLASHES para la firma JWS
            Storage::put($rutaLegible, json_encode($legible, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            // 5. Persistir en Base de Datos
            $dte->update([
                'estado' => 'PROCESADO',
                'sello_recepcion' => $selloRecibido,
                'ruta_json' => $rutaLegible,
            ]);

            return back()->with('success', 'DTE Procesado y JSON Legible generado correctamente.');
        }
//dd($dteJson);
        return back()->withErrors('Error de Hacienda: ' . $response->body());

    } catch (\Exception $e) {
        return back()->withErrors('Error de conexión: ' . $e->getMessage());
    }
}

    public function downloadJson($id)
    {
        $dte = Dte::findOrFail($id);

        // Verificamos si el campo ruta_json tiene información
        if (!$dte->ruta_json || !Storage::exists($dte->ruta_json)) {
            return back()->withErrors('El archivo JSON no existe o aún no ha sido generado.');
        }

        // Obtenemos el contenido del archivo
        $fileContent = Storage::get($dte->ruta_json);
        
        // Definimos un nombre para el archivo de descarga
        $fileName = "DTE_" . ($dte->numero_control ?? $dte->codigo_generacion) . ".json";

        // Retornamos la descarga
        return response($fileContent, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function verPdf($id)
        {
            $dte = Dte::findOrFail($id);

            if ($dte->estado !== 'PROCESADO' || !Storage::exists($dte->ruta_json)) {
                return back()->withErrors('El DTE aún no tiene un JSON procesado.');
            }

            // 1. Obtener el contenido del JSON legible
            $jsonContent = Storage::get($dte->ruta_json);

            // 2. Construir el payload que espera tu API de C#
            $payload = [
                'dteJson'       => $jsonContent,
                'selloRecibido' => $dte->sello_recepcion,
                'tipoDte'       => '01', // O $dte->tipo_dte
                'carrera'       => '-',
                'observacion'   => '-'
            ];

            try {
                // 3. Enviamos el POST directamente con el array. 
                // Laravel se encarga de convertirlo a JSON y poner los Headers correctos.
                $response = Http::timeout(60)->post('http://163.245.212.103:7122/api/generar-pdf', $payload);

                if ($response->successful()) {
                    return response($response->body(), 200)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'inline; filename="Factura-'.$dte->numero_control.'.pdf"');
                }

                return back()->withErrors('Error de la API de PDF ('.$response->status().'): ' . $response->body());

            } catch (\Exception $e) {
                return back()->withErrors('Error de conexión: ' . $e->getMessage());
            }
        }
}
