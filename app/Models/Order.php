<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\AddressDetail;
use App\Models\OrderItem;
use App\Models\TransactionDetail;


class Order extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id','address_detail_id','order_status','sub_total','item_discount','tax','shipping_charge','total','promo_code','promo_code_discount','grand_total','remark','first_name','last_name','email','contact_number','latitude','longitude','country','state','city','full_address','order_number','used_reward_points','reward_point_status','payable_amount','vat','order_for'
    ];


    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
    
    public function addressDetail()
    {
        return $this->belongsTo(AddressDetail::class, 'address_detail_id', 'id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class,'order_id','id');
    }
    public function transaction()
    {
        return $this->hasOne(TransactionDetail::class,'order_id','id');
    }
}
