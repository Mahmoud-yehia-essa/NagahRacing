<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GroupUser extends Model
{


  protected $guarded = [];


       public function groub()
    {
        return $this->belongsTo(Groub::class, 'groub_id');
    }


        public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }







}
