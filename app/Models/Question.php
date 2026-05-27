<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{

    protected $guarded = [];


    // Relationship: A question can have many answers
    public function answers()
    {
        return $this->hasMany(Answer::class, 'question_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }


}
