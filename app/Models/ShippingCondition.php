<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ShippingCondition extends Model
{
    use HasFactory, Uuid;

    protected $fillable = ['auto_id','user_id','shipping_package','order_amount_from','order_amount_to','discount_percent'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
