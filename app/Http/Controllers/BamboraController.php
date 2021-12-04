<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BamboraController extends Controller
{
    public function bamCallback(Request $request)
    {
        \Log::channel('bambora')->info($request->all());
    }
}
