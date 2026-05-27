<?php

namespace App\Models;

use App\Models\Festival;
use App\Models\Groub;
use Illuminate\Database\Eloquent\Model;

class GroupFestival extends Model
{


        protected $guarded = [];





     public function groub()
    {
        return $this->belongsTo(Groub::class, 'groub_id');
    }

    public function festival()
    {
        return $this->belongsTo(Festival::class, 'festival_id');
    }

}
