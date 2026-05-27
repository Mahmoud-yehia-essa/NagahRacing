<?php

namespace App\Models;

use App\Models\Festival;
use App\Models\GroupUser;
use App\Models\Nomination;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Groub extends Model
{
        protected $guarded = [];



          public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }



           public function festival()
    {
        return $this->belongsTo(Festival::class, 'festival_id');
    }




      // إضافة علاقة الترشيحات المتعلقة بالمجموعة
    public function nominations() {
        // فقط للأعضاء الموجودين في المجموعة والمهرجان المرتبط
        return Nomination::where('festival_id', $this->festival_id)
            ->whereIn('user_id', $this->groupUsers->pluck('user_id'));
    }


    // اعضاء المجموعة
    public function groupUsers()
    {
        return $this->hasMany(GroupUser::class, 'groub_id');
    }

    // المهرجانات
    public function festivals()
    {
        return $this->belongsToMany(Festival::class, 'group_festivals', 'groub_id', 'festival_id');
    }




}
