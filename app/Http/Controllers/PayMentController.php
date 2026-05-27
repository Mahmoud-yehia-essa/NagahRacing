<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PayMentController extends Controller
{
    public function showPaymentPage()
    {
        return view('frontend.payment.payment');
    }
}
