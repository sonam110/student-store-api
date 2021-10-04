<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PaymentCardDetail extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id','card_number','card_type','card_cvv','card_expiry','card_holder_name','is_default','status','is_minor','parent_full_name','mobile_number','payment_card_details'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
