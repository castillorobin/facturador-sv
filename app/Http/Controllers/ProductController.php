<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        // Multitenant filtrará automáticamente
        $products = Product::orderBy('nombre', 'asc')->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_unitario' => 'required|numeric|min:0',
            'codigo_interno' => 'nullable|string|max:50',
            'unidad_medida' => 'required|string',
            'es_exento' => 'boolean'
        ]);

        // Aseguramos el valor del booleano si no viene en el request
        $data['es_exento'] = $request->has('es_exento');

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product)
    {
        if ($product->company_id !== auth()->user()->company_id) {
            abort(403);
        }
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        if ($product->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_unitario' => 'required|numeric|min:0',
            'unidad_medida' => 'required|string',
            'codigo_interno' => 'nullable|string|max:50',
        ]);

        // Manejo del checkbox de exento
        $data = $request->all();
        $data['es_exento'] = $request->has('es_exento');

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product)
    {
        if ($product->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        // Opcional: Evitar borrar si ya está en facturas (DTEs)
        // if ($product->dteItems()->count() > 0) { 
        //    return back()->with('error', 'No se puede eliminar un producto con historial de ventas.');
        // }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado del catálogo.');
    }
}