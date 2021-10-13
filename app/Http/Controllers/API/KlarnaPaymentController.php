<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentGatewaySetting;
use mervick\aesEverywhere\AES256;
use Auth;
use App\Models\User;

class KlarnaPaymentController extends Controller
{
    function __construct()
    {
        $this->paymentInfo = PaymentGatewaySetting::first();
        $username       = $this->paymentInfo->klarna_username;
        $password       = $this->paymentInfo->klarna_password;
        $this->auth     = base64_encode($username.":".$password);
    }

}
