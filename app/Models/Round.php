<?php

namespace App\Models;

use App\Models\CamelRoundParticipation;
use Illuminate\Database\Eloquent\Model;

class Round extends Model
{

            protected $guarded = [];

                protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    //
     public function festival()
    {
        return $this->belongsTo(Festival::class);
    }


    public function participations()
{
    return $this->hasMany(CamelRoundParticipation::class, 'round_id');
}





// public function camals()
// {
//     return $this->belongsToMany(Camal::class, 'camel_round_participations', 'round_id', 'camal_id')
//                 ->withPivot('registration_number')
//                 ->withTimestamps();
// }

public function camals()
{
    return $this->belongsToMany(\App\Models\Camal::class, 'camel_round_participations', 'round_id', 'camal_id')
                ->withPivot('registration_number')
                ->withTimestamps();
}
}
