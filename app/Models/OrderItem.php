<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Order;
use App\Models\Package;
use App\Models\ProductsServicesBook;
use App\Models\ContestApplication;
use App\Models\OrderTracking;
use App\Models\RatingAndFeedback;
use App\Models\OrderItemReturn;
use App\Models\OrderItemDispute;
use App\Models\OrderItemReplacement;

class OrderItem extends Model
{
    use HasFactory, Uuid;



    protected $fillable = [
        'user_id','order_id','package_id','products_services_book_id','contest_application_id','product_type','contest_type','title','sku','price','quantity','discount','sell_type','rent_duration','item_status','item_payment_status','amount_returned','date_of_return_initiated','date_of_return_completed','return_card_number','return_card_holder_name','reason_of_return','reason_of_cancellation','is_returned','is_replaced','is_rated','note_to_seller','cover_image','return_applicable_date','tracking_number','expected_delivery_date','delivery_completed_date','is_disputed','earned_reward_points','reward_point_status','ask_for_cancellation','reason_for_cancellation_request_decline','reason_for_cancellation_request','is_sent_to_cool_company','sent_to_cool_company_date','is_transferred_to_vendor','amount_transferred_to_vendor','fund_transferred_date','student_store_commission','cool_company_commission'
    ];


    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function productsServicesBook()
    {
        return $this->belongsTo(ProductsServicesBook::class, 'products_services_book_id', 'id');
    }

    public function contestApplication()
    {
        return $this->belongsTo(ContestApplication::class, 'contest_application_id', 'id');
    }
    
    public function orderTrackings()
    {
        return $this->hasMany(OrderTracking::class,'order_item_id','id')->orderBy('created_at','ASC');
    }

    public function ratingAndFeedback()
    {
        return $this->hasOne(RatingAndFeedback::class,'order_item_id','id');
    }

    public function return()
    {
        return $this->hasOne(OrderItemReturn::class,'order_item_id','id');
    }

    public function replacement()
    {
        return $this->hasOne(OrderItemReplacement::class,'order_item_id','id');
    }

    public function dispute()
    {
        return $this->hasOne(OrderItemDispute::class,'order_item_id','id');
    }
}
