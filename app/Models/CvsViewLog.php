<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CvsViewLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','user_cv_detail_id','user_package_subscription_id','valid_till'];
}
