<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ShippingCondition;
use App\Http\Resources\ShippingConditionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;

class ShippingConditionController extends Controller
{
    public function index()
    {
        try
        {
            $shippingConditions = Auth::user()->shippingConditions;
            return response(prepareResult(false, $shippingConditions, getLangByLabelGroups('messages','message__address_detail_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
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
            
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {

            ShippingCondition::where('user_id',Auth::id())->delete();

            foreach ($request->shipping_conditions as $key => $shipping_condition) 
            {
                if(!empty($shipping_condition["order_amount_from"]) || $shipping_condition["order_amount_from"] == '0')
                {
                    $shippingCondition = new ShippingCondition;
                    $shippingCondition->user_id             = Auth::user()->id;
                    $shippingCondition->order_amount_from   = $shipping_condition['order_amount_from'];
                    $shippingCondition->order_amount_to     = $shipping_condition['order_amount_to'];
                    $shippingCondition->discount_percent    = $shipping_condition['discount_percent'];
                    $shippingCondition->save();
                }
            }

            DB::commit();
            return response()->json(prepareResult(false, $shippingCondition, getLangByLabelGroups('messages','message_payment_card_detail_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ShippingCondition  $shippingCondition
     * @return \Illuminate\Http\Response
     */
    public function show(ShippingCondition $shippingCondition)
    {
        return response()->json(prepareResult(false, $shippingCondition, getLangByLabelGroups('messages','message_payment_card_detail_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ShippingCondition  $shippingCondition
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,ShippingCondition $shippingCondition)
    {
        $validation = Validator::make($request->all(), [
            'shipping_package'  => 'required',
            'order_amount_from'  => 'required',
            'order_amount_to'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $shippingCondition->user_id             = Auth::user()->id;
            $shippingCondition->order_amount_from   = $request->order_amount_from;
            $shippingCondition->order_amount_to     = $request->order_amount_to;
            $shippingCondition->discount_percent     = $request->discount_percent;
            $shippingCondition->save();
            DB::commit();
            return response()->json(prepareResult(false, $shippingCondition, getLangByLabelGroups('messages','message_payment_card_detail_updated')), config('http_response.success'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\ShippingCondition $shippingCondition
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(ShippingCondition $shippingCondition)
    {
        $shippingCondition->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_payment_card_detail_deleted')), config('http_response.success'));
    }
}
