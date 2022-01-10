<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
	public function sendNotification(Request $request)
	{
		try
		{
			$title = $request->title;
			$body =  $request->body;
			$users = User::whereIn('id',$request->users_id)->get();
			$type = 'Admin Sent Notification';
			$user_type = 'user';
			$module = 'Admin';
			pushMultipleNotification($title,$body,$users,$type,true,$user_type,$module,'','notification');
			return response(prepareResult(false, [], getLangByLabelGroups('messages','message_notification_sent')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

}
