<?php

namespace App\Models;

use App\Models\Team;
use App\Models\Question;
use Illuminate\Database\Eloquent\Model;

class QuestionsRegister extends Model
{
    protected $guarded = [];


    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

}
