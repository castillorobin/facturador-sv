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
}