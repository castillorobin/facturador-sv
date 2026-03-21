<?php

namespace App\Models;
use App\Traits\Multitenant;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use Multitenant; // <--- Aquí activas la magia

    protected $fillable = [
        'company_id', 
        'nombre', 
        'tipo_documento', 
        'num_documento', 
        'nrc', 
        'nombre_comercial', 
        'cod_actividad', 
        'desc_actividad', 
        'departamento', 
        'municipio', 
        'direccion_complemento', 
        'telefono', 
        'email'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }


}
