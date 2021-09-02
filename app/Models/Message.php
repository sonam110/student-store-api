<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory, Uuid;


    protected $fillable = [
    	'products_services_book_id','job_id','contest_id','user_from_id', 'user_to_id', 'title', 'subject', 'message', 'status','message_type'
    ];
}
