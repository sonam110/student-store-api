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
        'reward_point_setting_id','app_name', 'org_number','description','logo_path','logo_thumb_path', 'invite_url','copyright_text','fb_ur','twitter_url','insta_url','linked_url','support_email','support_contact_number','customer_rewards_pt_value','single_rewards_pt_value','reward_points_policy','vat','meta_title', 'meta_keywords', 'meta_description','is_enabled_cool_company','coolCompanyVatRateId','coolCompanyCommission', 'cool_company_social_fee_percentage','cool_company_salary_tax_percentage','address', 'is_job_mod_enabled','is_product_mod_enabled','is_service_mod_enabled','is_book_mod_enabled','is_contest_mod_enabled','play_store_url','app_store_url'
    ];

    public function rewardPointSetting()
    {
        return $this->belongsTo(RewardPointSetting::class, 'reward_point_setting_id', 'id');
    }
}
