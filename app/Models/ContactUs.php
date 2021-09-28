<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ContactUs extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'name','phone','email','message','user_id','item_id','item_type','title','files','status'
    ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class,'user_id','id');
    // }
}
