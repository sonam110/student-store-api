<?php

namespace App\Http\Controllers\API\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Job;
use App\Models\Contest;
use App\Models\ProductsServicesBook;

class DashboardController extends Controller
{

	public function index(Request $request)
	{
		try
		{ 
			$data = [];
			if(!empty($request->user_id))
			{
				$data['total_students']         = 0;
				$data['total_companies']        = 0;
				$data['total_normal_users']     = 0;
				$data['total_products']         = ProductsServicesBook::select('id')->where('user_id',$request->user_id)->where('type','product')->count();
				$data['total_services']         = ProductsServicesBook::select('id')->where('user_id',$request->user_id)->where('type','service')->count();
				$data['total_books']            = ProductsServicesBook::select('id')->where('user_id',$request->user_id)->where('type','book')->count();
				$data['total_contests']         = Contest::select('id')->where('user_id',$request->user_id)->where('type','contest')->count();
				$data['total_events']           = Contest::select('id')->where('user_id',$request->user_id)->where('type','event')->count();
				$data['total_jobs']             = Job::select('id')->where('user_id',$request->user_id)->count();
                // $data['total_orders']        = Order::select('id')->count();


				$data['total_orders']           = OrderItem::select('order_items.id')
				->join('products_services_books',function ($join) {
					$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
				})
				->where('products_services_books.user_id',$request->user_id)
				->count()
				+ OrderItem::select('order_items.id')
				->join('contest_applications',function ($join) {
					$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
				})
				->join('contests',function ($join) {
					$join->on('contest_applications.contest_id', '=', 'contests.id');
				})
				->where('contests.user_id',$request->user_id)
				->count();
				$data['order_completed']        = OrderItem::select('order_items.id')
				->join('products_services_books',function ($join) {
					$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
				})
				->where('products_services_books.user_id',$request->user_id)
				->where('order_items.item_status','completed')
				->count()
				+ OrderItem::select('order_items.id')
				->join('contest_applications',function ($join) {
					$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
				})
				->join('contests',function ($join) {
					$join->on('contest_applications.contest_id', '=', 'contests.id');
				})
				->where('contests.user_id',$request->user_id)
				->where('order_items.item_status','completed')
				->count();

				$data['order_under_process']    = OrderItem::select('order_items.id')
				->join('products_services_books',function ($join) {
					$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
				})
				->where('products_services_books.user_id',$request->user_id)
				->where('order_items.item_status','processing')
				->count()
				+ OrderItem::select('order_items.id')
				->join('contest_applications',function ($join) {
					$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
				})
				->join('contests',function ($join) {
					$join->on('contest_applications.contest_id', '=', 'contests.id');
				})
				->where('contests.user_id',$request->user_id)
				->where('order_items.item_status','processing')
				->count();

				$data['order_delivered']        = OrderItem::select('order_items.id')
				->join('products_services_books',function ($join) {
					$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
				})
				->where('products_services_books.user_id',$request->user_id)
				->where('order_items.item_status','delivered')
				->count()
				+ OrderItem::select('order_items.id')
				->join('contest_applications',function ($join) {
					$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
				})
				->join('contests',function ($join) {
					$join->on('contest_applications.contest_id', '=', 'contests.id');
				})
				->where('contests.user_id',$request->user_id)
				->where('order_items.item_status','delivered')
				->count();

				$data['total_earnings'] = OrderItem::select('order_items.id',\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
				->join('products_services_books',function ($join) {
					$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
				})
				->where('products_services_books.user_id',$request->user_id)
				->get()[0]['total_amount'];
				+ OrderItem::select('order_items.id')
				->join('contest_applications',function ($join) {
					$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
				})
				->join('contests',function ($join) {
					$join->on('contest_applications.contest_id', '=', 'contests.id');
				})
				->where('contests.user_id',$request->user_id)
				->sum('order_items.amount_transferred_to_vendor');


				$data['total_amount_refunded']  = OrderItem::select('order_items.id')
				->join('products_services_books',function ($join) {
					$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
				})
				->where('products_services_books.user_id',$request->user_id)
				->sum('order_items.amount_returned')
				+ OrderItem::select('order_items.id')
				->join('contest_applications',function ($join) {
					$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
				})
				->join('contests',function ($join) {
					$join->on('contest_applications.contest_id', '=', 'contests.id');
				})
				->where('contests.user_id',$request->user_id)
				->sum('order_items.amount_returned');

				$data['total_returned_items']   = OrderItem::select('order_items.id')
				->join('products_services_books',function ($join) {
					$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
				})
				->where('products_services_books.user_id',$request->user_id)
				->where('order_items.item_status','returned')
				->count()
				+ OrderItem::select('order_items.id')
				->join('contest_applications',function ($join) {
					$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
				})
				->join('contests',function ($join) {
					$join->on('contest_applications.contest_id', '=', 'contests.id');
				})
				->where('contests.user_id',$request->user_id)
				->where('order_items.item_status','returned')
				->count();

				$data['student_store_commission'] = OrderItem::join('products_services_books',function ($join) {
					$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
				})
				->where('products_services_books.user_id',$request->user_id)
				->sum('student_store_commission');

				$data['cool_company_commission'] = OrderItem::join('products_services_books',function ($join) {
					$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
				})
				->where('products_services_books.user_id',$request->user_id)
				->sum('cool_company_commission');

				$data['amount_transferred_to_vendor'] = OrderItem::join('products_services_books',function ($join) {
					$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
				})
				->where('products_services_books.user_id',$request->user_id)
				->where('order_items.is_transferred_to_vendor', '1')
				->sum('amount_transferred_to_vendor');

				$data['pending_amount_transferred_to_vendor'] = OrderItem::join('products_services_books',function ($join) {
					$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
				})
				->where('products_services_books.user_id',$request->user_id)
				->where('order_items.is_transferred_to_vendor', '0')
				->sum('amount_transferred_to_vendor');
			}
			else
			{
				$data['total_students']         = User::select('id')->where('user_type_id',2)->count();
				$data['total_companies']        = User::select('id')->where('user_type_id',3)->count();
				$data['total_normal_users']     = User::select('id')->where('user_type_id',4)->count();
				$data['total_products']         = ProductsServicesBook::select('id')->where('type','product')->count();
				$data['total_services']         = ProductsServicesBook::select('id')->where('type','service')->count();
				$data['total_books']            = ProductsServicesBook::select('id')->where('type','book')->count();
				$data['total_contests']         = Contest::select('id')->where('type','contest')->count();
				$data['total_events']           = Contest::select('id')->where('type','event')->count();
				$data['total_jobs']             = Job::select('id')->count();
                // $data['total_orders']        = Order::select('id')->count();
				$data['total_orders']           = OrderItem::select('order_items.id')->count();
				$data['order_completed']        = OrderItem::select('order_items.id')->where('order_items.item_status','completed')
				->count();

				$data['order_under_process']    = OrderItem::select('order_items.id')->where('order_items.item_status','processing')
				->count();

				$data['order_delivered']        = OrderItem::select('order_items.id')->where('order_items.item_status','delivered')
				->count();

				$data['total_earnings']         = OrderItem::select('order_items.id',\DB::raw('sum(order_items.price * order_items.quantity) as total_amount'))->get()[0]['total_amount'];

				$data['total_amount_refunded']  = OrderItem::select('order_items.id')->sum('amount_returned');

				$data['total_returned_items']   = OrderItem::select('order_items.id')->where('order_items.item_status','returned')
				->count();

				$data['student_store_commission']  = OrderItem::sum('student_store_commission');

				$data['cool_company_commission']= OrderItem::sum('cool_company_commission');

				$data['amount_transferred_to_vendor']  = OrderItem::where('is_transferred_to_vendor', '1')->sum('amount_transferred_to_vendor');

				$data['pending_amount_transferred_to_vendor']  = OrderItem::where('is_transferred_to_vendor', '0')->sum('amount_transferred_to_vendor');
			}

			return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
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
				if(!empty($request->user_id))
				{
					$total_sales_book = OrderItem::select( 
						\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
					->join('products_services_books', function ($join) {
						$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
					})
					->where('order_items.product_type','book')
					->where('order_items.created_at','like','%'.$date.'%')
					->where('products_services_books.user_id',$request->user_id);

					$total_sales_service = OrderItem::select( 
						\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
					->join('products_services_books', function ($join) {
						$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
					})
					->where('order_items.product_type','service')
					->where('order_items.created_at','like','%'.$date.'%')
					->where('products_services_books.user_id',$request->user_id);

					$total_sales_product = OrderItem::select( 
						\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
					->join('products_services_books', function ($join) {
						$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
					})
					->where('order_items.product_type','product')
					->where('order_items.created_at','like','%'.$date.'%')
					->where('products_services_books.user_id',$request->user_id);

					$contestOrders = OrderItem::select( 
						\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
					->join('contest_applications', function ($join) {
						$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
					})
					->join('contests', function ($join) {
						$join->on('contest_applications.contest_id', '=', 'contests.id');
					})
					->where('order_items.contest_type','contest')
					->whereDate('order_items.created_at','like','%'.$date.'%')
					->where('contests.user_id',$request->user_id);


					$eventOrders = OrderItem::select( 
						\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
					->join('contest_applications', function ($join) {
						$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
					})
					->join('contests', function ($join) {
						$join->on('contest_applications.contest_id', '=', 'contests.id');
					})
					->where('order_items.contest_type','event')
					->where('order_items.created_at','like','%'.$date.'%')
					->where('contests.user_id',$request->user_id);
				}
				else
				{
					$total_sales_book = OrderItem::select( 
						\DB::raw('sum(order_items.price * order_items.quantity) as total_amount'))
					->join('products_services_books', function ($join) {
						$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
					})
					->where('order_items.product_type','book')
					->where('order_items.created_at','like','%'.$date.'%');

					$total_sales_service = OrderItem::select( 
						\DB::raw('sum(order_items.price * order_items.quantity) as total_amount'))
					->join('products_services_books', function ($join) {
						$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
					})
					->where('order_items.product_type','service')
					->where('order_items.created_at','like','%'.$date.'%');

					$total_sales_product = OrderItem::select( 
						\DB::raw('sum(order_items.price * order_items.quantity) as total_amount'))
					->join('products_services_books', function ($join) {
						$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
					})
					->where('order_items.product_type','product')
					->where('order_items.created_at','like','%'.$date.'%');

					$contestOrders = OrderItem::select( 
						\DB::raw('sum(order_items.price * order_items.quantity) as total_amount'))
					->join('contest_applications', function ($join) {
						$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
					})
					->join('contests', function ($join) {
						$join->on('contest_applications.contest_id', '=', 'contests.id');
					})
					->where('order_items.contest_type','contest')
					->whereDate('order_items.created_at','like','%'.$date.'%');

					$eventOrders = OrderItem::select( 
						\DB::raw('sum(order_items.price * order_items.quantity) as total_amount'))
					->join('contest_applications', function ($join) {
						$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
					})
					->join('contests', function ($join) {
						$join->on('contest_applications.contest_id', '=', 'contests.id');
					})
					->where('order_items.contest_type','event')
					->where('order_items.created_at','like','%'.$date.'%');
				}


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
			}
			return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function recentOrderList(Request $request)
	{
		try
		{
			$total_sales_book_list = OrderItem::join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->where('order_items.product_type','book')
			->limit(10)
			->with('productsServicesBook.user:id,first_name,last_name,user_type_id','productsServicesBook.categoryMaster','productsServicesBook.subCategory','order.user:id,first_name,last_name,user_type_id')
			->orderBy('order_items.created_at','desc');
			if(!empty($request->user_id))
			{
				$total_sales_book_list = $total_sales_book_list->where('products_services_books.user_id',$request->user_id);
			}

			$total_sales_service_list = OrderItem::join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->where('order_items.product_type','service')
			->limit(10)
			->with('productsServicesBook.user:id,first_name,last_name,user_type_id','productsServicesBook.categoryMaster','productsServicesBook.subCategory','order.user:id,first_name,last_name,user_type_id')
			->orderBy('order_items.created_at','desc');
			if(!empty($request->user_id))
			{
				$total_sales_service_list = $total_sales_service_list->where('products_services_books.user_id',$request->user_id);
			}

			$total_sales_product_list = OrderItem::join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->where('order_items.product_type','product')
			->limit(10)
			->with('productsServicesBook.user:id,first_name,last_name,user_type_id','productsServicesBook.categoryMaster','productsServicesBook.subCategory','order.user:id,first_name,last_name,user_type_id')
			->orderBy('order_items.created_at','desc');
			if(!empty($request->user_id))
			{
				$total_sales_product_list = $total_sales_product_list->where('products_services_books.user_id',$request->user_id);
			}

			$total_sales_contest_list = OrderItem::join('contest_applications', function ($join) {
				$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
			})
			->join('contests', function ($join) {
				$join->on('contest_applications.contest_id', '=', 'contests.id');
			})
			->where('order_items.contest_type','contest')
			->limit(10)
			->with('contestApplication.contest.user:id,first_name,last_name,user_type_id','contestApplication.contest.categoryMaster','contestApplication.contest.subCategory','order.user:id,first_name,last_name,user_type_id')
			->orderBy('order_items.created_at','desc');
			if(!empty($request->user_id))
			{
				$total_sales_contest_list = $total_sales_contest_list->where('contests.user_id',$request->user_id);
			}

			$total_sales_event_list = OrderItem::join('contest_applications', function ($join) {
				$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
			})
			->join('contests', function ($join) {
				$join->on('contest_applications.contest_id', '=', 'contests.id');
			})
			->where('order_items.contest_type','event')
			->limit(10)
			->with('contestApplication.contest.user:id,first_name,last_name,user_type_id','contestApplication.contest.categoryMaster','contestApplication.contest.subCategory','order.user:id,first_name,last_name,user_type_id')
			->orderBy('order_items.created_at','desc');
			if(!empty($request->user_id))
			{
				$total_sales_event_list = $total_sales_event_list->where('contests.user_id',$request->user_id);
			}
			$data['total_sales_book_list']     = $total_sales_book_list->get();
			$data['total_sales_service_list']  = $total_sales_service_list->get();
			$data['total_sales_product_list']  = $total_sales_product_list->get();
			$data['total_sales_contest_list']  = $total_sales_contest_list->get();
			$data['total_sales_event_list']    = $total_sales_event_list->get();
			return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		} 
	}

	public function topSellingList(Request $request)
	{
		try
		{
			$top_sales_book_list = OrderItem::select('order_items.*',\DB::raw('COUNT(order_items.id) as total_order_count', 'order_items.products_services_book_id'),\DB::raw('sum(order_items.quantity) as total_sell_count','order_items.products_services_book_id'))
			->join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->where('order_items.product_type','book')
			->limit(5)
			->with('productsServicesBook.user:id,first_name,last_name,user_type_id','productsServicesBook.categoryMaster','productsServicesBook.subCategory')
			->groupBy('order_items.products_services_book_id')
			->orderBy('total_order_count', 'DESC');
			if(!empty($request->user_id))
			{
				$top_sales_book_list = $top_sales_book_list->where('products_services_books.user_id',$request->user_id);
			}

			$top_sales_service_list = OrderItem::select('order_items.*',\DB::raw('COUNT(order_items.id) as total_order_count', 'order_items.products_services_book_id'),\DB::raw('sum(order_items.quantity) as total_sell_count','order_items.products_services_book_id'))
			->join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->where('order_items.product_type','service')
			->limit(5)
			->with('productsServicesBook.user:id,first_name,last_name,user_type_id','productsServicesBook.categoryMaster','productsServicesBook.subCategory')
			->groupBy('order_items.products_services_book_id')
			->orderBy('total_order_count', 'DESC');
			if(!empty($request->user_id))
			{
				$top_sales_service_list = $top_sales_service_list->where('products_services_books.user_id',$request->user_id);
			}

			$top_sales_product_list = OrderItem::select('order_items.*',\DB::raw('COUNT(order_items.id) as total_order_count', 'order_items.products_services_book_id'),\DB::raw('sum(order_items.quantity) as total_sell_count','order_items.products_services_book_id'))
			->join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->where('order_items.product_type','product')
			->limit(5)
			->with('productsServicesBook.user:id,first_name,last_name,user_type_id','productsServicesBook.categoryMaster','productsServicesBook.subCategory')
			->groupBy('order_items.products_services_book_id')
			->orderBy('total_order_count', 'DESC');
			if(!empty($request->user_id))
			{
				$top_sales_product_list = $top_sales_product_list->where('products_services_books.user_id',$request->user_id);
			}

			$top_sales_contest_list = OrderItem::select('order_items.*',\DB::raw('COUNT(contest_applications.contest_id) as total_order_count', 'order_items.contest_application_id'),\DB::raw('sum(order_items.quantity) as total_sell_count','order_items.contest_application_id'))
			->join('contest_applications', function ($join) {
				$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
			})
			->join('contests', function ($join) {
				$join->on('contest_applications.contest_id', '=', 'contests.id');
			})
			->where('order_items.contest_type','contest')
			->limit(5)
			->with('contestApplication.contest.user:id,first_name,last_name,user_type_id','contestApplication.contest.categoryMaster','contestApplication.contest.subCategory')
			->groupBy('order_items.contest_application_id')
			->orderBy('total_order_count', 'DESC');
			if(!empty($request->user_id))
			{
				$top_sales_contest_list = $top_sales_contest_list->where('contests.user_id',$request->user_id);
			}

			$top_sales_event_list = OrderItem::select('order_items.*',\DB::raw('COUNT(contest_applications.contest_id) as total_order_count', 'order_items.contest_application_id'),\DB::raw('sum(order_items.quantity) as total_sell_count','order_items.contest_application_id'))
			->join('contest_applications', function ($join) {
				$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
			})
			->join('contests', function ($join) {
				$join->on('contest_applications.contest_id', '=', 'contests.id');
			})
			->where('order_items.contest_type','event')
			->limit(5)
			->with('contestApplication.contest.user:id,first_name,last_name,user_type_id','contestApplication.contest.categoryMaster','contestApplication.contest.subCategory')
			->groupBy('order_items.contest_application_id')
			->orderBy('total_order_count', 'DESC');
			if(!empty($request->user_id))
			{
				$top_sales_event_list = $top_sales_event_list->where('contests.user_id',$request->user_id);
			}
			$data['top_sales_book_list']     = $top_sales_book_list->get();
			$data['top_sales_service_list']  = $top_sales_service_list->get();
			$data['top_sales_product_list']  = $top_sales_product_list->get();
			$data['top_sales_contest_list']  = $top_sales_contest_list->get();
			$data['top_sales_event_list']    = $top_sales_event_list->get();
			return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		} 
	}


	public function saleAmount(Request $request)
	{
		try{
			$data = [];
			if(!empty($request->user_id))
			{
				$data['total_earnings_of_today'] = OrderItem::select('order_items.id',\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
				->join('products_services_books',function ($join) {
					$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
				})
				->where('products_services_books.user_id',$request->user_id)
				->whereDate('order_items.created_at',date('Y-m-d'))
				->get()[0]['total_amount'];
				+ OrderItem::select('order_items.id',\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
				->join('contest_applications',function ($join) {
					$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
				})
				->join('contests',function ($join) {
					$join->on('contest_applications.contest_id', '=', 'contests.id');
				})
				->whereDate('order_items.created_at',date('Y-m-d'))
				->where('contests.user_id',$request->user_id)
				->get()[0]['total_amount'];

				$data['total_earnings_of_week'] = OrderItem::select('order_items.id',\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
				->join('products_services_books',function ($join) {
					$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
				})
				->where('products_services_books.user_id',$request->user_id)
				->whereDate('order_items.created_at','>=',date('Y-m-d',strtotime('-7days')))
				->get()[0]['total_amount'];
				+ OrderItem::select('order_items.id',\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
				->join('contest_applications',function ($join) {
					$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
				})
				->join('contests',function ($join) {
					$join->on('contest_applications.contest_id', '=', 'contests.id');
				})
				->whereDate('order_items.created_at','>=',date('Y-m-d',strtotime('-7days')))
				->where('contests.user_id',$request->user_id)
				->get()[0]['total_amount'];

				$data['total_earnings_of_month'] = OrderItem::select('order_items.id',\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
				->join('products_services_books',function ($join) {
					$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
				})
				->where('products_services_books.user_id',$request->user_id)
				->whereDate('order_items.created_at','>=',date('Y-m-d',strtotime('-30days')))
				->get()[0]['total_amount'];
				+ OrderItem::select('order_items.id',\DB::raw('sum(order_items.amount_transferred_to_vendor) as total_amount'))
				->join('contest_applications',function ($join) {
					$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
				})
				->join('contests',function ($join) {
					$join->on('contest_applications.contest_id', '=', 'contests.id');
				})
				->whereDate('order_items.created_at','>=',date('Y-m-d',strtotime('-30days')))
				->where('contests.user_id',$request->user_id)
				->get()[0]['total_amount'];
			}
			else
			{
				$data['total_earnings_of_today'] = OrderItem::select('order_items.id',\DB::raw('sum(order_items.price * order_items.quantity) as total_amount'))->whereDate('order_items.created_at',date('Y-m-d'))->get()[0]['total_amount'];

				$data['total_earnings_of_week'] = OrderItem::select('order_items.id',\DB::raw('sum(order_items.price * order_items.quantity) as total_amount'))->whereDate('order_items.created_at','>=',date('Y-m-d',strtotime('-7days')))->get()[0]['total_amount'];
				$data['total_earnings_of_month'] = OrderItem::select('order_items.id',\DB::raw('sum(order_items.price * order_items.quantity) as total_amount'))->whereDate('order_items.created_at','>=',date('Y-m-d',strtotime('-30days')))->get()[0]['total_amount'];
			}
			return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}


	public function jobsList(Request $request)
	{
		try
		{
			
			$recent_job_list = Job::orderBy('created_at','desc')
			->limit(10)
			->with('user:id,first_name,last_name,user_type_id','categoryMaster','subCategory');
			if(!empty($request->user_id))
			{
				$recent_job_list = $recent_job_list->where('user_id',$request->user_id);
			}

			$most_applied_job_list = Job::select('sp_jobs.*',\DB::raw('COUNT(job_applications.id) as total_job_application_count', 'job_applications.job_id'))
			->join('job_applications', function ($join) {
				$join->on('sp_jobs.id', '=', 'job_applications.job_id');
			})
			->limit(5)
			->groupBy('job_applications.job_id')
			->with('user:id,first_name,last_name,user_type_id','categoryMaster','subCategory')
			->orderBy('total_job_application_count', 'DESC');
			if(!empty($request->user_id))
			{
				$most_applied_job_list = $most_applied_job_list->where('sp_jobs.user_id',$request->user_id);
			}

			$data['recent_job_list']    	= $recent_job_list->get();
			$data['most_applied_job_list']  = $most_applied_job_list->get();
			return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		} 
	}
}
