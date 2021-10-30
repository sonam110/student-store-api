<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class UserDeviceInfo extends Model
{
    use HasFactory, Uuid;
    use SoftDeletes;

    protected $fillable = [
        'user_id','fcm_token','device_uuid','platform','model','os_version','manufacturer','serial_number','system_ip_address','status'
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
