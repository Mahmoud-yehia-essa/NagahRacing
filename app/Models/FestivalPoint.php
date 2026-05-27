<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FestivalPoint extends Model
{
     protected $guarded = [];

    public function festival()
    {
        return $this->belongsTo(Festival::class);
    }
}
