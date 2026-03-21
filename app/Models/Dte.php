<?php

namespace App\Models;

use App\Traits\Multitenant;
use Illuminate\Database\Eloquent\Model;

class Dte extends Model
{
    use Multitenant;

    protected $guarded = []; // O define todos los fillables que pusiste en la migración

    // Relación con los productos/detalles de la factura
    public function items()
    {
        return $this->hasMany(Dte_item::class);
    }

    // Relación con el cliente (Receptor)
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}