<?php

namespace App\Models;

use App\Models\User;
use App\Models\Camal;
use App\Models\Round;
use App\Models\Festival;
use App\Models\CamelRoundParticipation;
use Illuminate\Database\Eloquent\Model;

class Nomination extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }



    public function camal()
{
    return $this->belongsTo(Camal::class, 'camal_id'); // camal_id هو العمود في جدول nominations
}

    public function festival()
    {
        return $this->belongsTo(Festival::class);
    }

    public function round()
    {
        return $this->belongsTo(Round::class);
    }


       public function camelRoundParticipation()
{
    return $this->belongsTo(CamelRoundParticipation::class, 'camel_round_participations_id'); // camal_id هو العمود في جدول nominations
}


}
