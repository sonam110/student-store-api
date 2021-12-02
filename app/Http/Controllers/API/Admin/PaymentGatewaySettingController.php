<?php

namespace App\Http\Controllers\API\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\PaymentGatewaySetting;
use App\Models\User;
use Str;
class PaymentGatewaySettingController extends Controller
{
    public function paymentGatewaySettings()
    {
        try
        {
            $paymentGatewaySetting = PaymentGatewaySetting::first();
            return response(prepareResult(false, $paymentGatewaySetting, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function update(Request $request)
    {
        try
        {
            $validation = \Validator::make($request->all(),[
                'payment_gateway_name'              => ['required', 'string', 'max:191'],
            ]);

            if ($validation->fails()) {
                return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
            }

            
            if(PaymentGatewaySetting::first())
            {
                $paymentGatewaySetting = PaymentGatewaySetting::first();
            }
            else
            {
                $paymentGatewaySetting = new PaymentGatewaySetting;
            }
            $paymentGatewaySetting->payment_gateway_name        = $request->payment_gateway_name;
            $paymentGatewaySetting->payment_gateway_key         = $request->payment_gateway_key;
            $paymentGatewaySetting->payment_gateway_secret      = $request->payment_gateway_secret;
            $paymentGatewaySetting->stripe_currency      = $request->stripe_currency;
            $paymentGatewaySetting->klarna_username      = $request->klarna_username;
            $paymentGatewaySetting->klarna_password      = $request->klarna_password;
            $paymentGatewaySetting->swish_access_token   = $request->swish_access_token;
            $paymentGatewaySetting->bambora_encoded_api_key = $request->bambora_encoded_api_key;
            $paymentGatewaySetting->bambora_secret_key      = $request->bambora_secret_key;
            $paymentGatewaySetting->bambora_access_key      = $request->bambora_access_key;
            $paymentGatewaySetting->bambora_merchant_number = $request->bambora_merchant_number;
            $paymentGatewaySetting->bambora_md5_key         = $request->bambora_md5_key;
            $paymentGatewaySetting->save();
            return response()->json(prepareResult(false, $paymentGatewaySetting, getLangByLabelGroups('messages','message_updated')), config('http_response.success'));
        }
        catch (\Throwable $exception)
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        } 
    }
}
