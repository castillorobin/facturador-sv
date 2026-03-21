<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dte_item extends Model
{
    protected $guarded = [];

    public function dte()
    {
        return $this->belongsTo(Dte::class);
    }
}