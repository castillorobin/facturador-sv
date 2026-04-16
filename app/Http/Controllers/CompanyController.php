<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Actividad;



class CompanyController extends Controller
{
    public function edit()
    {
        // Obtenemos la empresa del usuario logueado
        $company = Auth::user()->company;


        // Obtenemos los catálogos para los selects
    $departamentos = Departamento::orderBy('valor')->get();
    $actividades = Actividad::orderBy('codigo')->get(); // 'valor' o 'codigo' según tu tabla
    
    // Obtenemos los municipios del departamento que ya tiene la empresa (opcional para carga inicial)
    $municipios = Municipio::orderBy('valor')->get();

 

        return view('company.edit', compact('company', 'departamentos', 'actividades', 'municipios'));
    }

    public function update(Request $request)
    {
        $company = Auth::user()->company;

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'nombre_comercial' => 'required|string|max:255',
            'nit' => 'required|string',
            'nrc' => 'required|string|max:10',
            'cod_actividad' => 'required|string|max:5',
            'desc_actividad' => 'required|string',
            'departamento' => 'required|string|size:2',
            'municipio' => 'required|string|size:2',
            'direccion_complemento' => 'required|string',
            'telefono' => 'required|string',
            'email' => 'required|email',
            // Credenciales API
            'api_usuario' => 'nullable|string',
            'api_password' => 'nullable|string',
            'password_privado' => 'nullable|string',
            'ambiente' => 'required|in:00,01',
        ]);

        $company->update($data);

        return redirect()->route('company.edit')->with('status', 'company-updated');
    }
}
