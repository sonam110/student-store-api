<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\OrderItem;
use App\Models\ProductsServicesBook;


class RatingAndFeedback extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'order_item_id','products_services_book_id','from_user','to_user','product_rating','user_rating','product_feedback','user_feedback','is_feedback_approved','user_name','contest_id'
    ];


    public function fromUser()
    {
        return $this->belongsTo(User::class,'from_user','id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class,'to_user','id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class,'from_user','id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class,'order_item_id','id');
    }

    public function product()
    {
        return $this->belongsTo(ProductsServicesBook::class,'products_services_book_id','id');
    }
}
