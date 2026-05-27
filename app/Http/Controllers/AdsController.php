<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdsController extends Controller
{
    public function addAds()
    {
        return view('admin.ads.add_ads');
    }
}
