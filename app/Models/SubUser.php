<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubUser extends Model
{
    protected $table = 'sub_users';

    protected $fillable = [
        'owner_id',
        'full_name',
        'phone',
        'login_code',
        'status',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
