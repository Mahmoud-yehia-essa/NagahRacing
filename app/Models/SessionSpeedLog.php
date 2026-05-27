<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionSpeedLog extends Model
{
    protected $fillable = [
        'training_session_id',
        'speed',
        'latitude',
        'longitude',
        'location_name',
    ];

    protected $casts = [
        'speed' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'training_session_id');
    }
}
