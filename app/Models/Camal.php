<?php

namespace App\Models;

use App\Models\User;
use App\Models\Round;
use Illuminate\Database\Eloquent\Model;

class Camal extends Model
{
    protected $table = 'camals'; // تحديد اسم الجدول الصحيح
    protected $guarded = [];

    public function nominations()
    {
        return $this->hasMany(Nomination::class, 'camal_id');
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }


     public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

}
