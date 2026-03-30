<?php

namespace App\Http\Controllers;
use App\Models\Dte;


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

public function store(Request $request)
{
    // 1. Validar la entrada
    $request->validate([
        'dte_origen_id' => 'required|exists:dtes,id',
        'motivo_detalle' => 'required|string|min:5',
        'items' => 'required|array',
    ]);

    // 2. Filtrar solo los productos que el usuario marcó en los checkboxes
    $itemsSeleccionados = collect($request->items)->filter(function($item) {
        return isset($item['selected']) && $item['selected'] == "1";
    });

    if ($itemsSeleccionados->isEmpty()) {
        return back()->withErrors('Debes seleccionar al menos un producto para aplicar la Nota de Crédito.');
    }

    // Aquí vendrá la lógica para llamar a DteService y enviar a Hacienda...
    // Por ahora, solo para probar que llegamos aquí:
    return "¡Listo! El sistema recibió " . $itemsSeleccionados->count() . " productos para procesar la Nota de Crédito.";
}

}
