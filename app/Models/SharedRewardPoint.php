<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;
use App\Models\User;

class SharedRewardPoint extends Model
{
    use HasFactory, Uuid;

    protected $fillable = ['sender_id','receiver_id','reward_points'];

    public function sender()
    {
        return $this->belongsTo(User::class,'sender_id','id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class,'receiver_id','id');
    }
}
