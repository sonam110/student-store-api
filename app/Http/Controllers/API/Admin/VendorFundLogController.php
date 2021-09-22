<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VendorFundTransfer;
use App\Models\OrderItem;
use Auth;

class VendorFundLogController extends Controller
{
    public function vendorFundTransferList(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $funds = VendorFundTransfer::orderBy('id', 'DESC')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $funds = VendorFundTransfer::orderBy('id', 'DESC')->get();
            }

            $totalEarning = OrderItem::sum('amount_transferred_to_vendor');
            $totalTransferred = OrderItem::where('user_id', Auth::id())
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

    public function vendorWiseFundTransferList(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $funds = VendorFundTransfer::where('user_id', $request->user_id)->orderBy('id', 'DESC')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $funds = VendorFundTransfer::where('user_id', $request->user_id)->orderBy('id', 'DESC')->get();
            }

            $totalEarning = OrderItem::where('user_id', $request->user_id)
                    ->sum('amount_transferred_to_vendor');
            $totalTransferred = OrderItem::where('is_transferred_to_vendor', 1)
                    ->where('user_id', $request->user_id)
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
