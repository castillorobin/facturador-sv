<?php

namespace App\Models;

use App\Traits\Multitenant;
use Illuminate\Database\Eloquent\Model;
// ESTAS DOS LÍNEAS SON LAS QUE ARREGLAN EL ERROR:
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dte extends Model
{
    use Multitenant;

    protected $guarded = [];

    // Cambiamos 'items' a 'details' para que coincida con lo que pusimos en el controlador
    public function details(): HasMany
    {
        return $this->hasMany(Dte_item::class, 'dte_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    protected $casts = [
        'fecha_emision' => 'datetime',
    ];
}