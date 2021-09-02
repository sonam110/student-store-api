<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Notification extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id','sender_id','notification_id','device_uuid','device_platform','type','title','sub_title','message','image_url','read_status','user_type','module','data_id','screen'
    ];
    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
