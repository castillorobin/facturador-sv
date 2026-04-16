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
    public function index(Request $request)
    {
        // Si no vienen fechas en el request, usamos la fecha de hoy para AMBAS
    $fechaInicio = $request->get('fecha_inicio', date('Y-m-d'));
    $fechaFin = $request->get('fecha_fin', date('Y-m-d'));

    // Consultar los DTEs filtrados
    $dtes = Dte::with('customer')
        ->whereBetween('fecha_emision', [
            $fechaInicio . ' 00:00:00',
            $fechaFin . ' 23:59:59'
        ])
        ->orderBy('id', 'desc')
        ->get();

    return view('dtes.index', compact('dtes', 'fechaInicio', 'fechaFin'));
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
            $company = auth()->user()->company;
            // 3. Crear la Cabecera del DTE
            $dte = Dte::create([
                'company_id' => auth()->user()->company_id, // Multitenant
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'tipo_dte' => $request->tipo_dte,
                'codigo_generacion' => strtoupper(Str::uuid()), // UUID v4 en mayúsculas
                'numero_control' => $numeroControl,
                'fecha_emision' => Carbon::now(),
                'tipo_modelo' => $company->modo_contingencia ? 2 : 1,
                'estado' => $company->modo_contingencia ? 'CONTINGENCIA' : 'BORRADOR',
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
    $dte = Dte::with('company', 'customer')->findOrFail($id);
    $company = $dte->company;
    
    // Selección dinámica de estructura y versión
    if ($dte->tipo_dte === '03') {
        $dteJson = $dteService->generarEstructura03($dte);
        $version = 3;
    } elseif ($dte->tipo_dte === '14') { 
        $dteJson = $dteService->generarEstructura14($dte);
        $version = 1;
    }else {
        $dteJson = $dteService->generarEstructura01($dte);
        $version = 1;
    } 
 
    $payload = [
        'Usuario'          => (string) $company->api_usuario,   // Antes estático: 032267824
        'Password'         => (string) $company->api_password,  // Antes estático: Alexan24.
        'Ambiente'         => (string) $company->ambiente,      // '00' para Pruebas, '01' para Producción
        'DteJson'          => json_encode($dteJson),
        'Nit'              => (string) $company->nit,                       // Antes estático: 05152308851012
        'PasswordPrivado'  => (string) $company->password_privado, // Antes estático: Pw6r$LbMw93
        'TipoDte'          => $dte->tipo_dte,
        'CodigoGeneracion' => $dte->codigo_generacion,
        'NumControl'       => $dte->numero_control,
        'VersionDte'       => $version,
        'CorreoCliente'    => $dte->customer->email 
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

            $jsonContent = Storage::get($dte->ruta_json);

            // IMPORTANTE: Para el PDF, la API de C# necesita saber si es 01 o 03
            // para elegir la plantilla correcta.
            $payload = [
                'dteJson'       => $jsonContent,
                'selloRecibido' => $dte->sello_recepcion,
                'tipoDte'       => $dte->tipo_dte, // Ahora usamos el valor real (01, 03, 05, etc.)
                'carrera'       => '-',
                'observacion'   => '-'
            ];

            try {
                $response = Http::timeout(60)->post('http://163.245.212.103:7122/api/generar-pdf', $payload);

                if ($response->successful()) {
                    return response($response->body(), 200)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'inline; filename="DTE-'.$dte->numero_control.'.pdf"');
                }

                return back()->withErrors('Error de la API de PDF ('.$response->status().'): ' . $response->body());

            } catch (\Exception $e) {
                return back()->withErrors('Error de conexión: ' . $e->getMessage());
            }
        }

        public function reenviarCorreo($id)
            {
                $dte = Dte::findOrFail($id);

                if ($dte->estado !== 'PROCESADO' || !Storage::exists($dte->ruta_json)) {
                    return back()->withErrors('El DTE no tiene un registro procesado para enviar.');
                }

                // 1. Leer el JSON legible
                $jsonContent = Storage::get($dte->ruta_json);
                $dteArray = json_decode($jsonContent, true);

                // 2. Extraer la firma (firmaElectronica o firmaDocumento según tu JSON)
                $firma = $dteArray['firmaElectronica'] ?? $dteArray['firmaDocumento'] ?? '';

                // 3. Preparar el payload para la API de C#
                $payload = [
                    'dteJson'           => $jsonContent,
                    'selloRecibido'     => $dte->sello_recepcion,
                    'tipoDte'           => $dte->tipo_dte,
                    'correoCliente'     => $dte->customer->email,
                    'codigoGeneracion'  => $dte->codigo_generacion,
                    'firmaDocumento'    => $firma
                ];

                try {
                    $response = Http::timeout(60)->post('http://163.245.212.103:7122/api/enviar-correo', $payload);

                    if ($response->successful()) {
                        return back()->with('success', '¡Correo reenviado exitosamente al cliente!');
                    }

                    return back()->withErrors('Error al reenviar correo: ' . $response->body());

                } catch (\Exception $e) {
                    return back()->withErrors('Error de conexión con el servidor de correos: ' . $e->getMessage());
                }
            }

            public function invalidar(Request $request, $id, DteService $dteService)
                {
                    $dte = Dte::findOrFail($id);
                    
                    // Validar que no haya pasado el tiempo límite (generalmente 24-48h según MH)
                    if($dte->fecha_emision->diffInHours(now()) > 24) {
                        return back()->withErrors('El tiempo límite para invalidar este DTE ha expirado.');
                    }

                    $company = $dte->company;

                    $payloadInvalida = $dteService->generarEstructuraInvalidacion(
                        $dte, 
                        $request->motivo, 
                        "Robin Castillo", // O el usuario autenticado
                        "032267824"
                    );

                    $datosParaAPI = [
                        'Usuario' => $company->api_usuario, // O el usuario autenticado
                        'Password' => $company->api_password,
                        'Ambiente' => $company->ambiente,
                        'DteJson' => json_encode($payloadInvalida),
                        'Nit' => $company->nit,
                        'PasswordPrivado' => $company->password_privado,
                        'TipoDte' => '99', // El código para Invalidación es 99 en algunas APIs
                        'CodigoGeneracion' => $payloadInvalida['identificacion']['codigoGeneracion']
                    ];

                    try {
                        $response = Http::post('http://163.245.212.103:7122/api/anular-dte', $datosParaAPI);

                        if ($response->successful()) {
                            $res = $response->json();
                            $dte->update([
                                'estado' => 'ANULADO',
                                'sello_invalidacion' => $res['selloRecibido'] ?? null,
                                'fecha_invalidacion' => now(),
                                'motivo_invalidacion' => $request->motivo
                            ]);
                            return back()->with('success', 'DTE Invalidado correctamente ante Hacienda.');
                        }
                        
                        return back()->withErrors('Error de Hacienda: ' . $response->body());

                    } catch (\Exception $e) {
                        return back()->withErrors('Error de conexión: ' . $e->getMessage());
                    }
                }
                public function anular(Request $request, $id, DteService $dteService)
                {
                    $dte = Dte::findOrFail($id);
                    $motivo = $request->input('motivo');

                    // 1. Generamos el JSON de Invalidación (Estructura Tipo 01 de Invalidación)
                    $jsonInvalidacion = $dteService->generarEstructuraInvalidacion($dte, $request->input('motivo'));

                    $company = $dte->company;
                    // 2. Ajustamos el Payload según lo que la API espera para "Procesar"
                    $payload = [
                        'Usuario' => $company->api_usuario,
                        'Password' => $company->api_password,
                        'Ambiente' => $company->ambiente,
                        'DteJson' => json_encode($jsonInvalidacion),
                        'Nit' => $company->nit,
                        'PasswordPrivado' => $company->password_privado,
                        'TipoDte' => '99', // <--- Asegúrate que tu API reconozca 99 como "Invalidación"
                        'CodigoGeneracion' => $jsonInvalidacion['identificacion']['codigoGeneracion'], // El nuevo UUID
                        'NumControl' => null, // La invalidación no lleva número de control propio
                        'VersionDte' => 2,    // La invalidación suele ser Versión 2
                        'CorreoCliente' => $dte->customer->email
                    ];

                    try {
                        // Usamos post directo con el array
                        $response = Http::timeout(60)->post('http://163.245.212.103:7122/api/anular-dte', $payload);

                        if ($response->successful()) {
                            $data = $response->json();
                            
                            // 1. Definir la ruta del archivo
                            $nombreArchivo = "INVALIDACION_" . $dte->codigo_generacion . ".json";
                            $rutaParaBaseDatos = "dtes_json/" . $nombreArchivo;

                            // Guardamos el JSON de respuesta de Hacienda
                            Storage::put($rutaParaBaseDatos, $response->body()); 

                            // 2. Actualizar el DTE
                            $dte->update([
                                'estado' => 'ANULADO',
                                'sello_recepcion' => $data['selloRecibido'] ?? $dte->sello_recepcion,
                                'json_invalidacion_path' => $rutaParaBaseDatos, // <-- AQUÍ SE CORRIGIÓ EL NOMBRE
                                'fecha_invalidacion' => now(),
                                'motivo_invalidacion' => $motivo
                            ]);

                            return back()->with('success', 'Documento invalidado y guardado correctamente.');
                        }

                        // Si da error 400, imprimimos el cuerpo para ver qué faltó
                        return back()->withErrors('Error de Hacienda al invalidar: ' . $response->body());

                    } catch (\Exception $e) {
                        return back()->withErrors('Error de conexión: ' . $e->getMessage());
                    }
                }

                public function destroy($id)
                {
                    $dte = Dte::findOrFail($id);

                    // Solo permitimos borrar si es BORRADOR para no perder rastro de lo enviado a Hacienda
                    if ($dte->estado !== 'BORRADOR') {
                        return back()->withErrors('No se puede eliminar un DTE que ya fue procesado.');
                    }

                    try {
                        DB::beginTransaction();
                        
                        // Borramos los detalles primero
                        $dte->items()->delete(); 
                        // Borramos la cabecera
                        $dte->delete();

                        DB::commit();

                        return redirect()->route('dtes.index')->with('success', 'Borrador eliminado. El número de control está libre para usarse de nuevo.');
                    } catch (\Exception $e) {
                        DB::rollBack();
                        return back()->withErrors('Error al eliminar: ' . $e->getMessage());
                    }
                }

                public function reportarIndividual($id, DteService $dteService)
                {
                    $dte = Dte::findOrFail($id);
                    
                    // 1. Generamos el JSON de Aviso (Estructura V3)
                    $avisoJson = $dteService->generarEstructuraContingenciaIndividual($dte);

                    $payload = [
                        'Usuario' => "032267824",
                        'Password' => "Alexan24.",
                        'Ambiente' => '00',
                        'DteJson' => json_encode($avisoJson),
                        'Nit' => "05152308851012",
                        'PasswordPrivado' => 'Pw6r$LbMw93',
                    ];

                    $response = Http::post('http://163.245.212.103:7122/api/contingencia', $payload);
                    $data = $response->json();

                    if (isset($data['selloRecibido'])) {
                        $dte->update([
                            'sello_contingencia' => $data['selloRecibido'],
                            'estado' => 'REPORTADO'
                        ]);
                        return back()->with('success', 'Documento reportado con éxito.');
                    }

                    return back()->withErrors('Error MH: ' . json_encode($data));
                }

                public function enviarIndividual($id, DteService $dteService)
                {
                    $dte = Dte::findOrFail($id);

                    $payload = [
                        'Usuario' => "032267824",
                        'Password' => "Alexan24.",
                        'Ambiente' => '00',
                        'Nit' => "05152308851012",
                        'PasswordPrivado' => 'Pw6r$LbMw93',
                        'DteJson' => json_encode($dteService->generarEstructura01($dte)),
                        'TipoDte' => (string)$dte->tipo_dte,
                        'CodigoGeneracion' => (string)$dte->codigo_generacion,
                        'NumControl' => (string)$dte->numero_control,
                        'VersionDte' => ($dte->tipo_dte == '01' ? 1 : 3),
                    ];

                    $response = Http::post('http://163.245.212.103:7122/api/procesar-dte', $payload);
                    $data = $response->json();

                    if (isset($data['selloRecibido'])) {
                        $dte->update([
                            'estado' => 'PROCESADO',
                            'sello_recepcion' => $data['selloRecibido'],
                            'fecha_procesamiento' => now()
                        ]);
                        return back()->with('success', 'DTE procesado y enviado correctamente.');
                    }

                    return back()->withErrors('Error al enviar factura: ' . json_encode($data));
                }
}
