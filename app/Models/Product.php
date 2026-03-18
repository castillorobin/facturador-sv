<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Multitenant; // <--- Aquí activas la magia
    protected $fillable = [
        'company_id', 'nombre', 'descripcion', 'precio', 'codigo'
    ];

    

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
