<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class MessageController extends Controller
{
	public function messages(Request $request)
	{
		try
		{
			$messages = Message::where('id','!=',null)->orderBy('created_at','DESC');
			if($request->status)
			{
				$messages = Message::where('status',$request->status);
			}
			if(!empty($request->per_page_record))
			{
			    $messages = $messages->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
			    $messages = $messages->get();
			}
			return response(prepareResult(false, $messages, getLangByLabelGroups('messages','message_message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

}
