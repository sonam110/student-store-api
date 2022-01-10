<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;

class SubscriberController extends Controller
{

    public function store(Request $request)
    {        
        $validation = Validator::make($request->all(), [
            'email'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        if(Subscriber::where('email', $request->email)->count()>0)
        {
            return response()->json(prepareResult(true, 'Already resgistered', getLangByLabelGroups('messages','alreadyAdded')), config('http_response.internal_server_error'));
        }

        DB::beginTransaction();
        try
        {
            $subscriber = new Subscriber;
            $subscriber->email                          	= $request->email;
            $subscriber->ip_address                  	= $request->ip();
            $subscriber->save();

            DB::commit();
            return response()->json(prepareResult(false, $subscriber, getLangByLabelGroups('messages','message_subscriber_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}