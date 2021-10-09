<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RewardPointSetting;

class AppSetting extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'reward_point_setting_id','app_name','description','logo_path','logo_thumb_path', 'invite_url','copyright_text','fb_ur','twitter_url','insta_url','linked_url','support_email','support_contact_number','customer_rewards_pt_value','single_rewards_pt_value','reward_points_policy','vat','meta_title', 'meta_keywords', 'meta_description','coolCompanyVatRateId','coolCompanyCommission'
    ];

    public function rewardPointSetting()
    {
        return $this->belongsTo(RewardPointSetting::class, 'reward_point_setting_id', 'id');
    }
}
