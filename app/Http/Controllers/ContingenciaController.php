<?php

namespace App\Http\Controllers;
use App\Models\Company;

use Illuminate\Http\Request;

class ContingenciaController extends Controller
{
    public function toggleModo()
    {
        $company = Company::find(auth()->user()->company_id);
        
        // Cambiamos al estado opuesto
        $company->modo_contingencia = !$company->modo_contingencia;
        $company->save();

        $estado = $company->modo_contingencia ? 'ACTIVADO' : 'DESACTIVADO';
        
        return back()->with('success', "Modo Contingencia $estado con éxito.");
    }

    
}
