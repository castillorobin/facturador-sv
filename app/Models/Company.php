<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'nombre',
        'nombre_comercial',
        'nit',
        'nrc',
        'cod_actividad',
        'desc_actividad',
        'tipo_establecimiento',
        'cod_estable_mh',
        'cod_estable',
        'cod_punto_venta_mh',
        'cod_punto_venta',
        'departamento',
        'municipio',
        'direccion_complemento',
        'telefono',
        'email',
        'api_usuario',
        'api_password',
        'password_privado',
        'ambiente',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
