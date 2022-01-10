<?php

namespace App\Http\Controllers\API\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\MailSetting;
use App\Models\User;
use Str;
class MailSettingController extends Controller
{
    public function mailSettings()
    {
        try
        {
            $mailSetting = MailSetting::first();
            return response(prepareResult(false, $mailSetting, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
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
                'mail_username'              => ['required', 'string', 'max:191'],
            ]);

            if ($validation->fails()) {
                return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
            }

            
            if(MailSetting::find(1))
            {
                $mailSetting = MailSetting::find(1);
            }
            else
            {
                $mailSetting = new MailSetting;
            }
            $mailSetting->mail_mailer           = $request->mail_mailer;
            $mailSetting->mail_host             = $request->mail_host;
            $mailSetting->mail_port             = $request->mail_port;
            $mailSetting->mail_username         = $request->mail_username;
            $mailSetting->mail_password         = $request->mail_password;
            $mailSetting->mail_encryption       = $request->mail_encryption;
            $mailSetting->mail_from_address     = $request->mail_from_address;
            $mailSetting->mail_from_name        = $request->mail_from_name;
            $mailSetting->save();
            return response()->json(prepareResult(false, $mailSetting, getLangByLabelGroups('messages','message_updated')), config('http_response.success'));
        }
        catch (\Throwable $exception)
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        } 
    }
}
