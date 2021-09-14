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
	$userDeviceInfo = UserDeviceInfo::where('user_id',$user->id)->latest()->first();

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
	$orderItem = OrderItem::find($refundOrderItemId);
	$orderId = $orderItem->order->id;
	$transaction = $orderItem->order->transaction;
	$stripe = new \Stripe\StripeClient(
	  env('STRIPE_SECRET')
	);
	$data = $stripe->refunds->create([
	  'charge' => $transaction->transaction_id,
	  'amount' => $refundOrderItemPrice * $refundOrderItemQuantity * 100,
	]);

	$refund = new Refund;
	$refund->order_id   				= $orderId;
	$refund->payment_card_detail_id   	= $transaction->payment_card_detail_id;
	$refund->order_item_id   			= $refundOrderItemId;
	$refund->transaction_id   			= $transaction->id;
	$refund->refund_id   				= $data->id;
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