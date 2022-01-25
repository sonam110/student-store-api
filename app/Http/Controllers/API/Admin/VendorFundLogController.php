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

            $totalEarning = OrderItem::where('item_status','completed')->sum('amount_transferred_to_vendor');
            $totalTransferred = OrderItem::where('item_status','completed')->where('is_transferred_to_vendor', 1)
                    ->sum('amount_transferred_to_vendor');
            $coolCompanyCommission = OrderItem::where('item_status','completed')->sum('cool_company_commission');
            $studentStoreCommission = OrderItem::where('item_status','completed')->sum('student_store_commission');

            $returnObject = [
                'totalEarning'          => $totalEarning,
                'totalTransferred'      => $totalTransferred,
                'coolCompanyCommission' => $coolCompanyCommission,
                'studentStoreCommission'=> $studentStoreCommission,
                'transferred_log'       => $funds
            ];

            return response(prepareResult(false, $returnObject, getLangByLabelGroups('messages','message_abuse_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
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

            $totalEarning = OrderItem::select('order_items.id')
            ->where('item_status','completed')
            ->where('order_items.vendor_user_id',$request->user_id)
            ->sum('order_items.amount_transferred_to_vendor');

            $totalTransferred = OrderItem::select('order_items.id')
            ->where('item_status','completed')
            ->where('order_items.vendor_user_id',$request->user_id)
            ->where('order_items.is_transferred_to_vendor',1)
            ->sum('order_items.amount_transferred_to_vendor');

            $totalPending = OrderItem::select('order_items.id')
            ->where('item_status','completed')
            ->where('order_items.vendor_user_id',$request->user_id)
            ->where('order_items.is_transferred_to_vendor',0)
            ->sum('order_items.amount_transferred_to_vendor');

            $studentStoreCommission = OrderItem::select('order_items.id')
            ->where('item_status','completed')
            ->where('order_items.vendor_user_id',$request->user_id)
            ->sum('order_items.student_store_commission');

            $coolCompanyCommission = OrderItem::select('order_items.id')
            ->where('item_status','completed')
            ->where('order_items.vendor_user_id',$request->user_id)
            ->sum('order_items.cool_company_commission');

            $returnObject = [
                'totalEarning'          => $totalEarning,
                'totalTransferred'      => $totalTransferred,
                'totalPending'          => $totalPending,
                'studentStoreCommission'=> $studentStoreCommission,
                'coolCompanyCommission' => $coolCompanyCommission,
                'transferred_log'       => $funds
            ];

            return response(prepareResult(false, $returnObject, getLangByLabelGroups('messages','message_abuse_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function pendingVendorsFundToTransferred(Request $request)
    {
        $today          = new \DateTime();
        $before15Days   = $today->sub(new \DateInterval('P15D'))->format('Y-m-d');

        $getListsProducts = OrderItem::select('users.id as user_id','users.first_name','users.last_name','users.email','users.stripe_account_id','users.stripe_status', \DB::raw('SUM(order_items.amount_transferred_to_vendor) as total_amount'))
            ->join('users', 'users.id','=','order_items.vendor_user_id')
            ->join('orders', 'orders.id','=','order_items.order_id')
            ->whereNotNull('order_items.vendor_user_id')
            ->whereIn('order_items.item_status',['completed', 'replaced', 'returned'])
            ->whereRaw("(CASE WHEN order_items.is_disputed = 1 THEN order_items.disputes_resolved_in_favour = 1 ELSE order_items.is_disputed=0 END)")
            ->where('orders.payment_status', 'paid')
            ->where('order_items.is_transferred_to_vendor', 0)
            ->where('order_items.amount_transferred_to_vendor', '>', 0)
            ->whereDate('order_items.delivery_completed_date', '<=', $before15Days)
            ->orderBy('order_items.auto_id', 'ASC')
            ->groupBy('order_items.vendor_user_id');

        if(!empty($request->per_page_record))
        {
            $getListsProducts = $getListsProducts->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
        }
        else
        {
            $getListsProducts = $getListsProducts->get();
        }
        return response(prepareResult(false, $getListsProducts, 'Pending amount for transferred'), config('http_response.success'));
    }

    public function pendingVendorFundToTransferred(Request $request, $user_id)
    {
        $today          = new \DateTime();
        $before15Days   = $today->sub(new \DateInterval('P15D'))->format('Y-m-d');

        $userInfoPendingToTrans = OrderItem::select('order_items.*')
            ->join('users', 'users.id','=','order_items.vendor_user_id')
            ->join('orders', 'orders.id','=','order_items.order_id')
            ->whereNotNull('order_items.vendor_user_id')
            ->whereIn('order_items.item_status',['completed', 'replaced', 'returned'])
            ->whereRaw("(CASE WHEN order_items.is_disputed = 1 THEN order_items.disputes_resolved_in_favour = 1 ELSE order_items.is_disputed=0 END)")
            ->where('orders.payment_status', 'paid')
            ->where('order_items.is_transferred_to_vendor', 0)
            ->where('order_items.amount_transferred_to_vendor', '>', 0)
            ->whereDate('order_items.delivery_completed_date', '<=', $before15Days)
            ->where('order_items.vendor_user_id', $user_id)
            ->get();

        
        $userInfoPendingToTransTotalProducts = OrderItem::select(\DB::raw('SUM(order_items.amount_transferred_to_vendor) as pending_amount_transferred_to_vendor, SUM(order_items.student_store_commission) as student_store_commission, SUM(order_items.cool_company_commission) as cool_company_commission, SUM(order_items.quantity * order_items.price) as total_order_amount'))
            ->join('users', 'users.id','=','order_items.vendor_user_id')
            ->join('orders', 'orders.id','=','order_items.order_id')
            ->whereNotNull('order_items.vendor_user_id')
            ->whereIn('order_items.item_status',['completed', 'replaced', 'returned'])
            ->whereRaw("(CASE WHEN order_items.is_disputed = 1 THEN order_items.disputes_resolved_in_favour = 1 ELSE order_items.is_disputed=0 END)")
            ->where('orders.payment_status', 'paid')
            ->where('order_items.is_transferred_to_vendor', 0)
            ->where('order_items.amount_transferred_to_vendor', '>', 0)
            ->whereDate('order_items.delivery_completed_date', '<=', $before15Days)
            ->where('order_items.vendor_user_id', $user_id)
            ->first();

        
        $returnObj = [
            'orderList'         => $userInfoPendingToTrans,
            'products_total'    => $userInfoPendingToTransTotalProducts
        ];

        return response(prepareResult(false, $returnObj, 'Pending amount for transferred'), config('http_response.success'));
    }
}
