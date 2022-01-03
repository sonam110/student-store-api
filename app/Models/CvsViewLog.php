<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\UserCvDetail;

class CvsViewLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','user_cv_detail_id','user_package_subscription_id','valid_till'];

    public function users()
    {
        return $this->hasMany(User::class, 'user_id', 'id');
    }

    public function userCvDetails()
    {
        return $this->hasMany(UserCvDetail::class, 'user_cv_detail_id', 'id');
    }
}
