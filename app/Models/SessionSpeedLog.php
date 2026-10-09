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
        'speed' => 'double',
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'training_session_id');
    }
}
