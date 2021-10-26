<?php 
use App\Models\User;
use App\Models\LabelGroup;
use Edujugon\PushNotification\PushNotification;
use App\Models\UserDeviceInfo;
use App\Models\Notification;
use Stripe as ST;

use App\Models\OrderItem;
use App\Models\TransactionDetail;
use App\Models\Refund;
use App\Models\PaymentGatewaySetting;
use Log;

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
	$userDeviceInfo = UserDeviceInfo::where('user_id',$user->id)->orderBy('created_at', 'DESC')->first();
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
		$userDeviceInfo = UserDeviceInfo::where('user_id',$user->id)->latest()->first();
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


function refund($refundOrderItemId,$refundOrderItemPrice,$refundOrderItemQuantity,$refundOrderItemReason)
{
	$isRefunded = false;
	$orderItem = OrderItem::find($refundOrderItemId);
	$orderId = $orderItem->order->id;
	$transaction = $orderItem->order->transaction;
	if($transaction->gateway_detail=='stripe' && $transaction->transaction_status=='succeeded')
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
			"type" 				=> $orderItem->product_type,
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
        Log::info($postData);

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
            $info = curl_errno($curl)>0 ? array("curl_error_".curl_errno($curl)=>curl_error($curl)) : curl_getinfo($curl);
            Log::info('Payment not refunded. Please check Curl Log');
            Log::info($info);
        }
        else
        {
        	$jsonRes = json_decode($response, true);
        	Log::info($jsonRes);
        	$refund_id = $jsonRes['refund_id'];
        }
        curl_close($curl);
	}
	elseif($transaction->gateway_detail=='swish')
	{
		$isRefunded = true;
	}
	
	if($isRefunded)
	{
		$refund = new Refund;
		$refund->order_id   				= $orderId;
		$refund->payment_card_detail_id   	= $transaction->payment_card_detail_id;
		$refund->order_item_id   			= $refundOrderItemId;
		$refund->transaction_id   			= $transaction->id;
		$refund->refund_id   				= $refund_id;

		if($transaction->gateway_detail=='stripe')
		{
			// $refund->object   					= $data->object;
			$refund->amount   					= $data->amount / 100;
			$refund->balance_transaction   		= $data->balance_transaction;
			$refund->charge   					= $data->charge;
			$refund->created   					= $data->created;
			$refund->currency   				= $data->currency;
			$refund->metadata   				= $data->metadata;
			$refund->payment_intent   			= $data->payment_intent;
			$refund->reason   					= $data->reason;
			$refund->receipt_number   			= $data->receipt_number;
			$refund->source_transfer_reversal   = $data->source_transfer_reversal;
			$refund->status   					= $data->status;
			$refund->transfer_reversal   		= $data->transfer_reversal;
		}
		
		$refund->gateway_detail   			= $transaction->gateway_detail;
		$refund->transaction_type   		= $transaction->transaction_type;
		$refund->transaction_mode   		= $transaction->transaction_mode;
		$refund->card_number   				= $transaction->card_number;
		$refund->card_type   				= $transaction->card_type;
		$refund->card_cvv   				= $transaction->card_cvv;
		$refund->card_expiry   				= $transaction->card_expiry;
		$refund->card_holder_name   		= $transaction->card_holder_name;
		$refund->quantity   				= $refundOrderItemQuantity;
		$refund->price   					= $refundOrderItemPrice;
		$refund->reason_for_refund   		= $refundOrderItemReason;
		$refund->save();
	}
	else
	{
		Log::info('Payment not refunded. Please check Log');
		Log::info($refundOrderItemId,$refundOrderItemPrice,$refundOrderItemQuantity,$refundOrderItemReason);
	}
}

function strReplaceAssoc(array $replace, $subject) 
{ 
	return str_replace(array_keys($replace), array_values($replace), $subject);
}