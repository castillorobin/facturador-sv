<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dte extends Model
{
    use Multitenant;
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
