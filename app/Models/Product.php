<?php

namespace App\Models;

use App\Traits\Multitenant; // Importante para el filtrado por empresa
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Multitenant;

    protected $fillable = [
        'company_id', 
        'nombre', 
        'codigo_interno', // Coincide con migración
        'precio_unitario', // Coincide con migración
        'unidad_medida',   // Agregado
        'es_exento'        // Agregado
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}