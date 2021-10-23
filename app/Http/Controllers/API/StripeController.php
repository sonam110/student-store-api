<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VendorFundTransfer;
use App\Models\OrderItem;
use Auth;
use Stripe;
use App\Models\PaymentGatewaySetting;

class StripeController extends Controller
{
    function __construct()
    {
        $this->paymentInfo = PaymentGatewaySetting::first();
    }

    public function createStripeAccount()
    {
        try
        {
            $user = User::select('id','stripe_account_id','stripe_status','stripe_create_timestamp')->find(Auth::id());
            if($user->stripe_status=='1' || $user->stripe_status==null)
            {
                \Stripe\Stripe::setApiKey($this->paymentInfo->payment_gateway_secret);
                $account = \Stripe\Account::create([
                  'type' => 'standard',
                ]);

                $user->stripe_account_id = $account['id'];
                $user->stripe_status = '2';
                $user->stripe_create_timestamp = date('Y-m-d H:i:s');
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
            $user = User::select('id','stripe_account_id','stripe_status')->find(Auth::id());
            if(($user->stripe_status=='2' || $user->stripe_status=='4') && (!empty($user->stripe_account_id)))
            {
                \Stripe\Stripe::setApiKey($this->paymentInfo->payment_gateway_secret);
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

    public function checkStripeAccountCurrentStatus($user_id, $account_id)
    {
        try
        {
            $user = User::select('id','stripe_account_id','stripe_status')->where('stripe_account_id', $account_id)->find($user_id);
            if($user)
            {
                $stripe = new \Stripe\StripeClient($this->paymentInfo->payment_gateway_secret);
                $accountStatus = $stripe->accounts->retrieve(
                  $user->stripe_account_id,
                  []
                );
                if(is_null($accountStatus->verification->disabled_reason))
                {
                    $user->stripe_status = '3';
                    $message = 'Account is activated.';
                }
                else
                {
                    $user->stripe_status = '4';
                    $message = 'Account is under verification.';
                }
                $user->stripe_create_timestamp = date('Y-m-d H:i:s');
                $user->save();

                return response(prepareResult(false, $message, getLangByLabelGroups('messages','message__address_detail_list')), config('http_response.success'));
            }
            return response()->json(prepareResult(true, 'Account not found.', getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function vendorFundTransferList(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $funds = VendorFundTransfer::where('user_id', Auth::id())->orderBy('id', 'DESC')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $funds = VendorFundTransfer::where('user_id', Auth::id())->orderBy('id', 'DESC')->get();
            }

            $totalEarning = OrderItem::where('user_id', Auth::id())
                    ->sum('amount_transferred_to_vendor');
            $totalTransferred = OrderItem::where('is_transferred_to_vendor', 1)
                    ->where('user_id', Auth::id())
                    ->sum('amount_transferred_to_vendor');

            $returnObject = [
                'totalEarning'      => $totalEarning,
                'totalTransferred'  => $totalTransferred,
                'transferred_log'   => $funds
            ];

            return response(prepareResult(false, $returnObject, getLangByLabelGroups('messages','message_abuse_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}
