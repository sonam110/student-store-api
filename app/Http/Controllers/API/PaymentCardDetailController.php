<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentCardDetail;
use App\Http\Resources\PaymentCardDetailResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;

class PaymentCardDetailController extends Controller
{
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
            $paymentCardDetail->mobile_number		= $request->mobile_number;
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
        $paymentCardDetail->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_payment_card_detail_deleted')), config('http_response.success'));
    }
}
