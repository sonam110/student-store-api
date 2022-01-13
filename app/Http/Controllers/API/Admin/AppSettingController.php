<?php

namespace App\Http\Controllers\API\Admin;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppSettingResource;
use Illuminate\Http\Request;
use Auth;
use App\Models\AppSetting;
use App\Models\User;
use Str;
class AppSettingController extends Controller
{
    public function appSettings()
    {
        try
        {
            $appSetting = AppSetting::first();
            return response(prepareResult(false, new AppSettingResource($appSetting), getLangByLabelGroups('messages','message_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function update(Request $request)
    {
        try
        {
            $validation = \Validator::make($request->all(),[
                'app_name' => ['required', 'string', 'max:191'],
            ]);

            if ($validation->fails()) {
                return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
            }

            
            if(AppSetting::first())
            {
                $appSetting = AppSetting::first();
            }
            else
            {
                $appSetting = new AppSetting;
            }
            $appSetting->reward_point_setting_id    = $request->reward_point_setting_id;
            $appSetting->single_rewards_pt_value    = $request->single_rewards_pt_value;
            $appSetting->customer_rewards_pt_value  = $request->customer_rewards_pt_value;
            $appSetting->app_name                   = $request->app_name;
            $appSetting->org_number                 = $request->org_number;
            $appSetting->description                = $request->description;
            $appSetting->logo_path                  = $request->logo_path;
            $appSetting->logo_thumb_path            = $request->logo_thumb_path;
            $appSetting->copyright_text             = $request->copyright_text;
            $appSetting->fb_ur                      = $request->fb_ur;
            $appSetting->twitter_url                = $request->twitter_url;
            $appSetting->insta_url                  = $request->insta_url;
            $appSetting->linked_url                 = $request->linked_url;
            $appSetting->support_email              = $request->support_email;
            $appSetting->support_contact_number     = $request->support_contact_number;
            $appSetting->address                    = $request->address;
            $appSetting->reward_points_policy       = $request->reward_points_policy;
            $appSetting->meta_title                 = $request->meta_title;
            $appSetting->meta_keywords              = $request->meta_keywords;
            $appSetting->meta_description           = $request->meta_description;
            $appSetting->customer_rewards_pt_value  = $request->customer_rewards_pt_value;
            $appSetting->single_rewards_pt_value    = $request->single_rewards_pt_value;
            $appSetting->vat                        = $request->vat;
            $appSetting->is_enabled_cool_company       = $request->is_enabled_cool_company;
            $appSetting->coolCompanyVatRateId       = $request->cool_company_vat_rate_id;
            $appSetting->coolCompanyCommission      = $request->coolCompanyCommission;
            $appSetting->cool_company_social_fee_percentage      = $request->cool_company_social_fee_percentage;
            $appSetting->cool_company_salary_tax_percentage      = $request->cool_company_salary_tax_percentage;
            $appSetting->invite_url                 = $request->invite_url;
            $appSetting->play_store_url             = $request->play_store_url;
            $appSetting->app_store_url              = $request->app_store_url;
            $appSetting->is_job_mod_enabled         = $request->is_job_mod_enabled;
            $appSetting->is_product_mod_enabled     = $request->is_product_mod_enabled;
            $appSetting->is_service_mod_enabled     = $request->is_service_mod_enabled;
            $appSetting->is_book_mod_enabled        = $request->is_book_mod_enabled;
            $appSetting->is_contest_mod_enabled     = $request->is_contest_mod_enabled;
            $appSetting->allowed_app_version     = $request->allowed_app_version;
            if($appSetting)
            {
                if(($appSetting->is_enabled_cool_company != $request->is_enabled_cool_company) ||
                ($appSetting->coolCompanyVatRateId != $request->cool_company_vat_rate_id) ||
                ($appSetting->coolCompanyCommission != $request->coolCompanyCommission) ||
                ($appSetting->cool_company_social_fee_percentage != $request->cool_company_social_fee_percentage) ||
                ($appSetting->cool_company_salary_tax_percentage != $request->cool_company_salary_tax_percentage))
                {
                    appSettingUpdatePrice();
                }
            }
            $appSetting->save();
            return response()->json(prepareResult(false, new AppSettingResource($appSetting), getLangByLabelGroups('messages','message_updated')), config('http_response.success'));
        }
        catch (\Throwable $exception)
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        } 
    }
}
