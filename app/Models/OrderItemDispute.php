<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItem;

class OrderItemDispute extends Model
{
    use HasFactory,Uuid;

    protected $fillable = ['dispute_raised_by','dispute_raised_against','order_item_id','products_services_book_id','quantity','amount_to_be_returned','dispute','reply','date_of_dispute_completed','dispute_status','reason_for_dispute_decline','admin'];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class,'order_id','id');
    }
}
