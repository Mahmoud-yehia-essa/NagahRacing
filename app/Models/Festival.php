<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Festival extends Model
{
    //
        protected $guarded = [];
    //      protected $casts = [
    //     'start' => 'date',
    //     'end'   => 'date',
    // ];

     // هنا نخلي الكاست للتواريخ
    protected $casts = [
        'start' => 'date:Y-m-d',
        'end'   => 'date:Y-m-d',
    ];

}
