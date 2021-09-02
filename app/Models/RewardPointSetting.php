<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class RewardPointSetting extends Model
{
    use HasFactory, Uuid;
    
    protected $fillable = [
        'reward_points','equivalent_currency_value','applicable_from','applicable_to','min_range','min_value','mid_range','mid_value','max_range','max_value','status'
    ];

}
