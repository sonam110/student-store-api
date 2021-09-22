<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Stripe;

class StripeController extends Controller
{
    public function createStripeAccount()
    {
        try
        {
            $user = User::select('stripe_account_id','stripe_status')->find(Auth::id());
            if($user->stripe_status=='1' || $user->stripe_status==null)
            {
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                $account = \Stripe\Account::create([
                  'type' => 'standard',
                ]);

                $user->stripe_account_id = $account['id'];
                $user->stripe_status = '2';
                $user->save();

                if($user)
                {
                    $account_links = \Stripe\AccountLink::create([
                      'account'     => $account['id'],
                      'refresh_url' => env('STRIPE_REFRESH_URL'),
                      'return_url'  => env('STRIPE_RETURN_URL'),
                      'type'        => 'account_onboarding',
                    ]);
                }

                return response(prepareResult(false, $account_links, getLangByLabelGroups('messages','message__address_detail_list')), config('http_response.success'));
            }
            return response()->json(prepareResult(true, 'Account already exist.', getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function regenerateStripeAccountLink()
    {
        try
        {
            $user = User::select('stripe_account_id','stripe_status')->find(Auth::id());
            if(($user->stripe_status=='2' || $user->stripe_status=='4') && (!empty($user->stripe_account_id)))
            {
                $account_links = \Stripe\AccountLink::create([
                  'account'     => $user->stripe_account_id,
                  'refresh_url' => env('STRIPE_REFRESH_URL'),
                  'return_url'  => env('STRIPE_RETURN_URL'),
                  'type'        => 'account_onboarding',
                ]);

                return response(prepareResult(false, $account_links, getLangByLabelGroups('messages','message__address_detail_list')), config('http_response.success'));
            }
            return response()->json(prepareResult(true, 'Account already activated.', getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}
