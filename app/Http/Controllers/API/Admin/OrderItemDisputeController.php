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
            $disputes = OrderItemDispute::orderBy('created_at','DESC');
            if(!empty($request->dispute_status))
            {
                $disputes = $disputes->where('dispute_status',$request->dispute_status);
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
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}


	public function show($id)
	{	
        $orderItemDispute = OrderItemDispute::find($id);	
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
        $orderItemDispute = OrderItemDispute::find($id);
        $orderItem = OrderItem::find($orderItemDispute->order_item_id);

        if($request->status == 'resolved_to_customer')
        {
            $item_status = 'canceled';
            $reason_for_cancellation = $request->admin_remarks;
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
