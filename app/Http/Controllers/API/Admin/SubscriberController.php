<?php

namespace App\Http\Controllers\API\Admin;


use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class SubscriberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
    	try
    	{
            $subscriber = Subscriber::select('*');
            if(!empty($request->email))
            {
                $subscriber->where('email', 'LIKE', '%'.$request->email.'%');
            }

    		if(!empty($request->per_page_record))
    		{
    			$subscribers = $subscriber->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
    		}
    		else
    		{
    			$subscribers = $subscriber->get();
    		}
    		return response(prepareResult(false, $subscribers, getLangByLabelGroups('messages','message_subscriber_list')), config('http_response.success'));
    	}
    	catch (\Throwable $exception) 
    	{
    		\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }

    

    public function destroy(Subscriber $subscriber)
    {
    	$subscriber->delete();
    	return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_subscriber_deleted')), config('http_response.success'));
    }
}