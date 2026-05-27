<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionChat extends Model
{
    protected $fillable = [
        'training_session_id',
        'owner_id',
        'camel_worker_id',
        'content',
        'content_type',
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
