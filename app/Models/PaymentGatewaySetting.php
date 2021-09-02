<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewaySetting extends Model
{
    use HasFactory, Uuid;

    protected $fillable = ['payment_gateway_name','payment_gateway_key', 'payment_gateway_secret'];
}
