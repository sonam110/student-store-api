<?php

namespace App\Http\Controllers\API;

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
use App\Models\User;
use App\Models\EmailTemplate;
use App\Mail\OrderMail;
use App\Mail\OrderStatusMail;
use App\Mail\OrderPlacedMail;
use App\Mail\OrderConfirmedMail;
use Mail;
use App\Models\ReasonForAction;
use App\Models\Contest;
use App\Models\ContestApplication;
use Session;
use App\Models\Package;
use App\Models\ShippingCondition;
use mervick\aesEverywhere\AES256;
use PDF;
use App\Models\UserPackageSubscription;
use Stripe;
use App\Models\PaymentGatewaySetting;

class OrderController extends Controller
{
	function __construct()
    {
        $this->paymentInfo = PaymentGatewaySetting::first();
    }

	public function index(Request $request)
	{
		try
		{
			$orders = Order::orderBy('created_at','DESC')->with('orderItems.productsServicesBook.user','orderItems.productsServicesBook.addressDetail','orderItems.productsServicesBook.categoryMaster','orderItems.orderTrackings','orderItems.return','orderItems.replacement','orderItems.dispute','orderItems.ratingAndFeedback');
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
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function allOrdersByUser(Request $request)
	{
		try
		{
			if(!empty($request->per_page_record))
			{
				$orders = Order::where('user_id', Auth::id())->orderBy('created_at','DESC')->with('orderItems.productsServicesBook.user.serviceProviderDetail','orderItems.productsServicesBook.user.defaultAddress','orderItems.productsServicesBook.addressDetail','orderItems.productsServicesBook.categoryMaster','orderItems.orderTrackings','orderItems.return','orderItems.replacement','orderItems.dispute','orderItems.ratingAndFeedback','transaction','orderItems.contestApplication.contest.user:id,first_name,last_name','orderItems.contestApplication.contest.cancellationRanges','orderItems.contestApplication.contest.contestWinners','orderItems.contestApplication.contest.ratingAndFeedback')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$orders = Order::where('user_id', Auth::id())->orderBy('created_at','DESC')->with('orderItems.productsServicesBook.user.serviceProviderDetail','orderItems.productsServicesBook.user.defaultAddress','orderItems.productsServicesBook.addressDetail','orderItems.productsServicesBook.categoryMaster','orderItems.orderTrackings','orderItems.return','orderItems.replacement','orderItems.dispute','orderItems.ratingAndFeedback','transaction','orderItems.contestApplication.contest.user:id,first_name,last_name','orderItems.contestApplication.contest.cancellationRanges','orderItems.contestApplication.contest.contestWinners','orderItems.contestApplication.contest.ratingAndFeedback')->get();
			}
			return response(prepareResult(false, $orders, getLangByLabelGroups('messages','messages_order_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function allOrdersForUser(Request $request)
	{
		try
		{
			$orderItems = OrderItem::select('order_items.*')
			->join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->orderBy('order_items.created_at','DESC')
			->where('products_services_books.user_id',Auth::id())
			->with('productsServicesBook.user.serviceProviderDetail','productsServicesBook.addressDetail','productsServicesBook.categoryMaster','user','orderTrackings','return','replacement','dispute','ratingAndFeedback','order:id,order_number,first_name,last_name,email,contact_number,latitude,longitude,country,state,city,full_address,zip_code','order.addressDetail');
			if(!empty($request->per_page_record))
			{
				$orders = $orderItems->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$orders = $orderItems->get();
			}
			return response(prepareResult(false, $orders, getLangByLabelGroups('messages','messages_order_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function store(Request $request)
	{
		$validation = Validator::make($request->all(), [
			'address_detail_id'     => 'required',
            // 'grand_total'           => 'required'
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}
		DB::beginTransaction();
		try
		{
			$delivery_code = NULL;
			$shipping_charge = 0;

			$getLastOrder = Order::select('order_number')->orderBy('created_at','DESC')->first();
			if(!empty($getLastOrder))
			{
				$order_number = $getLastOrder->order_number + 1;

			}
			else
			{
				$order_number = env('ORDER_START_NUMBER');
			}

			$addressfind = AddressDetail::find($request->address_detail_id);

			$order                      = new Order;
			$order->order_number        = $order_number;
			$order->user_id        		= Auth::id();
			$order->address_detail_id   = $request->address_detail_id;
			$order->order_status        = $request->order_status;
			$order->sub_total           = $request->sub_total;
			$order->item_discount       = $request->item_discount;
			$order->shipping_charge     = $shipping_charge;
			$order->total               = $request->total;
			$order->promo_code          = $request->promo_code;
			$order->promo_code_discount = $request->promo_code_discount;
			$order->grand_total         = $request->grand_total;
			$order->remark              = $request->remark;
			$order->first_name          = (!empty(Auth::user()->first_name)) ? AES256::decrypt(Auth::user()->first_name, env('ENCRYPTION_KEY')) : NULL;
			$order->last_name           = (!empty(Auth::user()->last_name)) ? AES256::decrypt(Auth::user()->last_name, env('ENCRYPTION_KEY')) : NULL;
			$order->email               = (!empty(Auth::user()->email)) ? AES256::decrypt(Auth::user()->email, env('ENCRYPTION_KEY')) : NULL;
			$order->contact_number      = (!empty(Auth::user()->contact_number)) ? AES256::decrypt(Auth::user()->contact_number, env('ENCRYPTION_KEY')) : NULL;
			$order->latitude            = $addressfind->latitude;
			$order->longitude           = $addressfind->longitude;
			$order->country             = $addressfind->country;
			$order->state               = $addressfind->state;
			$order->city                = $addressfind->city;
			$order->full_address        = $addressfind->full_address;
			$order->zip_code        	= $addressfind->zip_code;
			$order->used_reward_points 	= $request->used_reward_points;
			$order->order_for 			= $request->order_for;
			$order->reward_point_status = 'used';


			if($order->save())
			{ 
				Auth::user()->update(['reward_points'=>(Auth::user()->reward_points - $request->used_reward_points)]);
				$sub_total = 0;
				foreach ($request->items as $key => $orderedItem) {
					if(!empty($orderedItem['product_id']))
					{
						$productsServicesBook = ProductsServicesBook::find($orderedItem['product_id']);
						$vat_percent = $productsServicesBook->categoryMaster->title;
						if($productsServicesBook->is_on_offer == 1)
						{
							$price = $productsServicesBook->discounted_price;

							if($productsServicesBook->discount_type == 1)
							{
								$discount_amount = $productsServicesBook->basic_price_wo_vat * $productsServicesBook->discount_value / 100;
							}
							else
							{
								$discount_amount = $productsServicesBook->discount_value;
							} 

							$vendor_price = $productsServicesBook->basic_price_wo_vat - $discount_amount;
						}
						else
						{
							$price = $productsServicesBook->price;
							$vendor_price = $productsServicesBook->basic_price_wo_vat;
						}
						$title = $productsServicesBook->title;

						if($productsServicesBook->delivery_type == 'deliver_to_location')
						{
							$shipping_package = ShippingCondition::where('user_id',$productsServicesBook->user_id)->where('order_amount_from','<=', $price*$orderedItem['quantity'])->where('order_amount_to','>=',$price*$orderedItem['quantity'])->orderBy('created_at','desc')->first();
							if($shipping_package)
							{
								$shipping_charge = $shipping_charge + ($productsServicesBook->shipping_charge * $orderedItem['quantity']) * (100 - $shipping_package->discount_percent) / 100;
							}
							else
							{
								$shipping_charge = $shipping_charge + ($productsServicesBook->shipping_charge * $orderedItem['quantity']);
							}
						}

						
						if($productsServicesBook->delivery_type == 'pickup_from_location')
						{
							$delivery_code = rand(100000, 999999);
						}

						if(($orderedItem['quantity'] > $productsServicesBook->quantity) && ($productsServicesBook->type != 'service'))
						{
							return response()->json(prepareResult(true, ['quantity exceeded.Only '.$productsServicesBook->quantity.' left.'], getLangByLabelGroups('messages','message_quantity_exceeded')), config('http_response.bad_request'));
						}

						$user_package = UserPackageSubscription::where('user_id',$productsServicesBook->user_id)->where('module',$productsServicesBook->type)->orderBy('created_at','desc')->first();
						
					}
					elseif(!empty($orderedItem['contest_application_id']))
					{
						$contest_id = ContestApplication::find($orderedItem['contest_application_id'])->contest_id;
						$productsServicesBook = Contest::find($contest_id);
						$vat_percent = $productsServicesBook->categoryMaster->title;
						if($productsServicesBook->is_on_offer == 1)
						{
							$price = $productsServicesBook->discounted_price;
							if($productsServicesBook->discount_type == 1)
							{
								$discount_amount = $productsServicesBook->basic_price_wo_vat * $productsServicesBook->discount_value / 100;
							}
							else
							{
								$discount_amount = $productsServicesBook->discount_value;
							} 
							
							$vendor_price = $productsServicesBook->basic_price_wo_vat - $discount_amount;
						}
						else
						{
							$price = $productsServicesBook->subscription_fees;
							$vendor_price = $productsServicesBook->basic_price_wo_vat;
						}
						$title = $productsServicesBook->title;

						$user_package = UserPackageSubscription::where('user_id',$productsServicesBook->user_id)->where('module','contest')->orderBy('created_at','desc')->first();
					}
					else
					{
						$productsServicesBook = Package::find($orderedItem['package_id']);
						$vat_percent = '0';
						if($productsServicesBook->price == 0)
						{
							$price = $productsServicesBook->subscription;
							$vendor_price = $price;
						}
						else
						{
							$price = $productsServicesBook->price;
							$vendor_price = $price;
						}
						$title = $productsServicesBook->type_of_package;
						$user_package = UserPackageSubscription::where('user_id',null)->first();
					}

					//reward point will be applicable to student only
					

					if(($productsServicesBook->is_reward_point_applicable == 1) && (Auth::user()->user_type_id == 2))
					{
						$earned_reward_points = $productsServicesBook->reward_points * $orderedItem['quantity'];
					}
					else
					{
						$earned_reward_points = '0';
					}

					if($user_package)
					{
						$commission = $user_package->commission_per_sale;
					}
					else
					{
						$commission = 0;
					}

					$reward_points_value = AppSetting::first()->single_rewards_pt_value * $earned_reward_points;

					$amount_transferred_to_vendor = (($vendor_price * $orderedItem['quantity']) - $reward_points_value) * (100 - $commission) / 100;
					$student_store_commission = (($vendor_price * $orderedItem['quantity']) - $reward_points_value) * ($commission) / 100;

					$cool_company_commission = 0;
					$coolCompanyCommission = 0;

					//cool company commission for student
					if(($productsServicesBook->user) && ($productsServicesBook->user->user_type_id == 2))
					{
						$coolCompanyCommission = AppSetting::first()->coolCompanyCommission;
						$cool_company_commission = $amount_transferred_to_vendor * ($coolCompanyCommission)/100;
						$amount_transferred_to_vendor = $amount_transferred_to_vendor * (100 - $coolCompanyCommission)/100;
						

					}

					if($productsServicesBook->discount_type == 1)
					{
						$discount = $productsServicesBook->discount_value.'%';
					}
					else
					{
						$discount = $productsServicesBook->discount_value.'Rupees';
					} 

					$sub_total = $sub_total + ($price * $orderedItem['quantity']);

					$orderItem = new OrderItem;
					$orderItem->user_id							= Auth::id();
					$orderItem->order_id						= $order->id;
					if(!empty($orderedItem['product_id']))
					{
						$orderItem->products_services_book_id		= $orderedItem['product_id'];
						$orderItem->product_type					= $productsServicesBook->type;
						$orderItem->note_to_seller                   = $orderedItem['note_to_seller'];

						ProductsServicesBook::where('id',$orderedItem['product_id'])->update(['quantity' => $productsServicesBook->quantity - $orderedItem['quantity']]);
					}
					elseif(!empty($orderedItem['contest_application_id']))
					{
						$orderItem->contest_application_id			= $orderedItem['contest_application_id'];
						$orderItem->contest_type					= $productsServicesBook->type;
					}
					else
					{
						$orderItem->package_id						= $orderedItem['package_id'];
					}
					
                    
					$orderItem->title							= $title;
					$orderItem->sku							    = $productsServicesBook->sku;
					$orderItem->price                           = $price;
					$orderItem->earned_reward_points            = $earned_reward_points;
					$orderItem->quantity						= $orderedItem['quantity'];
					$orderItem->discount						= $discount;
					$orderItem->cover_image                     = $orderedItem['cover_image'];
					$orderItem->sell_type						= $productsServicesBook->sell_type;
					$orderItem->rent_duration					= $productsServicesBook->rent_duration;
					$orderItem->item_status						= $request->order_status;
					$orderItem->item_payment_status				= true;
					$orderItem->amount_transferred_to_vendor	= $amount_transferred_to_vendor;
					$orderItem->student_store_commission		= $student_store_commission;
					$orderItem->cool_company_commission			= $cool_company_commission;
					$orderItem->student_store_commission_percent= $commission;
					$orderItem->cool_company_commission_percent	= $coolCompanyCommission;
					$orderItem->vat_percent						= $vat_percent;
					$orderItem->delivery_code					= $delivery_code;
					$orderItem->save();


					if(!empty($orderedItem['product_id']))
					{

						$orderTracking                  = new OrderTracking;
						$orderTracking->order_item_id   = $orderItem->id;
						$orderTracking->status          = $request->order_status;
						$orderTracking->comment         = '';
						$orderTracking->type         	= 'delivery';
						$orderTracking->save();


	                    // Notification Start

						$title = 'New Order Received';
						$body =  'New Order Received for product '.$productsServicesBook->title;
						$user = $productsServicesBook->user;
						$type = 'Order Received';
						$user_type = 'seller';
						if($productsServicesBook->type == 'book')
						{
							$module = 'book';
						}
						else
						{
							$module = 'product_service';
						}
						pushNotification($title,$body,$user,$type,true,$user_type,$module,$order->id,'market-place-request');

	                    // Notification End

	                    //Mail Start

	                    ///to Seller

	                    $seller_details = [
	                    	'title' => $title,
	                    	'body' => $body
	                    ];
	                    
	                    Mail::to(AES256::decrypt($orderItem->productsServicesBook->user->email, env('ENCRYPTION_KEY')))->send(new OrderMail($seller_details));

	                    //Mail End
	                }
				}

				//Mail-start-buyer

                $emailTemplate = EmailTemplate::where('template_for','order_placed')->where('language_id',$order->user->language_id)->first();
                if(empty($emailTemplate))
                {
                	$emailTemplate = EmailTemplate::where('template_for','order_placed')->first();
                }

                $email_body = $emailTemplate->body;

                $arrayVal = [
                	'{{user_name}}' => AES256::decrypt($order->user->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($order->user->last_name, env('ENCRYPTION_KEY')),
                	'{{order_number}}' => $order->order_number,
                ];
                $email_body = strReplaceAssoc($arrayVal, $email_body);
                
                $details = [
                	'title' => $emailTemplate->subject,
                	'body' => $email_body,
                	// 'order_details' => Order::with('orderItems')->find($order->id),
                	'order_details' => $order,
                ];
                
                Mail::to(AES256::decrypt($order->email, env('ENCRYPTION_KEY')))->send(new OrderPlacedMail($details));
                //mail-end


				$paymentCardDetail = PaymentCardDetail::find($request->transaction_detail['payment_card_detail_id']);
				$transactionDetail = new TransactionDetail;
				$transactionDetail->order_id                 	= $order->id;
				$transactionDetail->payment_card_detail_id   	= $request->transaction_detail['payment_card_detail_id'];
                $transactionDetail->transaction_id           	= $request->transaction_detail['transaction_id'];

                $transactionDetail->description              	= $request->transaction_detail['description'];
                $transactionDetail->receipt_email            	= $request->transaction_detail['receipt_email'];
                $transactionDetail->receipt_number           	= $request->transaction_detail['receipt_number'];
                $transactionDetail->receipt_url              	= $request->transaction_detail['receipt_url'];
                $transactionDetail->refund_url               	= $request->transaction_detail['refund_url'];

                $transactionDetail->transaction_status       	= $request->transaction_detail['transaction_status'];
                $transactionDetail->transaction_reference_no 	= $request->transaction_detail['transaction_reference_no'];
                $transactionDetail->transaction_amount       	= $request->transaction_detail['transaction_amount'];
                $transactionDetail->transaction_type         	= $request->transaction_detail['transaction_type'];
                $transactionDetail->transaction_mode         	= $request->transaction_detail['transaction_mode'];
                $transactionDetail->gateway_detail           	= $request->transaction_detail['gateway_detail'];

                $transactionDetail->transaction_timestamp    	= $request->transaction_detail['transaction_timestamp'];
                $transactionDetail->currency       		     	= $request->transaction_detail['currency'];
                
				$transactionDetail->card_number              	= $paymentCardDetail->card_number;
				$transactionDetail->card_type                	= $paymentCardDetail->card_type;
				$transactionDetail->card_cvv                 	= $paymentCardDetail->card_cvv;
				$transactionDetail->card_expiry              	= $paymentCardDetail->card_expiry;
				$transactionDetail->card_holder_name         	= $paymentCardDetail->card_holder_name;
				$transactionDetail->save();
			}



			$reward_point_value = AppSetting::first()->customer_rewards_pt_value * $request->used_reward_points;

			$total = $sub_total - $reward_point_value;


			$vat = (AppSetting::first()->vat) * $total / 100;

			// $total = $total + $vat + $shipping_charge;

			$total = $total + $shipping_charge;

			$order->update([
				'sub_total' => $sub_total,
				'total'  => $total,
				'shipping_charge'  => $shipping_charge,
				'vat' => $vat,
				'grand_total' => $total -  $request->promo_code_discount,
				'payable_amount' => $total -  $request->promo_code_discount,
			]);

			DB::commit();
			$order = Order::with('orderItems.productsServicesBook.user','orderItems.productsServicesBook.addressDetail','orderItems.productsServicesBook.categoryMaster','orderItems.orderTrackings','orderItems.return','orderItems.replacement','orderItems.dispute','orderItems.ratingAndFeedback')->find($order->id);
			return response()->json(prepareResult(false, $order, getLangByLabelGroups('messages','messages_order_created')), config('http_response.created'));
		}
		catch (\Throwable $exception)
		{
			DB::rollback();
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
		}
	}

	public function show(Order $order)
	{
		$order = Order::with('orderItems.productsServicesBook.user','orderItems.productsServicesBook.addressDetail','orderItems.productsServicesBook.categoryMaster','orderItems.orderTrackings','orderItems.return','orderItems.replacement','orderItems.dispute','orderItems.ratingAndFeedback')->find($order->id);
		return response()->json(prepareResult(false, $order, getLangByLabelGroups('messages','messages_order_list')), config('http_response.success'));
	} 

	public function destroy(Order $order)
	{
		$order->delete();
		return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_order_deleted')), config('http_response.success'));
	}

	public function orderStatusUpdate(Request $request, $id)
	{

		$order = Order::find($id);
		$order->update(['order_status'=> $request->order_status]);
		return response()->json(prepareResult(false, $order, getLangByLabelGroups('messages','messages_order_stock_updated')), config('http_response.success'));
	}

	public function orderItemStatusUpdate(Request $request, $id)
	{
		
        // pending->confirmed->shipped->delivered->completed->replacement_initiated->replacement_acccepted->replacement_completed->return_initiated->return_confirmed->return_declined->return_completed
		$orderItem = OrderItem::find($id);
		$expected_delivery_date = $orderItem->expected_delivery_date;
		$tracking_number = $orderItem->tracking_number;
		$shipment_company_name = $orderItem->shipment_company_name;
		$reason_id_for_cancellation = $orderItem->reason_id_for_cancellation;
		$reason_for_cancellation = $orderItem->reason_for_cancellation;
		$return_applicable_date = $orderItem->return_applicable_date;
		$is_returned = $orderItem->is_returned;
		$amount_returned = $orderItem->amount_returned;
		$is_replaced = $orderItem->is_replaced;
		$is_disputed = $orderItem->is_disputed;
		$delivery_completed_date = $orderItem->delivery_completed_date;
		$reason_for_cancellation_request = $orderItem->reason_for_cancellation_request;
		$reason_for_cancellation_request_decline = $orderItem->reason_for_cancellation_request_decline;
		$ask_for_cancellation = $orderItem->ask_for_cancellation;
		$reason_id_for_cancellation_request = $orderItem->reason_id_for_cancellation_request;
		$reason_id_for_cancellation_request_decline = $orderItem->reason_id_for_cancellation_request_decline;
		$delivery_code = $orderItem->delivery_code;


		if($request->item_status == 'resolved_to_customer')
		{
			$item_status = 'canceled';
		}
		else
		{
			$item_status = $request->item_status;
		}

		if($request->item_status == 'canceled')
		{
			$reason_id_for_cancellation = $request->reason_id;
		}

		if($request->item_status == 'delivered') 
		{
			if(!empty($request->delivery_code) && $request->delivery_code != $delivery_code)
			{
				return response()->json(prepareResult(true, [], 'Delivery code not matched.'), config('http_response.internal_server_error'));
			}
		}
		

		$type = 'delivery';

		$comment = $request->comment;
		if(!empty($request->reason_for_cancellation))
		{
			$reason_for_cancellation = $request->reason_for_cancellation;
			$comment = $request->reason_for_cancellation;
		}
		if($request->item_status == 'completed')
		{
			$return_applicable_date = date('Y-m-d',strtotime('+14 days'));
			$delivery_completed_date = date('Y-m-d');

			OrderItem::where('id',$id)->update(['reward_point_status'=>'credited']);
			$user = $orderItem->user;
			$user->update(['reward_points'=>($user->reward_points + $orderItem->earned_reward_points)]);
		}
		if(!empty($request->expected_delivery_date))
		{
			$expected_delivery_date = date('Y-m-d',strtotime($request->expected_delivery_date));
		}
		if(!empty($request->tracking_number))
		{
			$tracking_number = $request->tracking_number;
		}
		if(!empty($request->shipment_company_name) && ($request->item_status == 'shipped'))
		{
			$shipment_company_name = $request->shipment_company_name;
		}


		if($request->item_status == 'replacement_initiated')
		{
			$comment = $request->reason_of_replacement;
			$type = 'replacement';
			$is_replaced = true;

			if($request->replacement_type == 'by_hand')
			{
				$replacement_code = rand(100000,999999);
			}
			else
			{
				$replacement_code = null;
			}

			$addressfind = AddressDetail::find($request->address_id);

			$orderItemReplacement = new OrderItemReplacement;
			$orderItemReplacement->user_id                   	= Auth::id();
			$orderItemReplacement->replacement_address_id       = $request->address_id;
			$orderItemReplacement->order_item_id             	= $id;
			$orderItemReplacement->products_services_book_id 	= $orderItem->products_services_book_id;
			$orderItemReplacement->quantity                  	= $request->quantity;
			$orderItemReplacement->replacement_type             = $request->replacement_type;
			$orderItemReplacement->shipment_company_name     	= $request->shipment_company_name;
			$orderItemReplacement->reason_id_for_replacement    = $request->reason_id;
			$orderItemReplacement->reason_of_replacement        = $request->reason_of_replacement;
			$orderItemReplacement->date_of_replacement_initiated = date('Y-m-d');
			$orderItemReplacement->images                    	= $request->images;
			$orderItemReplacement->replacement_tracking_number  = $request->replacement_tracking_number;
			$orderItemReplacement->expected_replacement_date = date('Y-m-d',strtotime($request->expected_replacement_date));
			$orderItemReplacement->first_name					= $orderItem->productsServicesBook->user->first_name;
			$orderItemReplacement->last_name					= $orderItem->productsServicesBook->user->last_name;
			$orderItemReplacement->email						= $orderItem->productsServicesBook->user->email;
			$orderItemReplacement->contact_number				= $orderItem->productsServicesBook->user->contact_number;
			$orderItemReplacement->latitude           	= $addressfind->latitude;
			$orderItemReplacement->longitude           	= $addressfind->longitude;
			$orderItemReplacement->country             	= $addressfind->country;
			$orderItemReplacement->state               	= $addressfind->state;
			$orderItemReplacement->city                	= $addressfind->city;
			$orderItemReplacement->full_address        	= $addressfind->full_address;
			$orderItemReplacement->replacement_status             = $request->item_status;
			$orderItemReplacement->replacement_code             = $replacement_code;
			$orderItemReplacement->save();

			$title = 'Replacement Request';
			$body =  'Order for '.$orderItem->title.' has Replacement Request because of '.$request->reason_of_replacement.'.';


			//Mail-start


			///to Buyer

			$emailTemplate = EmailTemplate::where('template_for','order_replacement_request')->where('language_id',$orderItem->order->user->language_id)->first();
			if(empty($emailTemplate))
			{
				EmailTemplate::where('template_for','order_replacement_request')->first();
			}

			$mail_body = $emailTemplate->body;

			$arrayVal = [
				'{{user_name}}' => AES256::decrypt($orderItem->order->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($orderItem->order->last_name, env('ENCRYPTION_KEY')),
				'{{order_item}}' => $orderItem->title,
				'{{replacement_reason}}' => $request->reason_of_replacement,
			];
			$mail_body = strReplaceAssoc($arrayVal, $mail_body);
			
			$details = [
				'title' => $emailTemplate->subject,
				'body' => $mail_body
			];
			
			Mail::to(AES256::decrypt($orderItem->order->email, env('ENCRYPTION_KEY')))->send(new OrderStatusMail($details));

			///to Seller

			$seller_details = [
				'title' => $title,
				'body' => $body
			];
			
			Mail::to(AES256::decrypt($orderItem->productsServicesBook->user->email, env('ENCRYPTION_KEY')))->send(new OrderMail($seller_details));

			//Mail end
		}

		if($request->item_status == 'replaced')
		{
			$type = 'replacement';

			$orderItemReplacement = OrderItemReplacement::where('order_item_id',$id)->first();
			if($orderItemReplacement->replacement_type == 'by_hand' && $request->replacement_code != $orderItemReplacement->replacement_code)
			{
				return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_replacement_code_error')), config('http_response.internal_server_error'));
			}
			$orderItemReplacement->date_of_replacement_completed   	= date('Y-m-d');
			$orderItemReplacement->replacement_status             	= $request->item_status;
			$orderItemReplacement->save();

			if(empty($request->replacement_code))
			{
				$orderItem = OrderItem::find($orderItemReplacement->order_item_id);
				$replaceOrderItem = new OrderItem;
				$replaceOrderItem->user_id						= $orderItem->user_id;
				$replaceOrderItem->order_id						= $orderItem->order_id;
				$replaceOrderItem->order_item_id				= $orderItem->id;
				$replaceOrderItem->products_services_book_id	= $orderItem->products_services_book_id;
				$replaceOrderItem->product_type					= $orderItem->product_type;
				$replaceOrderItem->title						= $orderItem->title;
				$replaceOrderItem->sku							= $orderItem->sku;
				$replaceOrderItem->price                        = $orderItem->price;
				$replaceOrderItem->quantity						= $orderItemReplacement->quantity;
				$replaceOrderItem->discount						= $orderItem->discount;
				$replaceOrderItem->cover_image                  = $orderItem->cover_image;
				$replaceOrderItem->sell_type					= $orderItem->sell_type;
				$replaceOrderItem->rent_duration				= $orderItem->rent_duration;
				$replaceOrderItem->item_status					= 'processing';
				$replaceOrderItem->item_payment_status			= true;
				$replaceOrderItem->note_to_seller               = $orderItem->note_to_seller;
				$replaceOrderItem->order_type               	= '1';
				$replaceOrderItem->save();

				$orderTracking                  = new OrderTracking;
				$orderTracking->order_item_id   = $replaceOrderItem->id;
				$orderTracking->status          ='processing';
				$orderTracking->comment         = '';
				$orderTracking->type         	= 'delivery';
				$orderTracking->save();
			}

			$title = 'Replacement Request Accepted';
			$body =  'Request for replacement of ordered product '.$orderItem->title.' has Accepted.';


			//Mail-start


			///to Buyer

			$emailTemplate = EmailTemplate::where('template_for','order_replaced')->where('language_id',$orderItem->order->user->language_id)->first();
			if(empty($emailTemplate))
			{
				EmailTemplate::where('template_for','order_replaced')->first();
			}

			$mail_body = $emailTemplate->body;

			$arrayVal = [
				'{{user_name}}' => AES256::decrypt($orderItem->order->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($orderItem->order->last_name, env('ENCRYPTION_KEY')),
				'{{order_item}}' => $orderItem->title,
			];
			$mail_body = strReplaceAssoc($arrayVal, $mail_body);
			
			$details = [
				'title' => $emailTemplate->subject,
				'body' => $mail_body
			];
			
			Mail::to(AES256::decrypt($orderItem->order->email, env('ENCRYPTION_KEY')))->send(new OrderStatusMail($details));

			//Mail end
		}

		if($request->item_status == 'return_initiated')
		{
			$is_returned = true;
			$comment = $request->reason_of_return;
			$type = 'return';
			if($request->return_type == 'by_hand')
			{
				$return_code = rand(100000,999999);
			}
			else
			{
				$return_code = null;
			} 

			$addressfind = AddressDetail::find($request->address_id);

			$orderItemReturn = new OrderItemReturn;
			$orderItemReturn->user_id                  	= Auth::id();
			$orderItemReturn->return_address_id        	= $request->address_id;
			$orderItemReturn->order_item_id            	= $id;
			$orderItemReturn->products_services_book_id	= $orderItem->products_services_book_id;
			$orderItemReturn->quantity                 	= $request->quantity;
			$orderItemReturn->return_type              	= $request->return_type;
			$orderItemReturn->shipment_company_name    	= $request->shipment_company_name;
			$orderItemReturn->reason_id_for_return      = $request->reason_id;
			$orderItemReturn->reason_of_return         	= $request->reason_of_return;
			$orderItemReturn->date_of_return_initiated 	= date('Y-m-d');
			$orderItemReturn->images                   	= $request->images;
			$orderItemReturn->amount_to_be_returned    	= $orderItem->price * $request->quantity;
			$orderItemReturn->return_card_number       	= $request->return_card_number;
			$orderItemReturn->return_card_holder_name  	= $request->return_card_holder_name;
			$orderItemReturn->return_tracking_number   	= $request->return_tracking_number;
			$orderItemReturn->expected_return_date     	= date('Y-m-d',strtotime($request->expected_return_date));
			$orderItemReturn->first_name				= $orderItem->productsServicesBook->user->first_name;
			$orderItemReturn->last_name					= $orderItem->productsServicesBook->user->last_name;
			$orderItemReturn->email						= $orderItem->productsServicesBook->user->email;
			$orderItemReturn->contact_number			= $orderItem->productsServicesBook->user->contact_number;
			$orderItemReturn->latitude            		= $addressfind->latitude;
			$orderItemReturn->longitude           		= $addressfind->longitude;
			$orderItemReturn->country             		= $addressfind->country;
			$orderItemReturn->state               		= $addressfind->state;
			$orderItemReturn->city                		= $addressfind->city;
			$orderItemReturn->full_address        		= $addressfind->full_address;
			$orderItemReturn->return_status				= $request->item_status;
			$orderItemReturn->return_code               = $return_code;
			$orderItemReturn->save();

			$title = 'Return Request';
			$body =  'Order for '.$orderItem->title.' has Return Request because of '.$request->reason_of_return.'.';
			
			//Mail-start


			///to Buyer

			$emailTemplate = EmailTemplate::where('template_for','order_return_request')->where('language_id',$orderItem->order->user->language_id)->first();
			if(empty($emailTemplate))
			{
				EmailTemplate::where('template_for','order_return_request')->first();
			}

			$mail_body = $emailTemplate->body;

			$arrayVal = [
				'{{user_name}}' => AES256::decrypt($orderItem->order->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($orderItem->order->last_name, env('ENCRYPTION_KEY')),
				'{{order_item}}' => $orderItem->title,
				'{{return_reason}}' => $request->reason_of_return,
			];
			$mail_body = strReplaceAssoc($arrayVal, $mail_body);
			
			$details = [
				'title' => $emailTemplate->subject,
				'body' => $mail_body
			];
			
			Mail::to(AES256::decrypt($orderItem->order->email, env('ENCRYPTION_KEY')))->send(new OrderStatusMail($details));

			///to Seller

			$seller_details = [
				'title' => $title,
				'body' => $body
			];
			
			Mail::to(AES256::decrypt($orderItem->productsServicesBook->user->email, env('ENCRYPTION_KEY')))->send(new OrderMail($seller_details));

			//Mail end
		}

		if($request->item_status == 'returned')
		{
			$type = 'return';
			$orderItemReturn = OrderItemReturn::where('order_item_id',$id)->first();
			$amount_returned = $orderItemReturn->amount_to_be_returned;
			// if($orderItemReturn->return_type == 'by_hand' && $request->return_code != $orderItemReturn->return_code)
			// {
			// 	return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_return_code_error')), config('http_response.internal_server_error'));
			// }
			$orderItemReturn->date_of_return_completed      = date('Y-m-d');
			$orderItemReturn->return_status                 = $request->item_status;
			$orderItemReturn->save();

			$title = 'Return Request Accepted';
			$body =  'Request for return of ordered product '.$orderItem->title.' has Accepted.';

			//Mail-start


			///to Buyer


			$emailTemplate = EmailTemplate::where('template_for','order_returned')->where('language_id',$orderItem->order->user->language_id)->first();
			if(empty($emailTemplate))
			{
				EmailTemplate::where('template_for','order_returned')->first();
			}

			$mail_body = $emailTemplate->body;

			$arrayVal = [
				'{{user_name}}' => AES256::decrypt($orderItem->order->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($orderItem->order->last_name, env('ENCRYPTION_KEY')),
				'{{order_item}}' => $orderItem->title,
			];
			$mail_body = strReplaceAssoc($arrayVal, $mail_body);
			
			$details = [
				'title' => $emailTemplate->subject,
				'body' => $mail_body
			];
			
			Mail::to(AES256::decrypt($orderItem->order->email, env('ENCRYPTION_KEY')))->send(new OrderStatusMail($details));

			//Mail end

			$add_qty = ProductsServicesBook::where('id',$orderItemReturn->products_services_book_id)->first()->quantity +
			$orderItemReturn->quantity;

			ProductsServicesBook::where('id',$orderItemReturn->products_services_book_id)->update(['quantity' => $add_qty]);
		}

		if($request->item_status == 'dispute_initiated')
		{
			$is_disputed = true;
			$comment = $request->dispute;
			$type = 'dispute';
			if($orderItem->user_id == Auth::id())
			{
				$dispute_raised_against = $orderItem->productsServicesBook->user_id;
			}
			else
			{
				$dispute_raised_against = $orderItem->user_id;
			}

			$orderItemDispute = new OrderItemDispute;
			$orderItemDispute->dispute_raised_by          	= Auth::id();
			$orderItemDispute->dispute_raised_against     	= $dispute_raised_against;
			$orderItemDispute->order_item_id              	= $id;
			$orderItemDispute->products_services_book_id 	= $orderItem->products_services_book_id;
			$orderItemDispute->quantity                  	= $request->quantity;
			$orderItemDispute->amount_to_be_returned        = $orderItem->price * $request->quantity;
			$orderItemDispute->reason_id_for_dispute        = $request->reason_id;
			$orderItemDispute->dispute                  	= $request->dispute;
			$orderItemDispute->dispute_status             	= $request->item_status;
			$orderItemDispute->dispute_images               = $request->dispute_images;
			$orderItemDispute->save();

			$title = 'Dispute Raised';
			$body =  'Dispute raised on Ordered product '.$orderItem->title.'.';
			$emailTemplate = EmailTemplate::where('template_for','order_dispute_raised')->first();
		}

		if($request->item_status == 'resolved_to_customer')
		{
			$type = 'dispute';
			$reason_for_cancellation = $request->reply;
			$orderItemDispute = OrderItemDispute::where('order_item_id',$id)->first();
			$orderItemDispute->date_of_dispute_completed     = date('Y-m-d');
			$orderItemDispute->reply                 		= $request->reply;
			$orderItemDispute->dispute_status                = $request->item_status;
			$orderItemDispute->save();

			$title = 'Dispute Resolved';
			$body =  'Dispute raised on Ordered product '.$orderItem->title.' has been resolved.';
			$emailTemplate = EmailTemplate::where('template_for','order_dispute_resolved')->first();
		}

		if($request->item_status == 'reviewed_by_seller')
		{
			$type = 'dispute';

			$orderItemDispute = OrderItemDispute::where('order_item_id',$id)->first();
			$orderItemDispute->dispute_status                = $request->item_status;
			$orderItemDispute->reason_id_for_review          = $request->reason_id;
			$orderItemDispute->review_by_seller              = $request->review_by_seller;
			$orderItemDispute->review_images                 = $request->review_images;
			$orderItemDispute->save();

			$title = 'Dispute Reviewed';
			$body =  'Dispute raised on Ordered product '.$orderItem->title.' has been reviewed by seller.';
			$emailTemplate = EmailTemplate::where('template_for','order_dispute_reviewed_by_seller')->first();
		}

		if($request->item_status == 'review_accepted')
		{
			$type = 'dispute';

			$item_status = 'completed';

			$orderItemDispute = OrderItemDispute::where('order_item_id',$id)->first();
			$orderItemDispute->dispute_status                = $request->item_status;
			$orderItemDispute->date_of_dispute_completed     = date('Y-m-d');
			$orderItemDispute->save();

			$title = 'Dispute Review Accepted';
			$body =  'Dispute reviewed by seller on Ordered product '.$orderItem->title.' has been Accepted by user.';
			$emailTemplate = EmailTemplate::where('template_for','order_completed')->first();
		}

		if($request->item_status == 'review_declined')
		{
			$type = 'dispute';

			$orderItemDispute = OrderItemDispute::where('order_item_id',$id)->first();
			$orderItemDispute->dispute_status                = $request->item_status;
			$orderItemDispute->reason_id_for_review_decline  = $request->reason_id;
			$orderItemDispute->reason_for_review_decline     = $request->reason_for_review_decline;
			$orderItemDispute->review_decline_images         = $request->review_decline_images;
			$orderItemDispute->save();

			$title = 'Dispute Review Declined';
			$body =  'Dispute reviewed by seller on Ordered product '.$orderItem->title.' has been Declined by user.';
			$emailTemplate = EmailTemplate::where('template_for','order_dispute_review_declined')->first();
		}

		if($request->item_status == 'declined')
		{
			if(!empty($request->reason_for_return_decline))
			{
				$type = 'return';
				$comment = $request->reason_for_return_decline;

				$orderItemReturn = OrderItemReturn::where('order_item_id',$id)->first();
				$orderItemReturn->reason_id_for_return_decline  = $request->reason_id;
				$orderItemReturn->reason_for_return_decline     = $request->reason_for_return_decline;
				$orderItemReturn->return_status                 = $request->item_status;
				$orderItemReturn->save();

				$title = 'Return request Declined';
				$body =  'Return request of ordered product '.$orderItem->title.' has been Declined.';
				$emailTemplate = EmailTemplate::where('template_for','order_return_declined')->first();
			}
			elseif(!empty($request->reason_for_dispute_decline))
			{
				$type = 'dispute';
				$comment = $request->reason_for_dispute_decline;

				$orderItemReturn = OrderItemDispute::where('order_item_id',$id)->first();
				$orderItemReturn->reason_id_for_dispute_decline  = $request->reason_id;
				$orderItemReturn->reason_for_dispute_decline     = $request->reason_for_dispute_decline;
				$orderItemReturn->dispute_status                 = $request->item_status;
				$orderItemReturn->save();

				$title = 'Raised Dispute Declined';
				$body =  'Raised Dispute on  ordered product '.$orderItem->title.' has been Declined.';
				$emailTemplate = EmailTemplate::where('template_for','order_dispute_declined')->first();
			}
			elseif(!empty($request->reason_for_replacement_decline))
			{
				$type = 'replacement';
				$comment = $request->reason_for_replacement_decline;

				$orderItemReplacement = OrderItemReplacement::where('order_item_id',$id)->first();
				$orderItemReplacement->reason_id_for_replacement_decline  	= $request->reason_id;
				$orderItemReplacement->reason_for_replacement_decline   = $request->reason_for_replacement_decline;
				$orderItemReplacement->replacement_status               = $request->item_status;
				$orderItemReplacement->save();

				$title = 'Replacement request Declined';
				$body =  'Replacement request of ordered product '.$orderItem->title.' has been Declined.';
				$emailTemplate = EmailTemplate::where('template_for','order_replacement_declined')->first();
			}
			// elseif(!empty($request->reason_for_review_decline))
			// {
			// 	$type = 'dispute';

			// 	$orderItemDispute = OrderItemDispute::where('order_item_id',$id)->first();
			// 	$orderItemDispute->dispute_status                = $request->item_status;
			// 	$orderItemDispute->reason_id_for_review_decline  = $request->reason_id;
			// 	$orderItemDispute->reason_for_review_decline     = $request->reason_for_review_decline;
			// 	$orderItemDispute->review_images                 = $request->review_images;
			// 	$orderItemDispute->save();

			// 	$title = 'Dispute Review Declined';
			// 	$body =  'Dispute reviewed by seller on Ordered product '.$orderItem->title.' has been Declined by user.';
			// }
		}

		if($request->item_status == 'ask_to_cancel')
		{
			$type = 'no_tracking';
			$item_status = $orderItem->item_status;
			$reason_id_for_cancellation_request = $request->reason_id;
			$reason_for_cancellation_request = $request->reason_for_cancellation_request;
			$title = 'Order Cancellation Request from seller.';
			$body =  'Order for '.$orderItem->title.' has been requested for cancellation by the seller.';
			$ask_for_cancellation = '1';
		}

		if($request->item_status == 'cancelation_request_accepted')
		{
			$type = 'delivery';
			$item_status = 'canceled';
			$reason_for_cancellation = $orderItem->reason_for_cancellation_request;
			$title = 'Order Cancellation Request accepted by the buyer.';
			$body =  'Cancellation request for  '.$orderItem->title.' has been accepted  by the user.';
			$ask_for_cancellation = '2';
		}

		if($request->item_status == 'cancelation_request_declined')
		{
			$type = 'no_tracking';
			$item_status = 'processing';
			$reason_id_for_cancellation_request_decline = $request->reason_id;
			$reason_for_cancellation_request_decline = $request->reason_for_cancellation_request_decline;
			$title = 'Order Cancellation Request declined by the buyer.';
			$body =  'Cancellation request for  '.$orderItem->title.' has been declined  by the user.';
			$ask_for_cancellation = '3';
		}

		$orderItem->item_status                     			= $item_status;
		$orderItem->tracking_number 		        			= $tracking_number;
		$orderItem->shipment_company_name 	        			= $shipment_company_name;
		$orderItem->return_applicable_date 	        			= $return_applicable_date;
		$orderItem->expected_delivery_date 	        			= $expected_delivery_date;
		$orderItem->delivery_completed_date         			= $delivery_completed_date;
		$orderItem->ask_for_cancellation 						= $ask_for_cancellation;
		$orderItem->reason_id_for_cancellation      			= $reason_id_for_cancellation;
		$orderItem->reason_for_cancellation         			= $reason_for_cancellation;
		$orderItem->reason_id_for_cancellation_request 			= $reason_id_for_cancellation_request;
		$orderItem->reason_for_cancellation_request 			= $reason_for_cancellation_request;
		$orderItem->reason_id_for_cancellation_request_decline 	= $reason_id_for_cancellation_request_decline;
		$orderItem->reason_for_cancellation_request_decline 	= $reason_for_cancellation_request_decline;
		$orderItem->is_returned 								= $is_returned;
		$orderItem->amount_returned 							= $amount_returned;
		$orderItem->is_replaced 								= $is_replaced;
		$orderItem->is_disputed 								= $is_disputed;
		$orderItem->save();

		if($item_status == 'canceled')
		{
			$refundOrderItemId = $orderItem->id;
			$refundOrderItemPrice = $orderItem->price;
			$refundOrderItemQuantity = $orderItem->quantity;
			$refundOrderItemReason = 'cancellation';
			refund($refundOrderItemId,$refundOrderItemPrice,$refundOrderItemQuantity,$refundOrderItemReason);
		}
		if($item_status == 'returned')
		{
			$refundOrderItemId = $orderItem->id;
			$refundOrderItemPrice = $orderItem->price;
			$refundOrderItemQuantity = $orderItemReturn->quantity;
			$refundOrderItemReason = 'return';
			refund($refundOrderItemId,$refundOrderItemPrice,$refundOrderItemQuantity,$refundOrderItemReason);
		}

		if($type == 'no_tracking')
		{

		}
		else
		{
			$orderTracking = new OrderTracking;
			$orderTracking->order_item_id = $id;
			$orderTracking->status = $request->item_status; 
			$orderTracking->comment = $comment;
			$orderTracking->type = $type;
			$orderTracking->save();
		}
		

        // Notification Start

		if(Auth::id() == $orderItem->user_id)
		{
			$user = $orderItem->productsServicesBook->user;
			$user_type = 'seller';
			$screen = 'market-place-request';
		}
		else
		{
			$user = $orderItem->user;
			$user_type = 'buyer';
			$screen = 'my_orders';
		}

		if($request->item_status == 'confirmed')
		{
			$title = 'Order Confirmed';
			$body =  'Order for '.$orderItem->title.' has been Confirmed.';

			//Mail-start


			///to Buyer

			$emailTemplate = EmailTemplate::where('template_for','order_confirmed')->where('language_id',$orderItem->order->user->language_id)->first();
			if(empty($emailTemplate))
			{
				EmailTemplate::where('template_for','order_confirmed')->first();
			}

			$mail_body = $emailTemplate->body;

			$arrayVal = [
				'{{user_name}}' => AES256::decrypt($orderItem->order->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($orderItem->order->last_name, env('ENCRYPTION_KEY')),
				'{{order_item}}' => $orderItem->title,
			];
			$mail_body = strReplaceAssoc($arrayVal, $mail_body);
			
			$details = [
				'title' => $emailTemplate->subject,
				'body' => $mail_body
			];
			
			Mail::to(AES256::decrypt($orderItem->order->email, env('ENCRYPTION_KEY')))->send(new OrderStatusMail($details));

			//Mail end
		}
		elseif($request->item_status == 'shipped')
		{
			$title = 'Order Shipped';
			$body =  'Order for '.$orderItem->title.' has been Shipped.';

			
			//Mail start
			///to Buyer

			$emailTemplate = EmailTemplate::where('template_for','order_shipped')->where('language_id',$orderItem->order->user->language_id)->first();
			if(empty($emailTemplate))
			{
				EmailTemplate::where('template_for','order_shipped')->first();
			}

			$mail_body = $emailTemplate->body;

			$arrayVal = [
				'{{user_name}}' => AES256::decrypt($orderItem->order->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($orderItem->order->last_name, env('ENCRYPTION_KEY')),
				'{{order_item}}' => $orderItem->title,
			];
			$mail_body = strReplaceAssoc($arrayVal, $mail_body);
			
			$details = [
				'title' => $emailTemplate->subject,
				'body' => $mail_body
			];
			
			Mail::to(AES256::decrypt($orderItem->order->email, env('ENCRYPTION_KEY')))->send(new OrderStatusMail($details));

			//Mail end
		}
		elseif($request->item_status == 'delivered')
		{
			$title = 'Order Delivered';
			$body =  'Order for '.$orderItem->title.' has been Delivered.';
			

			//Mail start
			///to Buyer

			$emailTemplate = EmailTemplate::where('template_for','order_delivered')->where('language_id',$orderItem->order->user->language_id)->first();
			if(empty($emailTemplate))
			{
				EmailTemplate::where('template_for','order_delivered')->first();
			}

			$mail_body = $emailTemplate->body;

			$arrayVal = [
				'{{user_name}}' => AES256::decrypt($orderItem->order->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($orderItem->order->last_name, env('ENCRYPTION_KEY')),
				'{{order_item}}' => $orderItem->title,
			];
			$mail_body = strReplaceAssoc($arrayVal, $mail_body);
			
			$details = [
				'title' => $emailTemplate->subject,
				'body' => $mail_body
			];
			
			Mail::to(AES256::decrypt($orderItem->order->email, env('ENCRYPTION_KEY')))->send(new OrderStatusMail($details));

			//Mail end
		}
		elseif($request->item_status == 'completed')
		{
			$title = 'Order Completed';
			$body =  'Order for '.$orderItem->title.' has been Completed.';
			
			//Mail start
			///to Buyer


			$emailTemplate = EmailTemplate::where('template_for','order_completed')->where('language_id',$orderItem->order->user->language_id)->first();
			if(empty($emailTemplate))
			{
				EmailTemplate::where('template_for','order_completed')->first();
			}

			$mail_body = $emailTemplate->body;

			$arrayVal = [
				'{{user_name}}' => AES256::decrypt($orderItem->order->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($orderItem->order->last_name, env('ENCRYPTION_KEY')),
				'{{order_item}}' => $orderItem->title,
			];
			$mail_body = strReplaceAssoc($arrayVal, $mail_body);
			
			$details = [
				'title' => $emailTemplate->subject,
				'body' => $mail_body
			];
			
			Mail::to(AES256::decrypt($orderItem->order->email, env('ENCRYPTION_KEY')))->send(new OrderStatusMail($details));

			//Mail end
		}
		elseif($request->item_status == 'canceled')
		{
			$title = 'Order canceled';
			$body =  'Order for '.$orderItem->title.' has been canceled.';
			$emailTemplate = EmailTemplate::where('template_for','order_canceled')->first();

			//Mail start
			///to Buyer

			
			$emailTemplate = EmailTemplate::where('template_for','order_canceled')->where('language_id',$orderItem->order->user->language_id)->first();
			if(empty($emailTemplate))
			{
				EmailTemplate::where('template_for','order_canceled')->first();
			}

			$mail_body = $emailTemplate->body;

			$arrayVal = [
				'{{user_name}}' => AES256::decrypt($orderItem->order->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($orderItem->order->last_name, env('ENCRYPTION_KEY')),
				'{{order_item}}' => $orderItem->title,
			];
			$mail_body = strReplaceAssoc($arrayVal, $mail_body);
			
			$details = [
				'title' => $emailTemplate->subject,
				'body' => $mail_body
			];
			
			Mail::to(AES256::decrypt($orderItem->order->email, env('ENCRYPTION_KEY')))->send(new OrderStatusMail($details));

			//Mail end
		}

		$type = 'Order Status';

		if($orderItem->productsServicesBook->type == 'book')
		{
			$module = 'book';
		}
		else
		{
			$module = 'product_service';
		}

		pushNotification($title,$body,$user,$type,true,$user_type,$module,$orderItem->id,$screen);

		$orderItem = OrderItem::with('orderTrackings','return','replacement','dispute')->find($id);
		return response()->json(prepareResult(false, $orderItem, getLangByLabelGroups('messages','messages_order_stock_updated')), config('http_response.success'));
	}

	public function ordersCount(Request $request)
	{
		try
		{
			$orders = [];
			$orders['all'] = OrderItem::select('order_items.*')
			->join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->where('products_services_books.user_id',Auth::id())
			->count();

			$orders['under_process'] = OrderItem::select('order_items.*')
			->join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->where('products_services_books.user_id',Auth::id())
			->where('order_items.item_status','processing')
			->count();

			$orders['delivered'] = OrderItem::select('order_items.*')
			->join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->where('products_services_books.user_id',Auth::id())
			->where('order_items.item_status','delivered')
			->count();

			$orders['earnings'] = OrderItem::select('order_items.*')
			->join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->where('products_services_books.user_id',Auth::id())
			->sum('order_items.amount_transferred_to_vendor');

			$orders['amount_refunded'] = OrderItem::select('order_items.*')
			->join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->where('products_services_books.user_id',Auth::id())
			->sum('amount_returned');

			$orders['returned_items'] = OrderItem::select('order_items.*')
			->join('products_services_books', function ($join) {
				$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
			})
			->where('products_services_books.user_id',Auth::id())
			->where('order_items.item_status','returned')
			->count();
			return response(prepareResult(false, $orders, getLangByLabelGroups('messages','messages_order_count_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function reasonForAction(Request $request)
	{
		try
		{
			$reasons = ReasonForAction::orderBy('created_at','DESC');

			if(!empty($request->action))
			{
				$reasons = $reasons->where('action',$request->action);
			}

			if(!empty($request->language_id))
			{
				$reasons = $reasons->where('language_id',$request->language_id);
			}

			if(!empty($request->module_type_id))
			{
				$reasons = $reasons->where('module_type_id',$request->module_type_id);
			}
			$reasons = $reasons->get();
			return response(prepareResult(false, $reasons, getLangByLabelGroups('messages','messages_order_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function generateInvoice($orderId) 
	{
		$getOrder = Order::find($orderId);
		if($getOrder)
		{
			$destinationPath = 'uploads/';
			$fileName = $getOrder->order_number.'.pdf';

			if(file_exists('uploads/'.$fileName)){ 
				unlink('uploads/'.$fileName);
			}
			$data = [
				'order' => $getOrder,
			];
			$pdf = PDF::loadView('invoice', $data);
			$pdf->save('uploads/'.$fileName);
			return response(prepareResult(false, env('APP_URL').$destinationPath.$fileName, 'Invoice'), config('http_response.success'));
		}
		return response()->json(prepareResult(true, 'Not found', getLangByLabelGroups('messages','message_error')), config('http_response.not_found'));
	}

	public function generateItemInvoice($orderItemId) 
	{
		$getOrder = OrderItem::find($orderItemId);
		if($getOrder)
		{
			$destinationPath = 'uploads/';
			$fileName = $orderItemId.'.pdf';

			if(file_exists('uploads/'.$fileName)){ 
				unlink('uploads/'.$fileName);
			}
			$data = [
				'order' => $getOrder,
			];
			//return view('item-invoice')->with('order', $getOrder);
			$pdf = PDF::loadView('item-invoice', $data);
			$pdf->save('uploads/'.$fileName);
			return response(prepareResult(false, env('APP_URL').$destinationPath.$fileName, 'Invoice'), config('http_response.success'));
		}
		return response()->json(prepareResult(true, 'Not found', getLangByLabelGroups('messages','message_error')), config('http_response.not_found'));
	}

	public function createStripeIntent(Request $request)
	{
		$sub_total = 0;
		$shipping_charge = 0;
		$itemInfo = [];
		foreach ($request->items as $key => $orderedItem) {
			if(!empty($orderedItem['product_id']))
			{
				$productsServicesBook = ProductsServicesBook::find($orderedItem['product_id']);
				if($productsServicesBook->is_on_offer == 1)
				{
					$price = $productsServicesBook->discounted_price;
				}
				else
				{
					$price = $productsServicesBook->price;
				}

				if($productsServicesBook->delivery_type == 'deliver_to_location')
				{
					$shipping_package = ShippingCondition::where('user_id',$productsServicesBook->user_id)->where('order_amount_from','<=', $price * $orderedItem['quantity'])->where('order_amount_to','>=',$price * $orderedItem['quantity'])->orderBy('created_at','desc')->first();
					if($shipping_package)
					{
						$shipping_charge = $shipping_charge + ($productsServicesBook->shipping_charge * $orderedItem['quantity']) * (100 - $shipping_package->discount_percent) / 100;
					}
					else
					{
						$shipping_charge = $shipping_charge + ($productsServicesBook->shipping_charge * $orderedItem['quantity']);
					}
				}

				//For klarna
				$itemInfo[] = [
	        		'type'      => 'physical',
	                'reference' => $orderedItem['product_id'],
	                'name'      => $orderedItem['title'],
	                'quantity'  => $orderedItem['quantity'],
	                'unit_price'=> $price * 100,
	                'tax_rate'  => 0,
	                'total_amount'          => $price * 100,
	                'total_discount_amount' => 0,
	                'total_tax_amount'      => 0,
	                'image_url' => $orderedItem['cover_image']
	        	];
			}
			elseif(!empty($orderedItem['contest_id']))
			{
				$productsServicesBook = Contest::find($orderedItem['contest_id']);
				if($productsServicesBook->is_on_offer == 1)
				{
					$price = $productsServicesBook->discounted_price;
				}
				else
				{
					$price = $productsServicesBook->subscription_fees;
				}

				//For klarna
				$itemInfo[] = [
	        		'type'      => 'physical',
	                'reference' => $orderedItem['contest_id'],
	                'name'      => $orderedItem['title'],
	                'quantity'  => $orderedItem['quantity'],
	                'unit_price'=> $price * 100,
	                'tax_rate'  => 0,
	                'total_amount'          => $price * 100,
	                'total_discount_amount' => 0,
	                'total_tax_amount'      => 0,
	                'image_url' => $orderedItem['cover_image']
	        	];
			}
			else
			{
				$productsServicesBook = Package::find($orderedItem['package_id']);
				if($productsServicesBook->price == 0)
				{
					$price = $productsServicesBook->subscription;
				}
				else
				{
					$price = $productsServicesBook->price;
				}
			}

			$sub_total = $sub_total + ($price * $orderedItem['quantity']);
		}

		$reward_point_value = AppSetting::first()->customer_rewards_pt_value * $request->used_reward_points;

		$total = $sub_total - $reward_point_value + $shipping_charge - $request->promo_code_discount;

		if($request->payment_method=='create_klarna_session') {
			$url = env('KLARNA_URL').'/payments/v1/sessions';
	        $username = $this->paymentInfo->klarna_username;
	        $password = $this->paymentInfo->klarna_password;
	        $auth     = base64_encode($username.":".$password);

	        $data = [
	            'purchase_country'  => 'SE',
	            'purchase_currency' => 'SEK',
	            'locale'            => env('KLARNA_LOCALE', 'sv-SE'),
	            'order_amount'      => $total * 100,
	            'order_tax_amount'  => 0,
	            'order_lines'       => $itemInfo
	        ];
	        $postData = json_encode($data);

	        $curl = curl_init();
	        curl_setopt_array($curl, array(
	          CURLOPT_URL => $url,
	          CURLOPT_RETURNTRANSFER => true,
	          CURLOPT_ENCODING => '',
	          CURLOPT_MAXREDIRS => 10,
	          CURLOPT_TIMEOUT => 0,
	          CURLOPT_FOLLOWLOCATION => true,
	          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	          CURLOPT_CUSTOMREQUEST => 'POST',
	          CURLOPT_POSTFIELDS => $postData,
	          CURLOPT_HTTPHEADER => array(
	            'Authorization: Basic '.$auth,
	            'Content-Type: application/json',
	          ),
	        ));

	        $response = curl_exec($curl);
	        if(curl_errno($curl)>0)
	        {
	            $info = curl_errno($curl)>0 ? array("curl_error_".curl_errno($curl)=>curl_error($curl)) : curl_getinfo($curl);
	            return response()->json(prepareResult(true, $info, "Error while creating klarna session"), config('http_response.internal_server_error'));
	        }
	        curl_close($curl);
	        return response()->json(prepareResult(false, json_decode($response, true), "Session successfully created."), config('http_response.success'));
		} elseif($request->payment_method=='create_klarna_customer_token') {
			$user = User::find(Auth::id());
	        $url  = env('KLARNA_URL').'/payments/v1/authorizations/'.$request->auth_token.'/customer-token';

	        $given_name = AES256::decrypt($user->first_name, env('ENCRYPTION_KEY'));
	        $family_name = (!empty(AES256::decrypt($user->last_name, env('ENCRYPTION_KEY')))) ? AES256::decrypt($user->last_name, env('ENCRYPTION_KEY')) : null;
	        $email = AES256::decrypt($user->email, env('ENCRYPTION_KEY'));
	        $phone = AES256::decrypt($user->contact_number, env('ENCRYPTION_KEY'));
	        $street_address = $user->defaultAddress->full_address;
	        $postal_code = $user->defaultAddress->zip_code;
	        $city = $user->defaultAddress->city;

	        $data = [
	            'purchase_country'  => 'SE',
	            'locale'            => env('KLARNA_LOCALE', 'sv-SE'),
	            'billing_address'   => [
	                'given_name'    => $given_name,
	                'family_name'   => $family_name,
	                'email'         => $email,
	                'phone'         => $phone,
	                'street_address'=> $street_address,
	                'postal_code'   => $postal_code,
	                'city'          => $city,
	                'country'       => 'SE'
	            ],
	            'description'       => 'Student Store',
	            'intended_use'      => 'subscription',
	            'merchant_urls'     => [
	                'terms'         => env('FRONT_APP_URL').'page/return-policy',
	                'checkout'      => env('FRONT_APP_URL').'cart',
	                'confirmation'  => env('FRONT_APP_URL').'confirmation',
	                'push'          => env('APP_URL').'api/push-notification-klarna',
	            ]
	        ];
	        $postData = json_encode($data);

	        $curl = curl_init();
	        curl_setopt_array($curl, array(
	          CURLOPT_URL => $url,
	          CURLOPT_RETURNTRANSFER => true,
	          CURLOPT_ENCODING => '',
	          CURLOPT_MAXREDIRS => 10,
	          CURLOPT_TIMEOUT => 0,
	          CURLOPT_FOLLOWLOCATION => true,
	          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	          CURLOPT_CUSTOMREQUEST => 'POST',
	          CURLOPT_POSTFIELDS => $postData,
	          CURLOPT_HTTPHEADER => array(
	            'Authorization: Basic '.$auth,
	            'Content-Type: application/json',
	          ),
	        ));

	        $response = curl_exec($curl);
	        if(curl_errno($curl)>0)
	        {
	            $info = curl_errno($curl)>0 ? array("curl_error_".curl_errno($curl)=>curl_error($curl)) : curl_getinfo($curl);
	            return response()->json(prepareResult(true, $info, "Error while creating klarna session"), config('http_response.internal_server_error'));
	        }
	        $responseDecode = json_decode($response, true);
	        $user->klarna_customer_token = $responseDecode['token_id'];
	        $user->save();
	        curl_close($curl);
	        return response()->json(prepareResult(false, $responseDecode, "Session successfully created."), config('http_response.success'));
		} elseif($request->payment_method=='place_order_from_klarna_customer_token') {
			$url = env('KLARNA_URL').'/customer-token/v1/tokens/'.$request->customerToken.'/order';
	        $username = $this->paymentInfo->klarna_username;
	        $password = $this->paymentInfo->klarna_password;
	        $auth     = base64_encode($username.":".$password);

	        $data = [
	        	'merchant_reference1' 	=>  '45aa52f387871e3a210645d4',
	        	'merchant_reference2' 	=>  '45aa52f387871e3a210645d4',
			    'merchant_data' 		=>  date('Y-m-d'),
			    'locale' 	=>  env('KLARNA_LOCALE', 'sv-SE'),
			    'auto_capture' 			=>  true,
			    'purchase_currency' 	=>  'SEK',
			    'order_amount' 			=>  $total * 100,
			    'order_tax_amount' 		=>  0,
			    'order_lines' 			=>  $itemInfo
	        ];
	        $postData = json_encode($data);

	        $curl = curl_init();
	        curl_setopt_array($curl, array(
	          CURLOPT_URL => $url,
	          CURLOPT_RETURNTRANSFER => true,
	          CURLOPT_ENCODING => '',
	          CURLOPT_MAXREDIRS => 10,
	          CURLOPT_TIMEOUT => 0,
	          CURLOPT_FOLLOWLOCATION => true,
	          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	          CURLOPT_CUSTOMREQUEST => 'POST',
	          CURLOPT_POSTFIELDS => $postData,
	          CURLOPT_HTTPHEADER => array(
	            'Authorization: Basic '.$auth,
	            'Content-Type: application/json',
	          ),
	        ));

	        $response = curl_exec($curl);
	        if(curl_errno($curl)>0)
	        {
	            $info = curl_errno($curl)>0 ? array("curl_error_".curl_errno($curl)=>curl_error($curl)) : curl_getinfo($curl);
	            return response()->json(prepareResult(true, $info, "Error while creating klarna session"), config('http_response.internal_server_error'));
	        }
	        curl_close($curl);
	        return response()->json(prepareResult(false, json_decode($response, true), "Session successfully created."), config('http_response.success'));
		} else {
			\Stripe\Stripe::setApiKey($this->paymentInfo->payment_gateway_secret);

			$customer_id = $request->customer_id;
			$ephemeralKey = \Stripe\EphemeralKey::create(
			    ['customer' 		=> $customer_id],
			    ['stripe_version' 	=> '2020-08-27']
			);

			$paymentIntent = \Stripe\PaymentIntent::create([
			    'amount' 	=> ($total) * 100,
			    'currency' 	=> $this->paymentInfo->stripe_currency,
			    'customer' 	=> $customer_id
			]);

			$returnObj = [
				'paymentIntent' 	=> $paymentIntent->client_secret,
			    'ephemeralKey' 		=> $ephemeralKey->secret,
			    'customer' 			=> $customer_id,
			    'payable_amount' 	=> $total,
			];
			return response(prepareResult(false, $returnObj, 'Order Intent create'), config('http_response.success'));
		}
	}

	public function createStripeSubscription(Request $request)
	{
		//cancel Subcription if exist
		$checkPackage = Package::where('stripe_plan_id', $request->stripe_plan_id)->first();
		if($checkPackage)
		{
			$user_package = UserPackageSubscription::where('package_id', $checkPackage->id)->where('user_id', Auth::id())->whereNotNull('subscription_id')->where('payby','stripe')->where('is_canceled', 0)->orderBy('auto_id', 'DESC')->first();
		}


		$stripe = new \Stripe\StripeClient($this->paymentInfo->payment_gateway_secret);
		$subscription = $stripe->subscriptions->create([
		  'customer' => Auth::user()->stripe_customer_id,
		  'items' => [
		    ['price' => $request->stripe_plan_id],
		  ],
		  'payment_behavior' => 'default_incomplete',
		  'expand' => ['latest_invoice.payment_intent'],
		]);
		$returnObj = [
			'subscription_id' 	=> $subscription->id,
			'client_secret' 	=> $subscription->latest_invoice->payment_intent->client_secret,
			'status' 			=> $subscription->status,
			'hosted_invoice_url'=> $subscription->latest_invoice->hosted_invoice_url,
		];
		if($subscription->status=='active') 
		{
			//if success then unsubscribe same package which is already subscribed
			if($user_package)
			{
				$user_package->is_canceled = 1;
				$user_package->canceled_date = date('Y-m-d');
				$user_package->save();

				$stripe = new \Stripe\StripeClient($this->paymentInfo->payment_gateway_secret);
				$cancelSubscription = $stripe->subscriptions->cancel(
				  	$user_package->subscription_id,
				  	[]
				);
			}
		}
		return response(prepareResult(false, $returnObj, 'Cancel Subscription'), config('http_response.success'));
	}

	public function cancelStripeSubscription(Request $request)
	{
		$user_package = UserPackageSubscription::where('subscription_id', $request->subscription_id)->orderBy('auto_id', 'DESC')->first();
		if($user_package)
		{
			$user_package->is_canceled = 1;
			$user_package->canceled_date = date('Y-m-d');
			$user_package->save();

			$stripe = new \Stripe\StripeClient($this->paymentInfo->payment_gateway_secret);
			$cancelSubscription = $stripe->subscriptions->cancel(
			  	$request->subscription_id,
			  	[]
			);
			$user_package->response_request = str_replace('Stripe\Subscription JSON: ', '', $cancelSubscription);
			$user_package->save();

			$title = 'Package Subscription Canceled';
            $body =  'Your '.$user_package->package->module.' module '.getLangByLabelGroups('packages', $user_package->package->type_of_package).' package is successfully canceled.';
            $user = $user_package->user;
            $type = 'Package';
            $user_type = 'buyer';
            $module = 'profile';
            pushNotification($title,$body,$user,$type,true,$user_type,$module,'no-data','package');

			return response(prepareResult(false, $user_package->response_request, 'Cancel Subscription'), config('http_response.success'));
		}
		return response()->json(prepareResult(true, 'Subscription id not found.', getLangByLabelGroups('messages','message_error')), config('http_response.not_found'));
	}
}
