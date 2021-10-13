<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentCardDetail;
use App\Models\User;
use App\Http\Resources\PaymentCardDetailResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use Stripe;
use mervick\aesEverywhere\AES256;
use App\Models\PaymentGatewaySetting;

class PaymentCardDetailController extends Controller
{
    function __construct()
    {
        $this->paymentInfo = PaymentGatewaySetting::first();
    }

    public function index()
    {
        try
        {
            $paymentCardDetails = Auth::user()->paymentCardDetails;
            return response(prepareResult(false, $paymentCardDetails, getLangByLabelGroups('messages','message__address_detail_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {        
        $validation = Validator::make($request->all(), [
            'card_number'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $stripe = new \Stripe\StripeClient($this->paymentInfo->payment_gateway_secret);
            $checkUser = User::find(Auth::id());
            $customerId = $checkUser->stripe_customer_id;
            if(empty($customerId))
            {
                $first_name = AES256::decrypt($checkUser->first_name, env('ENCRYPTION_KEY'));
                $last_name = (!empty($checkUser->last_name)) ? AES256::decrypt($checkUser->last_name, env('ENCRYPTION_KEY')) : null;
                $contact_number = AES256::decrypt($checkUser->contact_number, env('ENCRYPTION_KEY'));
                $email = AES256::decrypt($checkUser->email, env('ENCRYPTION_KEY'));

                $account = $stripe->customers->create([
                    'name'              => $first_name .' '.$last_name,
                    'phone'             => $contact_number,
                    'email'             => $email,
                    'description'       => 'New Customer added',
                ]);
                $customerId = $account->id;

                $checkUser->stripe_customer_id = $customerId;
                $checkUser->save();
            }
            if(empty($customerId))
            {
                return response()->json(prepareResult(true, $customerId, getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
            }
            $cardExpiry = explode('/', AES256::decrypt($request->card_expiry, env('ENCRYPTION_KEY')));

            $paymentMethods = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'number' => AES256::decrypt($request->card_number, env('ENCRYPTION_KEY')),
                    'exp_month' => $cardExpiry[0],
                    'exp_year'  => $cardExpiry[1],
                    'cvc'       => AES256::decrypt($request->card_cvv, env('ENCRYPTION_KEY')),
                ],
            ]);

            $paymentMethodAttach = $stripe->paymentMethods->attach(
                $paymentMethods->id,
                ['customer' => $customerId]
            );

            if(empty($paymentMethods->id))
            {
                return response()->json(prepareResult(true, $paymentMethods, getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
            }
        	if($request->is_default == true)
        	{
        		PaymentCardDetail::where('user_id',Auth::id())->update(['is_default'=>false]);
        	}
            $paymentCardDetail = new PaymentCardDetail;
            $paymentCardDetail->user_id             = Auth::user()->id;
            $paymentCardDetail->card_number         = $request->card_number;
            $paymentCardDetail->card_type           = $request->card_type;
            $paymentCardDetail->card_cvv            = $request->card_cvv;
            $paymentCardDetail->card_expiry         = $request->card_expiry;
            $paymentCardDetail->card_holder_name    = $request->card_holder_name;
            $paymentCardDetail->is_default          = $request->is_default;
            $paymentCardDetail->status              = 1;
            $paymentCardDetail->is_minor            = $request->is_minor;
            $paymentCardDetail->parent_full_name    = $request->parent_full_name;
            $paymentCardDetail->mobile_number       = $request->mobile_number;
            $paymentCardDetail->stripe_payment_method_id      = $paymentMethods->id;
            $paymentCardDetail->save();

            DB::commit();
            return response()->json(prepareResult(false, $paymentCardDetail, getLangByLabelGroups('messages','message_payment_card_detail_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PaymentCardDetail  $paymentCardDetail
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentCardDetail $paymentCardDetail)
    {
        return response()->json(prepareResult(false, $paymentCardDetail, getLangByLabelGroups('messages','message_payment_card_detail_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PaymentCardDetail  $paymentCardDetail
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,PaymentCardDetail $paymentCardDetail)
    {
        $validation = Validator::make($request->all(), [
            'card_number' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $stripe = new \Stripe\StripeClient($this->paymentInfo->payment_gateway_secret);
            $checkUser = User::find(Auth::id());
            $customerId = $checkUser->stripe_customer_id;
            if(empty($customerId))
            {
                $first_name = AES256::decrypt($checkUser->first_name, env('ENCRYPTION_KEY'));
                $last_name = (!empty($checkUser->last_name)) ? AES256::decrypt($checkUser->last_name, env('ENCRYPTION_KEY')) : null;
                $contact_number = AES256::decrypt($checkUser->contact_number, env('ENCRYPTION_KEY'));
                $email = AES256::decrypt($checkUser->email, env('ENCRYPTION_KEY'));

                $account = $stripe->customers->create([
                    'name'              => $first_name .' '.$last_name,
                    'phone'             => $contact_number,
                    'email'             => $email,
                    'description'       => 'New Customer added',
                ]);
                $customerId = $account->id;

                $checkUser->stripe_customer_id = $customerId;
                $checkUser->save();
            }

            $cardExpiry = explode('/', AES256::decrypt($request->card_expiry, env('ENCRYPTION_KEY')));

            if(!empty($paymentCardDetail->stripe_payment_method_id))
            {
                $stripe->paymentMethods->detach(
                    $paymentCardDetail->stripe_payment_method_id,
                    []
                );
            }

            $paymentMethods = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'number' => AES256::decrypt($request->card_number, env('ENCRYPTION_KEY')),
                    'exp_month' => $cardExpiry[0],
                    'exp_year'  => $cardExpiry[1],
                    'cvc'       => AES256::decrypt($request->card_cvv, env('ENCRYPTION_KEY')),
                ],
            ]);

            $paymentMethodAttach = $stripe->paymentMethods->attach(
                $paymentMethods->id,
                ['customer' => $customerId]
            );

            if(empty($paymentMethods->id))
            {
                return response()->json(prepareResult(true, $paymentMethods, getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
            }

            $paymentCardDetail->user_id             = Auth::user()->id;
            $paymentCardDetail->card_number         = $request->card_number;
            $paymentCardDetail->card_type           = $request->card_type;
            $paymentCardDetail->card_cvv            = $request->card_cvv;
            $paymentCardDetail->card_expiry         = $request->card_expiry;
            $paymentCardDetail->card_holder_name    = $request->card_holder_name;
            $paymentCardDetail->is_default          = $request->is_default;
            $paymentCardDetail->status              = 1;
            $paymentCardDetail->is_minor            = $request->is_minor;
            $paymentCardDetail->parent_full_name    = $request->parent_full_name;
            $paymentCardDetail->mobile_number       = $request->mobile_number;
            $paymentCardDetail->stripe_payment_method_id      = $paymentMethods->id;
            $paymentCardDetail->save();
            DB::commit();
            return response()->json(prepareResult(false, $paymentCardDetail, getLangByLabelGroups('messages','message_payment_card_detail_updated')), config('http_response.success'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\PaymentCardDetail $paymentCardDetail
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(PaymentCardDetail $paymentCardDetail)
    {
        $stripe = new \Stripe\StripeClient($this->paymentInfo->payment_gateway_secret);
        $checkUser = User::find(Auth::id());
        $customerId = $checkUser->stripe_customer_id;
        if(!empty($paymentCardDetail->stripe_payment_method_id))
        {
            $stripe->paymentMethods->detach(
                $paymentCardDetail->stripe_payment_method_id,
                []
            );
        }
        $paymentCardDetail->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_payment_card_detail_deleted')), config('http_response.success'));
    }
}
