<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{

    protected $guarded = [];

     // Relationship: An answer belongs to a question
     public function question()
     {
         return $this->belongsTo(Question::class, 'question_id');
     }
}
