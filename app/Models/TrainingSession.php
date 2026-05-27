<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
    protected $fillable = [
        'camel_worker_id',
        'location_name',
        'latitude',
        'longitude',
        'round_status',
        'speed',
        'average_speed',
        'round_distance_km',
        'round_time',
        'performance',
        'session_ended_at',
        'summary_text',
        'summary_audio',
        'summary_image',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'speed' => 'decimal:2',
        'average_speed' => 'decimal:2',
        'round_distance_km' => 'decimal:2',
        'performance' => 'decimal:2',
        'session_ended_at' => 'datetime',
    ];

    public function worker()
    {
        return $this->belongsTo(CamelWorker::class, 'camel_worker_id');
    }

    public function speedLogs()
    {
        return $this->hasMany(SessionSpeedLog::class, 'training_session_id');
    }

    public function instructions()
    {
        return $this->hasMany(SessionInstruction::class, 'training_session_id');
    }

    public function chats()
    {
        return $this->hasMany(SessionChat::class, 'training_session_id');
    }
}
