<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItemDispute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use App\Models\OrderItem;
use App\Models\OrderTracking;

use DB;

class OrderItemDisputeController extends Controller
{
	public function index(Request $request)
	{
		try
		{
            $disputes = OrderItemDispute::with('orderItem:id,user_id,order_id,vendor_user_id,title,sku,price,quantity,reason_for_cancellation','disputeRaisedBy:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','disputeRaisedAgainst:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','reasonIdForDisputeDecline','reasonIdForReviewDecline')
            ->orderBy('created_at','DESC');

            if(!empty($request->dispute_status))
            {
                $disputes = $disputes->where('dispute_status',$request->dispute_status);
            }

            if(!empty($request->dispute_raised_by))
            {
                $disputes = $disputes->where('dispute_raised_by', 'LIKE', '%'.$request->dispute_raised_by.'%');
            }

            if(!empty($request->dispute_raised_against))
            {
                $disputes = $disputes->where('dispute_raised_against','%'.,'LIKE', $request->dispute_raised_against.'%');
            }

			if(!empty($request->per_page_record))
			{
			    $disputes = $disputes->with('orderItem')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
			    $disputes = $disputes->with('orderItem')->get();
			}
			return response(prepareResult(false, $disputes, getLangByLabelGroups('messages','message_disputes_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}


	public function show($id)
	{	
        $orderItemDispute = OrderItemDispute::with('orderItem:id,user_id,order_id,vendor_user_id,title,sku,price,quantity,reason_for_cancellation','orderItem.user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','orderItem.vendor:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','reasonIdForDisputeDecline','reasonIdForReviewDecline')->find($id);	
		return response()->json(prepareResult(false, $orderItemDispute, getLangByLabelGroups('messages','message_disputes_list')), config('http_response.success'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\OrderItemDispute  $orderItemDispute
	 * @return \Illuminate\Http\Response
	 */
	

   
	public function destroy(OrderItemDispute $orderItemDispute)
	{
		$orderItemDispute->delete();
		return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_disputes_deleted')), config('http_response.success'));
	}

    public function resolve(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'status'    => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        if($request->status=='undefined')
        {
            return response(prepareResult(true, ['status is undefined.'], getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        $orderItemDispute = OrderItemDispute::find($id);
        $orderItem = OrderItem::find($orderItemDispute->order_item_id);

        if($request->status == 'resolved_to_customer')
        {
            $item_status = 'canceled';
            $reason_for_cancellation = $request->admin_remarks;

            $refundOrderItemId = $orderItem->id;
            $refundOrderItemPrice = $orderItem->price_after_apply_reward_points;
            $refundOrderItemQuantity = $orderItem->quantity;
            $refundOrderItemReason = 'resolved_to_customer_by_admin';

            $isRefunded = refund($refundOrderItemId,$refundOrderItemPrice,$refundOrderItemQuantity,$refundOrderItemReason);

            $orderItem->canceled_refunded_amount = $refundOrderItemPrice * $refundOrderItemQuantity;
            $orderItem->returned_rewards = ceil($orderItem->used_item_reward_points / $refundOrderItemQuantity);


            if($isRefunded=='failed')
            {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
            }

            //Update price in order_item
            $orderItem->used_item_reward_points = ceil($orderItem->used_item_reward_points / $orderItem->quantity) * ($orderItem->quantity - $refundOrderItemQuantity);
            $orderItem->earned_reward_points = ceil($orderItem->earned_reward_points / $orderItem->quantity) * ($orderItem->quantity - $refundOrderItemQuantity);
            $orderItem->amount_transferred_to_vendor = round(($orderItem->amount_transferred_to_vendor / $orderItem->quantity) * ($orderItem->quantity - $refundOrderItemQuantity), 2);
            $orderItem->student_store_commission = round(($orderItem->student_store_commission / $orderItem->quantity) * ($orderItem->quantity - $refundOrderItemQuantity), 2);
            $orderItem->cool_company_commission = round(($orderItem->cool_company_commission / $orderItem->quantity) * ($orderItem->quantity - $refundOrderItemQuantity), 2);
            $orderItem->vat_amount = round(($orderItem->vat_amount / $orderItem->quantity) * ($orderItem->quantity - $refundOrderItemQuantity), 2);
            $orderItem->save(); 

            $orderQuantity = $orderItem->quantity;
        }
        else
        {
            $item_status = 'completed';
            $reason_for_cancellation = $orderItem->reason_for_cancellation;
        }

        
        $orderItemDispute->admin_remarks = $request->admin_remarks;
        $orderItemDispute->dispute_status = $request->status;
        $orderItemDispute->date_of_dispute_completed     = date('Y-m-d');
        $orderItemDispute->save();

        

        // if($orderItem->item_status == 'cancelled')
        // {
        //     $type = 'dispute';
        // }
        // else
        // {
        //     $type = 'delivery';
        // }

        $orderTracking = new OrderTracking;
        $orderTracking->order_item_id = $orderItemDispute->order_item_id;
        $orderTracking->status = $item_status; 
        $orderTracking->comment = $request->admin_remarks;
        $orderTracking->type = 'delivery';
        $orderTracking->save();

        $orderTracking = new OrderTracking;
        $orderTracking->order_item_id = $orderItemDispute->order_item_id;
        $orderTracking->status = $request->status; 
        $orderTracking->comment = $request->admin_remarks;
        $orderTracking->type = 'dispute';
        $orderTracking->save();

        
        $orderItem->item_status             = $item_status;
        $orderItem->reason_for_cancellation = $reason_for_cancellation;
        $orderItem->save();

        $orderItem = OrderItem::with('orderTrackings','return','replacement','dispute')->find($orderItem->id);
        return response()->json(prepareResult(false, $orderItem, getLangByLabelGroups('messages','messages_order_item_dispute_resolved')), config('http_response.success'));
    }

}
