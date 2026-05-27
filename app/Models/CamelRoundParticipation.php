<?php

namespace App\Models;

use App\Models\Camal;
use App\Models\Round;
use Illuminate\Database\Eloquent\Model;

class CamelRoundParticipation extends Model
{
    //
        protected $guarded = [];




    public function round()
    {
        return $this->belongsTo(Round::class, 'round_id');
    }

    public function camal()
    {
        return $this->belongsTo(Camal::class, 'camal_id');
    }

}
