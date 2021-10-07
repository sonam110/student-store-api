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
            $totalTransferred = OrderItem::where('is_transferred_to_vendor', 1)
                    ->sum('amount_transferred_to_vendor');
            $coolCompanyCommission = OrderItem::sum('cool_company_commission');
            $studentStoreCommission = OrderItem::sum('student_store_commission');

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



            // $totalEarning = OrderItem::where('user_id', $request->user_id)
                    // ->sum('amount_transferred_to_vendor');

            $totalEarning = OrderItem::select('order_items.id')
            ->join('products_services_books',function ($join) {
                $join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
            })
            ->where('products_services_books.user_id',$request->user_id)
            ->sum('order_items.amount_transferred_to_vendor');
            + OrderItem::select('order_items.id')
            ->join('contest_applications',function ($join) {
                $join->on('order_items.contest_application_id', '=', 'contest_applications.id');
            })
            ->join('contests',function ($join) {
                $join->on('contest_applications.contest_id', '=', 'contests.id');
            })
            ->where('contests.user_id',$request->user_id)
            ->sum('order_items.amount_transferred_to_vendor');

            // $totalTransferred = OrderItem::where('is_transferred_to_vendor', 1)
            //         ->where('user_id', $request->user_id)
            //         ->sum('amount_transferred_to_vendor');

            $totalTransferred = OrderItem::select('order_items.id')
            ->join('products_services_books',function ($join) {
                $join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
            })
            ->where('products_services_books.user_id',$request->user_id)
            ->where('order_items.is_transferred_to_vendor',1)
            ->sum('order_items.amount_transferred_to_vendor');
            + OrderItem::select('order_items.id')
            ->join('contest_applications',function ($join) {
                $join->on('order_items.contest_application_id', '=', 'contest_applications.id');
            })
            ->join('contests',function ($join) {
                $join->on('contest_applications.contest_id', '=', 'contests.id');
            })
            ->where('contests.user_id',$request->user_id)
            ->where('order_items.is_transferred_to_vendor',1)
            ->sum('order_items.amount_transferred_to_vendor');



            $totalPending = OrderItem::select('order_items.id')
            ->join('products_services_books',function ($join) {
                $join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
            })
            ->where('products_services_books.user_id',$request->user_id)
            ->where('order_items.is_transferred_to_vendor',0)
            ->sum('order_items.amount_transferred_to_vendor');
            + OrderItem::select('order_items.id')
            ->join('contest_applications',function ($join) {
                $join->on('order_items.contest_application_id', '=', 'contest_applications.id');
            })
            ->join('contests',function ($join) {
                $join->on('contest_applications.contest_id', '=', 'contests.id');
            })
            ->where('contests.user_id',$request->user_id)
            ->where('order_items.is_transferred_to_vendor',0)
            ->sum('order_items.amount_transferred_to_vendor');


            $studentStoreCommission = OrderItem::select('order_items.id')
            ->join('products_services_books',function ($join) {
                $join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
            })
            ->where('products_services_books.user_id',$request->user_id)
            ->sum('order_items.student_store_commission');
            + OrderItem::select('order_items.id')
            ->join('contest_applications',function ($join) {
                $join->on('order_items.contest_application_id', '=', 'contest_applications.id');
            })
            ->join('contests',function ($join) {
                $join->on('contest_applications.contest_id', '=', 'contests.id');
            })
            ->where('contests.user_id',$request->user_id)
            ->sum('order_items.student_store_commission');


            $coolCompanyCommission = OrderItem::select('order_items.id')
            ->join('products_services_books',function ($join) {
                $join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
            })
            ->where('products_services_books.user_id',$request->user_id)
            ->sum('order_items.cool_company_commission');
            + OrderItem::select('order_items.id')
            ->join('contest_applications',function ($join) {
                $join->on('order_items.contest_application_id', '=', 'contest_applications.id');
            })
            ->join('contests',function ($join) {
                $join->on('contest_applications.contest_id', '=', 'contests.id');
            })
            ->where('contests.user_id',$request->user_id)
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
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}
