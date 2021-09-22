<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VendorFundTransfer extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'transection_id', 'object', 'amount', 'amount_reversed', 'balance_transaction', 'created', 'currency', 'description', 'destination', 'destination_payment', 'livemode', 'complete_response'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
