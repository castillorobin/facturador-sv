<?php

namespace App\Http\Controllers;

use App\Models\Dte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DteExportController extends Controller
{
    public function exportZip(Request $request)
    {
        // 1. Validar el rango de fechas
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        // 2. Consultar los DTEs en el rango (usando tu modelo Dte y columna fecha_emision)
        $dtes = \App\Models\Dte::whereBetween('fecha_emision', [
            $request->fecha_inicio . ' 00:00:00',
            $request->fecha_fin . ' 23:59:59'
        ])->get();

        if ($dtes->isEmpty()) {
            return back()->withErrors('No se encontraron registros en el rango de fechas seleccionado.');
        }

        // 3. Configurar el archivo ZIP temporal
        $zipFileName = 'Export_DTEs_' . date('Ymd_His') . '.zip';
        $zipPath = storage_path('app/private/' . $zipFileName); // Lo creamos en private también por consistencia
        
        $zip = new ZipArchive;
        $archivosAgregados = 0;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($dtes as $dte) {
                
                // --- PROCESAR JSON ORIGINAL (FACTURA) ---
                // Probamos la ruta guardada en la base de datos
                $rutaBD = $dte->json_legible_path ?? $dte->ruta_json;

                if (!empty($rutaBD) && Storage::exists($rutaBD)) {
                    // Construimos la ruta absoluta REAL incluyendo 'private'
                    $rutaAbsoluta = storage_path('app/private/' . $rutaBD);
                    
                    if (file_exists($rutaAbsoluta)) {
                        // Lo metemos en la carpeta FACTURAS dentro del ZIP
                        $zip->addFile($rutaAbsoluta, 'FACTURAS/' . basename($rutaAbsoluta));
                        $archivosAgregados++;
                    }
                }

                // --- PROCESAR JSON DE INVALIDACIÓN (SI ESTÁ ANULADO) ---
                if ($dte->estado === 'ANULADO' && !empty($dte->json_invalidacion_path)) {
                    if (Storage::exists($dte->json_invalidacion_path)) {
                        $rutaAbsolutaAnulacion = storage_path('app/private/' . $dte->json_invalidacion_path);
                        
                        if (file_exists($rutaAbsolutaAnulacion)) {
                            // Lo metemos en la carpeta ANULACIONES dentro del ZIP
                            $zip->addFile($rutaAbsolutaAnulacion, 'ANULACIONES/' . basename($rutaAbsolutaAnulacion));
                            $archivosAgregados++;
                        }
                    }
                }
            }

            // 4. Cerrar el ZIP
            $zip->close();

            // Si después de recorrer todo no se encontró ningún archivo físico
            if ($archivosAgregados === 0) {
                if (file_exists($zipPath)) unlink($zipPath);
                return back()->withErrors('Se encontraron los registros, pero los archivos físicos .json no existen en la carpeta storage/app/private/dtes_json/');
            }
        } else {
            return back()->withErrors('No se pudo crear el archivo comprimido en el servidor.');
        }

        // 5. Retornar descarga y borrar el temporal después de enviar
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
