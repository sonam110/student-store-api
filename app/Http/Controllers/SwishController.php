<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SwishController extends Controller
{
    public function swishPaymentCallback(Request $request)
    {
        \Log::channel('swish')->info($request->all());
    }
}
