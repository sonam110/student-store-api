<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Stripe;

class StripeController extends Controller
{
    public function createConnect()
    {
        $getUser = User::find(Auth::id());
        $account = \Stripe\Account::create([
          'type' => 'standard',
        ]);
    }
}
