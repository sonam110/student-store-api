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

            
            if(PaymentGatewaySetting::find(1))
            {
                $paymentGatewaySetting = PaymentGatewaySetting::find(1);
            }
            else
            {
                $paymentGatewaySetting = new PaymentGatewaySetting;
            }
            $paymentGatewaySetting->payment_gateway_name        = $request->payment_gateway_name;
            $paymentGatewaySetting->payment_gateway_key         = $request->payment_gateway_key;
            $paymentGatewaySetting->payment_gateway_secret      = $request->payment_gateway_secret;
            $paymentGatewaySetting->save();
            return response()->json(prepareResult(false, $paymentGatewaySetting, getLangByLabelGroups('messages','message_updated')), config('http_response.success'));
        }
        catch (\Throwable $exception)
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        } 
    }
}
