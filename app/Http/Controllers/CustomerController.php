<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Actividad;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use App\Models\Dte;
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
        $municipios = Municipio::all();
 
        return view('customers.create', compact('departamentos', 'actividades', 'municipios'));
    }
 
    public function store(Request $request)
    {
       // dd($request->all());
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
            'tipo' => 'nullable|string',
        ]);



        Customer::create($data);

        return redirect()->route('customers.index')->with('success', 'Cliente creado exitosamente.');
    }

    public function edit(Customer $customer)
{
    $departamentos = Departamento::orderBy('valor')->get();
    $actividades = Actividad::orderBy('descripcion')->get();
    
    // FILTRAR: Solo traer municipios que pertenecen al departamento del cliente
    // Asumiendo que en tu tabla 'municipios' tienes una columna 'departamento_codigo' o similar
    $municipios = Municipio::where('departamento_codigo', $customer->departamento)->get();

    return view('customers.edit', compact('customer', 'actividades', 'departamentos', 'municipios'));
}

    public function update(Request $request, Customer $customer)
    {
        if ($customer->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_documento' => 'required|in:36,37', // 36=NIT, 37=DUI
            'num_documento' => 'required|string|max:20',
            'email' => 'nullable|email',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'tipo' => 'nullable|in:peque,mediano,grande',
        ]);

        $customer->update($request->all());

        return redirect()->route('customers.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        // Opcional: Validar si tiene DTEs asociados antes de borrar
        if ($customer->dtes()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un cliente que tiene facturas asociadas.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Cliente eliminado con éxito.');
    }
}