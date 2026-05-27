<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionInstruction extends Model
{
    protected $fillable = [
        'training_session_id',
        'owner_id',
        'camel_worker_id',
        'is_from_owner',
        'instruction',
        'instruction_type',
        'reply',
        'reply_type',
    ];

    protected $casts = [
        'is_from_owner' => 'boolean',
    ];

    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'training_session_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function worker()
    {
        return $this->belongsTo(CamelWorker::class, 'camel_worker_id');
    }
}
