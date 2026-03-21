<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Departamento;
use App\Models\Actividad;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        // El Trait Multitenant filtrará automáticamente por la empresa del usuario
        $customers = Customer::orderBy('nombre', 'asc')->get();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        $departamentos = Departamento::orderBy('valor')->get();
        $actividades = Actividad::orderBy('descripcion')->get();
        return view('customers.create', compact('departamentos', 'actividades'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_documento' => 'required', // 36=NIT, 13=DUI, etc
            'num_documento' => 'required|string',
            'nrc' => 'nullable|string|max:10',
            'nombre_comercial' => 'nullable|string',
            'cod_actividad' => 'nullable|string',
            'desc_actividad' => 'nullable|string',
            'departamento' => 'nullable|string',
            'municipio' => 'nullable|string',
            'direccion_complemento' => 'nullable|string',
            'telefono' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        Customer::create($data);

        return redirect()->route('customers.index')->with('success', 'Cliente creado exitosamente.');
    }
}