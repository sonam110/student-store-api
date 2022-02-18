<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use App\Models\ProductsServicesBook;
use App\Models\AddressDetail;
use App\Models\OrderTracking;
use App\Models\OrderItemReplacement;
use App\Models\OrderItemReturn;
use App\Models\OrderItemDispute;
use App\Models\PaymentCardDetail;
use App\Models\AppSetting;
use App\Models\EmailTemplate;
use App\Mail\OrderPlacedMail;
use App\Mail\OrderConfirmedMail;
use Mail;
use App\Models\ReasonForAction;
use App\Models\Contest;
use App\Models\ContestApplication;
use Session;
use App\Models\Package;
use App\Models\Language;


class OrderController extends Controller
{
	function __construct()
    {
        $this->lang_id = Language::select('id')->first()->id;
        if(!empty(request()->lang_id))
        {
            $this->lang_id = request()->lang_id;
        }
    }

	public function index(Request $request)
	{
		try
		{
			$lang_id = $this->lang_id;
			if(empty($lang_id))
	        {
	            $lang_id = Language::select('id')->first()->id;
	        }

			$orders = Order::orderBy('orders.created_at','DESC')->with('orderItems.productsServicesBook.user','orderItems.productsServicesBook.addressDetail','orderItems.productsServicesBook.categoryMaster','orderItems.productsServicesBook.subCategory','orderItems.orderTrackings','orderItems.return','orderItems.replacement','orderItems.dispute','orderItems.ratingAndFeedback')
			->with(['orderItems.productsServicesBook.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['orderItems.productsServicesBook.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }]);
			if(!empty($request->from_date))
			{
				$orders = $orders->whereDate('orders.created_at','>=',$request->from_date);
			}
			if(!empty($request->to_date))
			{
				$orders = $orders->whereDate('orders.created_at','<=',$request->to_date);
			}
			if(!empty($request->order_for))
			{
				$orders = $orders->where('orders.order_for',$request->order_for);
			}
			if(!empty($request->order_number))
			{
				$orders = $orders->where('orders.order_number','like','%'.$request->order_number.'%');
			}
			if(!empty($request->status))
			{
				$orders = $orders->join('order_items', function ($join) {
                        $join->on('orders.id', '=', 'order_items.order_id');
                    })
				->where('order_items.item_status',$request->status);
			}
			if(!empty($request->per_page_record))
			{
				$orders = $orders->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$orders = $orders->get();
			}
			return response(prepareResult(false, $orders, getLangByLabelGroups('messages','messages_order_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function show(Order $order)
	{
		$lang_id = $this->lang_id;
		if(empty($lang_id))
        {
            $lang_id = Language::select('id')->first()->id;
        }

		$order = Order::with('orderItems.productsServicesBook.user','orderItems.productsServicesBook.addressDetail','orderItems.productsServicesBook.categoryMaster','orderItems.orderTrackings','orderItems.return','orderItems.replacement','orderItems.dispute','orderItems.ratingAndFeedback')
		->with(['orderItems.productsServicesBook.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '1');
        }])
        ->with(['orderItems.productsServicesBook.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '0');
        }])
        ->find($order->id);
		return response()->json(prepareResult(false, $order, getLangByLabelGroups('messages','messages_order_list')), config('http_response.success'));
	}


	public function filter(Request $request)
	{
		try
		{
			$lang_id = $this->lang_id;
			if(empty($lang_id))
	        {
	            $lang_id = Language::select('id')->first()->id;
	        }
			
			$orderItems = OrderItem::select('order_items.*','orders.*', 'users.first_name', 'users.last_name', 'users.email', 'order_items.created_at as created_at')
			->join('orders', function ($join) {
                $join->on('order_items.order_id', '=', 'orders.id');
            })
			->join('users', function ($join) {
                $join->on('order_items.user_id', '=', 'users.id');
            })
			->orderBy('order_items.created_at','DESC')
			->with('productsServicesBook.user','productsServicesBook.addressDetail','productsServicesBook.categoryMaster','productsServicesBook.subCategory','orderTrackings','return','replacement','dispute','ratingAndFeedback','contestApplication.contest.user:id,first_name,last_name','contestApplication.contest.cancellationRanges','contestApplication.contest.contestWinners','contestApplication.contest.ratingAndFeedback')
			->with(['productsServicesBook.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
	            $q->select('id','category_master_id','title','slug')
	                ->where('language_id', $lang_id)
	                ->where('is_parent', '1');
	        }])
	        ->with(['productsServicesBook.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
	            $q->select('id','category_master_id','title','slug')
	                ->where('language_id', $lang_id)
	                ->where('is_parent', '0');
	        }]);
			if(!empty($request->from_date))
			{
				$orderItems = $orderItems->whereDate('order_items.created_at','>=',$request->from_date);
			}
			if(!empty($request->to_date))
			{
				$orderItems = $orderItems->whereDate('order_items.created_at','<=',$request->to_date);
			}
			if(!empty($request->product_type))
			{
				$orderItems = $orderItems->where('order_items.product_type',$request->product_type);
			}
			if(!empty($request->status))
			{
				$orderItems = $orderItems->where('order_items.item_status',$request->status);
			}
			if(!empty($request->order_number))
			{
				$orderItems = $orderItems->where('orders.order_number','like','%'.$request->order_number.'%');
			}
			if(!empty($request->order_for))
			{
				$orderItems = $orderItems->where('orders.order_for',$request->order_for);
			}
			if(!empty($request->user_type))
			{
				if($request->user_type == 'student')
				{
					$user_type_id = '2';
				}
				elseif($request->user_type == 'company')
				{
					$user_type_id = '3';
				}
				else
				{
					$user_type_id = '4';
				}
				$orderItems = $orderItems->where('users.user_type_id',$user_type_id);
			}

			if(!empty($request->email))
			{
				$orderItems = $orderItems->where('users.email','like','%'.$request->email.'%');
			}

			if(!empty($request->contact_number))
			{
				$orderItems = $orderItems->where('users.contact_number','like','%'.$request->contact_number.'%');
			}

			if(!empty($request->first_name))
			{
				$orderItems = $orderItems->where('users.first_name','like','%'.$request->first_name.'%');
			}

			if(!empty($request->last_name))
			{
				$orderItems = $orderItems->where('users.last_name','like','%'.$request->last_name.'%');
			}

			if(!empty($request->company_name))
			{
				$orderItems = $orderItems->join('service_provider_details', function ($join) {
	                        $join->on('users.id', '=', 'service_provider_details.user_id');
	                    })
				->where('service_provider_details.company_name','like','%'.$request->company_name.'%');
			}
						
			if(!empty($request->per_page_record))
			{
				$orderItems = $orderItems->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$orderItems = $orderItems->get();
			}
			return response(prepareResult(false, $orderItems, getLangByLabelGroups('messages','messages_order_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}
}
