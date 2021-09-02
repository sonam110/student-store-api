<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Refund extends Model
{
    use HasFactory, Uuid;
    protected $fillable = ['auto_id','order_id','payment_card_detail_id','order_item_id','transaction_id','refund_id','object','amount','balance_transaction','charge','created','currency','metadata','payment_intent','reason','receipt_number','source_transfer_reversal','status','transfer_reversal','gateway_detail','transaction_type','transaction_mode','card_number','card_type','card_cvv','card_expiry','card_holder_name','quantity','price','reason_for_refund'];
}
