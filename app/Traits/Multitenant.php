<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Multitenant
{
    protected static function bootMultitenant()
    {
        // Al crear un registro (Cliente, Producto, Factura), 
        // le asigna automáticamente el ID de la empresa del usuario logueado
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->company_id = auth()->user()->company_id;
            }
        });

        // Al consultar, solo trae los registros de la empresa del usuario logueado
        static::addGlobalScope('company_id', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('company_id', auth()->user()->company_id);
            }
        });
    }
}