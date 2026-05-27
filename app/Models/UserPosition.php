<?php

namespace App\Models;

use App\Models\User;
use App\Models\Festival;
use Illuminate\Database\Eloquent\Model;

class UserPosition extends Model
{
        protected $guarded = [];

 public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


  public function festival()
    {
        return $this->belongsTo(Festival::class);
    }

    //
}
