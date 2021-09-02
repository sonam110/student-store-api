<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserDisputeDetail extends Model
{
    use HasFactory, Uuid;


    protected $fillable = [
    	'dispute_raised_by_user',
    	'dispute_raised_for_user',
    	'order_item_id',
    	'comment_by_consumer',
    	'comment_by_provider',
    	'comment_by_admin',
    	'dispute_status'
    ];
}
