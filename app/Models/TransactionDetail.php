<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\PaymentCardDetail;

class TransactionDetail extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'order_id','payment_card_detail_id','user_package_subscription_id', 'payment_status','transaction_id','transaction_reference_no','transaction_type','transaction_mode','transaction_status','transaction_amount','gateway_detail','card_number','card_type','card_cvv','card_expiry','card_holder_name','description','receipt_email','receipt_number','receipt_url','refund_url','transaction_timestamp','currency'
    ];


    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function paymentCardDetail()
    {
        return $this->belongsTo(PaymentCardDetail::class, 'payment_card_detail_id', 'id');
    }
}
