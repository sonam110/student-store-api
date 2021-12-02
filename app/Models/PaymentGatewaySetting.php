<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewaySetting extends Model
{
    use HasFactory, Uuid;

    protected $fillable = ['payment_gateway_name','payment_gateway_key', 'payment_gateway_secret','stripe_currency','klarna_username','klarna_password','swish_access_token', 'bambora_encoded_api_key', 'bambora_secret_key', 'bambora_access_key', 'bambora_merchant_number', 'bambora_md5_key'];
}
