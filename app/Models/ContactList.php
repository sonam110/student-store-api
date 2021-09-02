<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ChatList;
use App\Models\ProductsServicesBook;
use App\Models\Job;
use App\Models\Contest;
use Auth;
use App\Models\User;

class ContactList extends Model
{
    use HasFactory, Uuid;

    protected $fillable = ['job_id','products_services_book_id','contest_id','buyer_id','seller_id'];

 
    public function unreadMessages()
    {
    	return $this->hasMany(ChatList::class,'contact_list_id','id')->where('receiver_id',Auth::id())->where('status','unread');
    }

    public function productsServicesBook()
    {
        return $this->belongsTo(ProductsServicesBook::class, 'products_services_book_id', 'id');
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class, 'contest_id', 'id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'buyer_id', 'id');
    }
}
