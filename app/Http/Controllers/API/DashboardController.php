<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Job;
use App\Models\Contest;
use App\Models\ProductsServicesBook;
use App\Models\Language;

class DashboardController extends Controller
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
			$data = [];
			$data['total_students']         = 0;
			$data['total_companies']        = 0;
			$data['total_normal_users']     = 0;
			$data['total_products']         = ProductsServicesBook::select('id')->where('user_id',Auth::id())->where('type','product')->count();
			$data['total_services']         = ProductsServicesBook::select('id')->where('user_id',Auth::id())->where('type','service')->count();
			$data['total_books']            = ProductsServicesBook::select('id')->where('user_id',Auth::id())->where('type','book')->count();
			$data['total_contests']         = Contest::select('id')->where('user_id',Auth::id())->where('type','contest')->count();
			$data['total_events']           = Contest::select('id')->where('user_id',Auth::id())->where('type','event')->count();
			$data['total_jobs']             = Job::select('id')->where('user_id',Auth::id())->count();
            // $data['total_orders']        = Order::select('id')->count();

			$data['total_spends']           = OrderItem::join('orders', 'orders.id','=','order_items.order_id')
				->where('orders.payment_status', 'paid')
				->where('order_items.user_id', Auth::id())
				->sum(\DB::raw('order_items.price * order_items.quantity'));

			$data['total_orders']           = OrderItem::select('order_items.id')
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.vendor_user_id',Auth::id())
			->count();

			$data['order_completed']        = OrderItem::select('order_items.id')
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.vendor_user_id',Auth::id())
			->where('order_items.item_status','completed')
			->count();

			$data['order_under_process']    = OrderItem::select('order_items.id')
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.vendor_user_id',Auth::id())
			->where('order_items.item_status','processing')
			->count();
			

			$data['order_delivered']        = OrderItem::select('order_items.id')
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.vendor_user_id',Auth::id())
			->where('order_items.item_status','delivered')
			->count();

			$data['total_earnings'] = OrderItem::select('order_items.id')
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.vendor_user_id',Auth::id())
			->sum('order_items.amount_transferred_to_vendor');


			$data['total_amount_refunded']  = OrderItem::select('order_items.id')
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.vendor_user_id',Auth::id())
			->sum('order_items.amount_returned');
			
			$data['total_returned_items']   = OrderItem::select('order_items.id')
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->join('order_item_returns', 'order_item_returns.order_item_id','=','order_items.id')
			->where('order_items.vendor_user_id',Auth::id())
			->where('order_items.item_status','returned')
			->sum('order_item_returns.quantity');
			
			return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function salesReport(Request $request)
	{
		try
		{
			$days = $request->days;  

			$data = [];
			for($i = 1; $i<=$days; $i++)
			{
				$date = date('Y-m-d',strtotime('-'.($i-1).' days'));
				$total_sales_book = OrderItem::select( 
					\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
				->join('orders', 'orders.id','=','order_items.order_id')
				->where('orders.payment_status', 'paid')
				->where('order_items.product_type','book')
				->where('order_items.created_at','like','%'.$date.'%')
				->where('order_items.vendor_user_id',Auth::id());


				$total_sales_service = OrderItem::select( 
					\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
				->join('orders', 'orders.id','=','order_items.order_id')
				->where('orders.payment_status', 'paid')
				->where('order_items.product_type','service')
				->where('order_items.created_at','like','%'.$date.'%')
				->where('order_items.vendor_user_id',Auth::id());


				$total_sales_product = OrderItem::select( 
					\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
				->join('orders', 'orders.id','=','order_items.order_id')
				->where('orders.payment_status', 'paid')
				->where('order_items.product_type','product')
				->where('order_items.created_at','like','%'.$date.'%')
				->where('order_items.vendor_user_id',Auth::id());

				$contestOrders = OrderItem::select( 
					\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
				->join('orders', 'orders.id','=','order_items.order_id')
				->where('orders.payment_status', 'paid')
				->where('order_items.contest_type','contest')
				->whereDate('order_items.created_at','like','%'.$date.'%')
				->where('order_items.vendor_user_id',Auth::id());


				$eventOrders = OrderItem::select( 
					\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
				->join('orders', 'orders.id','=','order_items.order_id')
				->where('orders.payment_status', 'paid')
				->where('order_items.contest_type','event')
				->where('order_items.created_at','like','%'.$date.'%')
				->where('order_items.vendor_user_id',Auth::id());
				

				$data[$i-1]['date'] = $date;
				$data[$i-1]['total_sales_book']     = $total_sales_book->count();
				$data[$i-1]['total_amount_book']    = $total_sales_book->get()[0]['total_amount'];
				$data[$i-1]['total_sales_service']  = $total_sales_service->count();
				$data[$i-1]['total_amount_service'] = $total_sales_service->get()[0]['total_amount'];
				$data[$i-1]['total_sales_product']  = $total_sales_product->count();
				$data[$i-1]['total_amount_product'] = $total_sales_product->get()[0]['total_amount'];
				$data[$i-1]['total_sales_contest']  = $contestOrders->count();
				$data[$i-1]['total_amount_contest'] = $contestOrders->get()[0]['total_amount'];
				$data[$i-1]['total_sales_event']    = $eventOrders->count();
				$data[$i-1]['total_amount_event']   = $eventOrders->get()[0]['total_amount'];
				
				$date_arr[] = date('d M', strtotime($data[$i-1]['date']));
				$total_sales_book_arr[] = $data[$i-1]['total_sales_book'];
				$total_amount_book_arr[] = ($data[$i-1]['total_amount_book']>0) ? $data[$i-1]['total_amount_book'] : 0;
				
				$total_sales_service_arr[] = $data[$i-1]['total_sales_service'];
				$total_amount_service_arr[] = ($data[$i-1]['total_amount_service']>0) ? $data[$i-1]['total_amount_service'] : 0;
				
				$total_sales_product_arr[] = $data[$i-1]['total_sales_product'];
				$total_amount_product_arr[] = ($data[$i-1]['total_amount_product']>0) ? $data[$i-1]['total_amount_product'] : 0;
				
				$total_sales_contest_arr[] = $data[$i-1]['total_sales_contest'];
				$total_amount_contest_arr[] = ($data[$i-1]['total_amount_contest']>0) ? $data[$i-1]['total_amount_contest'] : 0;

				$total_sales_event_arr[] = $data[$i-1]['total_sales_event'];
				$total_amount_event_arr[] = ($data[$i-1]['total_amount_event']>0) ? $data[$i-1]['total_amount_event'] : 0;

			}
			$object = [
					'date' => implode(', ', $date_arr),
					'total_sales_book' => implode(', ', $total_sales_book_arr),
					'total_amount_book' => implode(', ', $total_amount_book_arr),
					'total_sales_service' => implode(', ', $total_sales_service_arr),
					'total_amount_service' => implode(', ', $total_amount_service_arr),
					'total_sales_product' => implode(', ', $total_sales_product_arr),
					'total_amount_product' => implode(', ', $total_amount_product_arr),
					'total_sales_contest' => implode(', ', $total_sales_contest_arr),
					'total_amount_contest' => implode(', ', $total_amount_contest_arr),
					'total_sales_event' => implode(', ', $total_sales_event_arr),
					'total_amount_event' => implode(', ', $total_amount_event_arr),
				];
			return response(prepareResult(false, $object, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function recentOrderList(Request $request)
	{
		try
		{
			$lang_id = $this->lang_id;
			if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

			$total_sales_book_list = OrderItem::join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.product_type','book')
			->limit(10)
			->with('productsServicesBook.user:id,first_name,last_name,user_type_id','productsServicesBook.categoryMaster','productsServicesBook.subCategory','order.user:id,first_name,last_name,user_type_id')
			->with(['productsServicesBook.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['productsServicesBook.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
			->orderBy('order_items.created_at','desc')
			->where('products_services_books.user_id',Auth::id());


			$total_sales_service_list = OrderItem::join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.product_type','service')
			->limit(10)
			->with('productsServicesBook.user:id,first_name,last_name,user_type_id','productsServicesBook.categoryMaster','productsServicesBook.subCategory','order.user:id,first_name,last_name,user_type_id')
			->with(['productsServicesBook.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['productsServicesBook.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
			->orderBy('order_items.created_at','desc')
			->where('products_services_books.user_id',Auth::id());


			$total_sales_product_list = OrderItem::join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.product_type','product')
			->limit(10)
			->with('productsServicesBook.user:id,first_name,last_name,user_type_id','productsServicesBook.categoryMaster','productsServicesBook.subCategory','order.user:id,first_name,last_name,user_type_id')
			->with(['productsServicesBook.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['productsServicesBook.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
			->orderBy('order_items.created_at','desc')
			->where('products_services_books.user_id',Auth::id());


			$total_sales_contest_list = OrderItem::join('contest_applications', function ($join) {
				$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
			})
			->join('contests', function ($join) {
				$join->on('contest_applications.contest_id', '=', 'contests.id');
			})
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.contest_type','contest')
			->limit(10)
			->with('contestApplication.contest.user:id,first_name,last_name,user_type_id','contestApplication.contest.categoryMaster','contestApplication.contest.subCategory','order.user:id,first_name,last_name,user_type_id')
			->with(['contestApplication.contest.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['contestApplication.contest.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
			->orderBy('order_items.created_at','desc')
			->where('contests.user_id',Auth::id());


			$total_sales_event_list = OrderItem::join('contest_applications', function ($join) {
				$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
			})
			->join('contests', function ($join) {
				$join->on('contest_applications.contest_id', '=', 'contests.id');
			})
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.contest_type','event')
			->limit(10)
			->with('contestApplication.contest.user:id,first_name,last_name,user_type_id','contestApplication.contest.categoryMaster','contestApplication.contest.subCategory','order.user:id,first_name,last_name,user_type_id')
			->with(['contestApplication.contest.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['contestApplication.contest.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
			->orderBy('order_items.created_at','desc')
			->where('contests.user_id',Auth::id());


			$data['total_sales_book_list']     = $total_sales_book_list->get();
			$data['total_sales_service_list']  = $total_sales_service_list->get();
			$data['total_sales_product_list']  = $total_sales_product_list->get();
			$data['total_sales_contest_list']  = $total_sales_contest_list->get();
			$data['total_sales_event_list']    = $total_sales_event_list->get();
			return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		} 
	}

	public function topSellingList(Request $request)
	{
		try
		{
			$lang_id = $this->lang_id;
			if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }
			
			$top_sales_book_list = OrderItem::select('order_items.*',\DB::raw('COUNT(order_items.id) as total_order_count', 'order_items.products_services_book_id'),\DB::raw('sum(order_items.quantity) as total_sell_count','order_items.products_services_book_id'))
			->join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.product_type','book')
			->limit(5)
			->with('productsServicesBook.user:id,first_name,last_name,user_type_id','productsServicesBook.categoryMaster','productsServicesBook.subCategory')
			->with(['productsServicesBook.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['productsServicesBook.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
			->groupBy('order_items.products_services_book_id')
			->orderBy('total_order_count', 'DESC')
			->where('products_services_books.user_id',Auth::id());


			$top_sales_service_list = OrderItem::select('order_items.*',\DB::raw('COUNT(order_items.id) as total_order_count', 'order_items.products_services_book_id'),\DB::raw('sum(order_items.quantity) as total_sell_count','order_items.products_services_book_id'))
			->join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.product_type','service')
			->limit(5)
			->with('productsServicesBook.user:id,first_name,last_name,user_type_id','productsServicesBook.categoryMaster','productsServicesBook.subCategory')
			->with(['productsServicesBook.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['productsServicesBook.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
			->groupBy('order_items.products_services_book_id')
			->orderBy('total_order_count', 'DESC')
			->where('products_services_books.user_id',Auth::id());


			$top_sales_product_list = OrderItem::select('order_items.*',\DB::raw('COUNT(order_items.id) as total_order_count', 'order_items.products_services_book_id'),\DB::raw('sum(order_items.quantity) as total_sell_count','order_items.products_services_book_id'))
			->join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.product_type','product')
			->limit(5)
			->with('productsServicesBook.user:id,first_name,last_name,user_type_id','productsServicesBook.categoryMaster','productsServicesBook.subCategory')
			->with(['productsServicesBook.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['productsServicesBook.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
			->groupBy('order_items.products_services_book_id')
			->orderBy('total_order_count', 'DESC')
			->where('products_services_books.user_id',Auth::id());


			$top_sales_contest_list = OrderItem::select('order_items.*',\DB::raw('COUNT(contest_applications.contest_id) as total_order_count', 'order_items.contest_application_id'),\DB::raw('sum(order_items.quantity) as total_sell_count','order_items.contest_application_id'))
			->join('contest_applications', function ($join) {
				$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
			})
			->join('contests', function ($join) {
				$join->on('contest_applications.contest_id', '=', 'contests.id');
			})
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.contest_type','contest')
			->limit(5)
			->with('contestApplication.contest.user:id,first_name,last_name,user_type_id','contestApplication.contest.categoryMaster','contestApplication.contest.subCategory')
			->with(['contestApplication.contest.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['contestApplication.contest.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
			->groupBy('order_items.contest_application_id')
			->orderBy('total_order_count', 'DESC')
			->where('contests.user_id',Auth::id());


			$top_sales_event_list = OrderItem::select('order_items.*',\DB::raw('COUNT(contest_applications.contest_id) as total_order_count', 'order_items.contest_application_id'),\DB::raw('sum(order_items.quantity) as total_sell_count','order_items.contest_application_id'))
			->join('contest_applications', function ($join) {
				$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
			})
			->join('contests', function ($join) {
				$join->on('contest_applications.contest_id', '=', 'contests.id');
			})
			->join('orders', 'orders.id','=','order_items.order_id')
			->where('orders.payment_status', 'paid')
			->where('order_items.contest_type','event')
			->limit(5)
			->with('contestApplication.contest.user:id,first_name,last_name,user_type_id','contestApplication.contest.categoryMaster','contestApplication.contest.subCategory')
			->with(['contestApplication.contest.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['contestApplication.contest.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
			->groupBy('order_items.contest_application_id')
			->orderBy('total_order_count', 'DESC')
			->where('contests.user_id',Auth::id());


			$data['top_sales_book_list']     = $top_sales_book_list->get();
			$data['top_sales_service_list']  = $top_sales_service_list->get();
			$data['top_sales_product_list']  = $top_sales_product_list->get();
			$data['top_sales_contest_list']  = $top_sales_contest_list->get();
			$data['top_sales_event_list']    = $top_sales_event_list->get();
			return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		} 
	}


	public function saleAmount(Request $request)
	{
		try{
			$data = [];
			$data['total_earnings_of_today'] = OrderItem::select('order_items.id')
				->join('orders', 'orders.id','=','order_items.order_id')
				->where('orders.payment_status', 'paid')
				->whereDate('order_items.created_at',date('Y-m-d'))
				->where('order_items.vendor_user_id',Auth::id())
				->sum('order_items.amount_transferred_to_vendor');

			$data['total_earnings_of_week'] = OrderItem::select('order_items.id')
				->join('orders', 'orders.id','=','order_items.order_id')
				->where('orders.payment_status', 'paid')
				->whereDate('order_items.created_at','>=',date('Y-m-d',strtotime('-7days')))
				->where('order_items.vendor_user_id',Auth::id())
				->sum('order_items.amount_transferred_to_vendor');

			$data['total_earnings_of_month'] = OrderItem::select('order_items.id')
				->join('orders', 'orders.id','=','order_items.order_id')
				->where('orders.payment_status', 'paid')
				->whereDate('order_items.created_at','>=',date('Y-m-d',strtotime('-30days')))
				->where('order_items.vendor_user_id',Auth::id())
				->sum('order_items.amount_transferred_to_vendor');
				
			return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}


	public function jobsList(Request $request)
	{
		try
		{
			$lang_id = $this->lang_id;
			if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

			$recent_job_list = Job::orderBy('created_at','desc')
			->limit(10)
			->with('user:id,first_name,last_name,user_type_id','categoryMaster','subCategory')
			->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
			->where('user_id',Auth::id());


			$most_applied_job_list = Job::select('sp_jobs.*',\DB::raw('COUNT(job_applications.id) as total_job_application_count', 'job_applications.job_id'))
			->join('job_applications', function ($join) {
				$join->on('sp_jobs.id', '=', 'job_applications.job_id');
			})
			->limit(5)
			->groupBy('job_applications.job_id')
			->with('user:id,first_name,last_name,user_type_id','categoryMaster','subCategory')
			->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
			->orderBy('total_job_application_count', 'DESC')
			->where('sp_jobs.user_id',Auth::id());
			

			$data['recent_job_list']    	= $recent_job_list->get();
			$data['most_applied_job_list']  = $most_applied_job_list->get();
			return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		} 
	}
}
