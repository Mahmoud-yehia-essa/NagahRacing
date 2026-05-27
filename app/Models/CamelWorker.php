<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CamelWorker extends Model
{
    protected $fillable = [
        'owner_id',
        'full_name',
        'login_code',
        'photo_path',
        'phone',
        'status',
        'is_online',
    ];

    protected $casts = [
        'is_online' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
