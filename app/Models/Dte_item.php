<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dte_item extends Model
{
    protected $guarded = [];

    public function dte()
    {
        return $this->belongsTo(Dte::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

   
}