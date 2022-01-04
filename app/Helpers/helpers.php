<?php 
use App\Models\User;
use App\Models\LabelGroup;
use Edujugon\PushNotification\PushNotification;
use App\Models\UserDeviceInfo;
use App\Models\Notification;
use Stripe as ST;

use App\Models\AppSetting;
use App\Models\OrderItem;
use App\Models\TransactionDetail;
use App\Models\Refund;
use App\Models\SharedRewardPoint;
use App\Models\PaymentGatewaySetting;
use App\Models\UserPackageSubscription;
use App\Models\ProductsServicesBook;
use App\Models\Contest;

function prepareResult($error, $data, $msg)
{
	return ['error' => $error, 'data' => $data, 'message' => $msg];
}

function getUserLanguage()
{
	$getLang = env('APP_DEFAULT_LANGUAGE', '1');
	if(Auth::check())
	{
		$getLang = Auth::user()->language_id;
		if(empty($getLang))
		{
			$getLang = env('APP_DEFAULT_LANGUAGE', '1');
		}
	}
	return $getLang;
}

function getLangByLabelGroups($groupName, $label_name)
{
	$getLang = getUserLanguage();
	$getLabelGroup = LabelGroup::select('id')
	->with(['returnLabelNames' => function($q) use ($getLang, $label_name) {
		$q->select('id','label_group_id','label_value')
		->where('language_id', $getLang)
		->where('label_name', $label_name);
	}])
	->where('name', $groupName)
	->first();
	$data = @$getLabelGroup->returnLabelNames;
	return @$data['label_value'];
}

function pushNotification($title,$body,$user,$type,$save_to_database,$user_type,$module,$id,$screen)
{
	$userDeviceInfo = UserDeviceInfo::where('user_id',$user->id)->whereIn('platform',['Android','iOS'])->orderBy('created_at', 'DESC')->first();
	if(!empty($userDeviceInfo))
	{ 
		if(!empty($userDeviceInfo->fcm_token))
		{
			$push = new PushNotification('fcm');
			$push->setMessage([
				"notification"=>[
					'title' => $title,
					'body'  => $body,
					'sound' => 'default',
					'android_channel_id' => '1',
                    //'timestamp' => date('Y-m-d G:i:s')
				],
				'data'=>[
					'id'  => $id,
					'user_type'  => $user_type,
					'module'  => $module,
					'screen'  => $screen
				]                        
			])
			->setApiKey(env('FIREBASE_KEY'))
			->setDevicesToken($userDeviceInfo->fcm_token)
			->send();
			/*if($userDeviceInfo->platform=='Android')
			{
				$push = new PushNotification('fcm');
				$push->setMessage([
					"notification"=>[
						'title' => $title,
						'body'  => $body,
						'sound' => 'default',
						'android_channel_id' => '1',
	                    //'timestamp' => date('Y-m-d G:i:s')
					],
					'data'=>[
						'id'  => $id,
						'user_type'  => $user_type,
						'module'  => $module,
						'screen'  => $screen
					]                        
				])
				->setApiKey(env('FIREBASE_KEY'))
				->setDevicesToken($userDeviceInfo->fcm_token)
				->send();
			}
			elseif($userDeviceInfo->platform=='iOS')
			{
				$push = new PushNotification('apn');

				$push->setMessage([
					'aps' => [
		                'alert' => [
		                    'title' => $title,
		                    'body' => $body
		                ],
		                'sound' => 'default',
		                'badge' => 1

		            ],
		            'extraPayLoad' => [
		                'custom' => 'My custom data',
		            ]                       
				])
				->setDevicesToken($userDeviceInfo->fcm_token);
				$push = $push->send();
				//return $push->getFeedback();
			}*/
		}
	}
	if($save_to_database == true)
	{
		$notification = new Notification;
		$notification->user_id          = $user->id;
		$notification->sender_id        = Auth::id();
		$notification->device_uuid      = $userDeviceInfo ? $userDeviceInfo->device_uuid : null;
		$notification->device_platform  = $userDeviceInfo ? $userDeviceInfo->platform : null;
		$notification->type             = $type;
		$notification->user_type        = $user_type;
		$notification->module           = $module;
		$notification->title            = $title;
		$notification->sub_title        = $title;
		$notification->message          = $body;
		$notification->image_url        = '';
		$notification->screen        	= $screen;
		$notification->data_id        	= $id;
		$notification->read_status      = false;
		$notification->save();
	}
}


function pushMultipleNotification($title,$body,$users,$type,$save_to_database,$user_type,$module,$id,$screen)
{
	foreach ($users as $key => $user) {
		$userDeviceInfo = UserDeviceInfo::where('user_id',$user->id)->whereIn('platform',['Android','iOS'])->latest()->first();
		if($userDeviceInfo)
		{
			
			$push = new PushNotification('fcm');
			$push->setMessage([
				"notification"=>[
					'title' => $title,
					'body'  => $body,
					'sound' => 'default',
					'android_channel_id' => '1',
                //'timestamp' => date('Y-m-d G:i:s')
				],
				'data'=>[
					'id'  => $id,
					'user_type'  => $user_type,
					'module'  => $module,
					'screen'  => $screen
				]                         
			])
			->setApiKey(env('FIREBASE_KEY'))
			->setDevicesToken($userDeviceInfo->fcm_token)
			->send();
		}
		if($save_to_database == true)
		{
			$notification = new Notification;
			$notification->user_id          = $user->id;
			$notification->sender_id        = Auth::id();
			$notification->device_uuid      = $userDeviceInfo ? $userDeviceInfo->device_uuid : null;
			$notification->device_platform  = $userDeviceInfo ? $userDeviceInfo->platform : null;
			$notification->type             = '';
			$notification->user_type        = $user_type;
			$notification->module           = $module;
			$notification->title            = $title;
			$notification->sub_title        = $title;
			$notification->message          = $body;
			$notification->image_url        = '';
			$notification->screen        	= $screen;
			$notification->data_id        	= $id;
			$notification->read_status      = false;
			$notification->save();
		}
	}

}


function createResume($fileName,$user)
{
	if(file_exists('public/uploads/'.$fileName)){ 
		unlink('public/uploads/'.$fileName);
	}
	$data = [
		'user' => $user,
	];
	$pdf = PDF::loadView('pdf', $data);
	return $pdf->save('uploads/'.$fileName);
}

function getKlarnaOrderInfo($klarna_transaction_id)
{
	$url = env('KLARNA_URL').'/ordermanagement/v1/orders/'.$klarna_transaction_id;
	$paymentInfo = PaymentGatewaySetting::first();
	$username = $paymentInfo->klarna_username;
    $password = $paymentInfo->klarna_password;
    $auth     = base64_encode($username.":".$password);
    $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Basic '.$auth,
            'Content-Type: application/json',
          ),
        ));

        $response = curl_exec($curl);
        return $response;
}

function refund($refundOrderItemId,$refundOrderItemPrice,$refundOrderItemQuantity,$refundOrderItemReason)
{
	$isRefunded = false;
	$orderItem = OrderItem::find($refundOrderItemId);
	$orderId = $orderItem->order->id;
	$transaction = $orderItem->order->transaction;
	$refund_id = time().'-SYS-GEN';
	if(!empty($transaction->transaction_id) && $refundOrderItemPrice > 0)
	{
		if($transaction->gateway_detail=='stripe' && \Str::lower($transaction->transaction_status)=='succeeded')
		{
			try 
			{
				\Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
				$stripe = new \Stripe\StripeClient(
				  env('STRIPE_SECRET')
				);
				$data = \Stripe\Refund::create([
					'amount' => $refundOrderItemPrice * $refundOrderItemQuantity * 100,
				  	'payment_intent' => $transaction->transaction_id,
				]);
				$refund_id = $data->id;
				$isRefunded = true;
			} 
			catch (\Exception $e) 
			{
			  Log::info('Stripe Payment not refunded. Please check Log');
	      Log::info($e->getMessage());
	      Log::info($orderItem);
	      return 'failed';
			}
		}
		elseif($transaction->gateway_detail=='klarna' && $transaction->transaction_status=='ACCEPTED')
		{
			$isRefunded = true;
			$url = env('KLARNA_URL').'/ordermanagement/v1/orders/'.$transaction->transaction_id.'/refunds';
			$paymentInfo = PaymentGatewaySetting::first();
			$username = $paymentInfo->klarna_username;
	    $password = $paymentInfo->klarna_password;
	    $auth     = base64_encode($username.":".$password);

			$itemInfo[] = [
				"reference" 		=> $orderItem->id,
				'type'      		=> 'physical',
				"quantity" 			=> $refundOrderItemQuantity,
				"quantity_unit" 	=> "pcs.",
				"name"	 			=> $orderItem->title,
				"total_amount" 		=> $refundOrderItemPrice * $refundOrderItemQuantity * 100,
				"unit_price" 		=> $refundOrderItemPrice * 100,
				"total_discount_amount" => 0,
				"tax_rate" 			=> 0,
				"total_tax_amount"	=> 0
			];
			$data = [
	        'refunded_amount'  	=> $refundOrderItemPrice * $refundOrderItemQuantity * 100,
	        'description' 		=> $refundOrderItemReason,
	        'reference'        	=> $orderItem->order->order_number,
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
	    	$isRefunded = false;
	      $info = curl_error($curl);
	      Log::info('Klarna Payment not refunded. Please check Log');
	      Log::error($info);
	      Log::info($orderItem);
	      return 'failed';
	    }
	    else
	    {
	    	$getOrderStatus = getKlarnaOrderInfo($transaction->transaction_id);
	    	$res = json_decode($getOrderStatus, true);
	    	$refund_id = $res['refunds'][0]['refund_id'];
	    	$isRefunded = true;
	    }
	    curl_close($curl);
		}
		elseif($transaction->gateway_detail=='swish')
		{
			$isRefunded = true;
		}
	}
	elseif($transaction->transaction_amount<1)
	{
		$isRefunded = true;
	}
	
	if($isRefunded)
	{
		if($orderItem->used_item_reward_points>0)
		{
			//reward points revert 
			$getOneItemReward = ceil($orderItem->used_item_reward_points / $orderItem->quantity);
			$userInfo = User::find($orderItem->user_id);
			$userInfo->reward_points = $userInfo->reward_points + ($getOneItemReward * $refundOrderItemQuantity);
			$userInfo->save();

			//Log create
			$sharedRewardPoint 						= new SharedRewardPoint;
			$sharedRewardPoint->sender_id 			= User::orderBy('auto_id', 'ASC')->first()->id;
			$sharedRewardPoint->receiver_id 		= $orderItem->user_id;
			$sharedRewardPoint->reward_points 	= ($getOneItemReward * $refundOrderItemQuantity);
			$sharedRewardPoint->save();
		}

		$refund = new Refund;
		$refund->order_id   					= $orderId;
		$refund->payment_card_detail_id   	= $transaction->payment_card_detail_id;
		$refund->order_item_id   			= $refundOrderItemId;
		$refund->transaction_id   		= $transaction->id;
		$refund->refund_id   					= $refund_id;

		if($transaction->gateway_detail=='stripe' && $transaction->transaction_amount>0)
		{
			// $refund->object   					= $data->object;
			$refund->amount   					= $data->amount / 100;
			$refund->balance_transaction= $data->balance_transaction;
			$refund->charge   					= $data->charge;
			$refund->created   					= $data->created;
			$refund->currency   				= $data->currency;
			$refund->metadata   				= $data->metadata;
			$refund->payment_intent   	= $data->payment_intent;
			$refund->reason   					= $data->reason;
			$refund->receipt_number   	= $data->receipt_number;
			$refund->source_transfer_reversal   = $data->source_transfer_reversal;
			$refund->status   					= $data->status;
			$refund->transfer_reversal  = $data->transfer_reversal;
		}
		else
		{
			$refund->amount   					= $refundOrderItemPrice * $refundOrderItemQuantity;
			$refund->created   					= time();
			$refund->currency   				= 'SEK';
		}
		
		$refund->gateway_detail   			= $transaction->gateway_detail;
		$refund->transaction_type   		= $transaction->transaction_type;
		$refund->transaction_mode   		= $transaction->transaction_mode;
		$refund->card_number   					= $transaction->card_number;
		$refund->card_type   						= $transaction->card_type;
		$refund->card_cvv   						= $transaction->card_cvv;
		$refund->card_expiry   					= $transaction->card_expiry;
		$refund->card_holder_name   		= $transaction->card_holder_name;
		$refund->quantity   						= $refundOrderItemQuantity;
		$refund->price   								= $refundOrderItemPrice;
		$refund->rewards_refund   			= @$orderItem->used_item_reward_points;
		$refund->reason_for_refund   		= $refundOrderItemReason;
		$refund->save();

		return 'success';
	}
	else
	{
		Log::info('Payment not refunded. Please check Log');
		Log::info($orderItem);
		return 'failed';
	}
}

function strReplaceAssoc(array $replace, $subject) 
{ 
	return str_replace(array_keys($replace), array_values($replace), $subject);
}

function updateCommissions($amount, $is_on_offer, $discount_type, $discount_value, $vat_percentage, $userId, $type)
{
	$appsetting = AppSetting::select('is_enabled_cool_company','coolCompanyCommission','cool_company_social_fee_percentage','cool_company_salary_tax_percentage')->first();

	$ss_commission_percent = 0;
	$getPackageInfo = UserPackageSubscription::where('user_id', $userId)->where('module', $type)->orderBy('auto_id', 'DESC')->first();
	if($getPackageInfo)
	{
		$ss_commission_percent = $getPackageInfo->commission_per_sale;
	}

	$discounted_price = 0;
	$coolCompanyCommission = 0;
	$cc_commission_amount_all = 0;
	$cc_social_fee_percentage = 0;
	$cc_salary_tax_percentage = 0;
	$cc_commission_fee = 0;
	$for_social_fee_cal = 0;
	$cc_social_fee = 0;
	$for_gross_salary_cal = 0;
	$cc_salary_tax = 0;
	$net_salary = 0;
	$vat_amount = 0;
	$ss_commission_amount = 0;
	$price_with_all_com_vat = 0;

	$va = 0;
	$ssca = 0;
	$ccc = 0;
	$ccsfp = 0;
	$ccstp = 0;
	$cccf = 0;
	$fsfc = 0;
	$ccsf = 0;
	$fgsc = 0;
	$ccst = 0;
	$ns = 0;

	//for actual value calculation
	if(User::select('user_type_id')->find($userId)->user_type_id!=2)
	{
		$va = $amount * ($vat_percentage / 100);
	}

	$ssca = $amount * ($ss_commission_percent / 100);
	
	if($appsetting->is_enabled_cool_company && User::select('user_type_id')->find($userId)->user_type_id==2 && $type=='service')
	{
		$ccc = $appsetting->coolCompanyCommission;
		$ccsfp = $appsetting->cool_company_social_fee_percentage;
		$ccstp = $appsetting->cool_company_salary_tax_percentage;

		$cccf = $amount * ($ccc / 100);
		$fsfc = $amount - $cccf;
		$ccsf = $fsfc * ($ccsfp / 100);
		$fgsc = $amount - ($cccf + $ccsf);
		$ccst = $fgsc * ($ccstp / 100);
		$ns = $amount - ($cccf + $ccsf + $ccst);
	}
	$tcca = $cccf + $ccsf + $ccst;
	$price_with_all_com_vat = round(($tcca + $ssca + $amount + $va), 2);

	//For discount value
	if($is_on_offer==1)
	{
		if($discount_type==1)
		{
			$price = $amount - ($amount * $discount_value / 100);
		}
		else
		{
			$price = $amount - $discount_value;
		}
		$discounted_price = $price;
	}
	else
	{
		$price = $amount;
	}

	if(User::select('user_type_id')->find($userId)->user_type_id!=2)
	{
		$vat_amount = $price * ($vat_percentage / 100);
	}
	
	$ss_commission_amount = $price * ($ss_commission_percent / 100);

	if($appsetting->is_enabled_cool_company && User::select('user_type_id')->find($userId)->user_type_id==2 && $type=='service')
	{
		$coolCompanyCommission = $appsetting->coolCompanyCommission;
		$cc_social_fee_percentage = $appsetting->cool_company_social_fee_percentage;
		$cc_salary_tax_percentage = $appsetting->cool_company_salary_tax_percentage;

		$cc_commission_fee = $price * ($coolCompanyCommission / 100);
		$for_social_fee_cal = $price - $cc_commission_fee;
		$cc_social_fee = $for_social_fee_cal * ($cc_social_fee_percentage / 100);
		$for_gross_salary_cal = $price - ($cc_commission_fee + $cc_social_fee);
		$cc_salary_tax = $for_gross_salary_cal * ($cc_salary_tax_percentage / 100);
		$net_salary = $price - ($cc_commission_fee + $cc_social_fee + $cc_salary_tax);
	}
	$totalCCAmount = $cc_commission_fee + $cc_social_fee + $cc_salary_tax;
	$totalCCPercent = $coolCompanyCommission + $cc_social_fee_percentage + $cc_salary_tax_percentage;
	$return = [
			'cool_company_commission' => round($coolCompanyCommission, 2),
			'cc_social_fee_percentage' => round($cc_social_fee_percentage, 2),
			'cc_salary_tax_percentage' => round($cc_salary_tax_percentage, 2),
			'cc_commission_fee' => round($cc_commission_fee, 2),
			'for_social_fee_cal' => round($for_social_fee_cal, 2),
			'cc_social_fee' => round($cc_social_fee, 2),
			'for_gross_salary_cal' => round($for_gross_salary_cal, 2),
			'cc_salary_tax' => round($cc_salary_tax, 2),
			'net_salary' => round($net_salary, 2),
			'vat_amount' => round($vat_amount, 2),
			'ss_commission_percent' => round($ss_commission_percent, 2),
			'ss_commission_amount' => round($ss_commission_amount, 2),
			'discounted_price' => round($discounted_price, 2),
			'price' => round($price, 2),
			'price_with_all_com_vat' => round($price_with_all_com_vat, 2),
			'totalCCPercent' => round($totalCCPercent, 2),
			'totalCCAmount' => round($totalCCAmount, 2),
			'totalAmount' => round(($totalCCAmount + $ss_commission_amount + $price + $vat_amount), 2),
	];

	return $return;
}

function updatePrice($categoryID, $vat_percentage, $type)
{
	$items = ProductsServicesBook::where('category_master_id', $categoryID)->get();
	foreach ($items as $key => $item) {
		$getCommVal = updateCommissions($item->basic_price_wo_vat, $item->is_on_offer, $item->discount_type, $item->discount_value, $vat_percentage, $item->user_id, $item->type);

		//update Price
		$item->price = $getCommVal['totalAmount'];
		$item->discounted_price = $getCommVal['discounted_price'];
		$item->vat_percentage = $vat_percentage;
    $item->vat_amount = $getCommVal['vat_amount'];
    $item->ss_commission_percent = $getCommVal['ss_commission_percent'];
    $item->ss_commission_amount = $getCommVal['ss_commission_amount'];
    $item->cc_commission_percent_all = $getCommVal['totalCCPercent'];
    $item->cc_commission_amount_all = $getCommVal['totalCCAmount'];
    $item->save();
	}
	return true;
}