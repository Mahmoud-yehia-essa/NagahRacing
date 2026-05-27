<?php

namespace App\Models;

use App\Models\Question;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $guarded = [];

    public function questions()
    {
        return $this->hasMany(Question::class, 'category_id');
    }

    //
}
