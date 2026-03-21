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

class DteController extends Controller
{
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

            // 2. Generar Correlativo (Número de Control)
            // Formato MH sugerido: DTE-tipo-8_digitos_correlativos
            $ultimoDte = Dte::where('tipo_dte', $request->tipo_dte)->latest()->first();
            $correlativo = $ultimoDte ? (int) substr($ultimoDte->numero_control, -8) + 1 : 1;
            $numeroControl = "DTE-" . $request->tipo_dte . "-" . str_pad($correlativo, 8, '0', STR_PAD_LEFT);

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
}
