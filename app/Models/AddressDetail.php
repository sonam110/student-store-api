<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AddressDetail extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id','latitude','longitude','country','state','city','zip_code','full_address','address_type','is_default','status','is_deleted',
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
