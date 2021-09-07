<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use Log;

class NotificationController extends Controller
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
            $notifications = Notification::where('user_id',Auth::id())->orderBy('created_at','DESC');
            if($request->read_status)
            {
                $notifications = $notifications->where('read_status',$request->read_status);
            }
            if(!empty($request->per_page_record))
            {
                $notifications = $notifications->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $notifications = $notifications->get();
            }
            
            if($request->mark_all_as_read == 'true' || $request->mark_all_as_read == 1)
            {
                Notification::where('user_id',Auth::id())->update(['read_status' => '1']);
            }
            
            return response(prepareResult(false, NotificationResource::collection($notifications), getLangByLabelGroups('messages','message_notification_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function store(Request $request)
    {        
        $validation = Validator::make($request->all(), [
            'message'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $notification = new Notification;
            $notification->user_id              = 3;
            $notification->sender_id            = Auth::id();
            $notification->device_uuid          = $request->device_uuid;
            $notification->device_platform      = $request->device_platform;
            $notification->type                 = $request->type;
            $notification->title                = $request->title;
            $notification->sub_title            = $request->sub_title;
            $notification->message              = $request->message;
            $notification->image_url            = $request->image_url;
            $notification->read_status          = false;
            $notification->save();
            DB::commit();
            return response()->json(prepareResult(false, new NotificationResource($notification), getLangByLabelGroups('messages','message_notification_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function show(Notification $notification)
    {
        return response()->json(prepareResult(false, new NotificationResource($notification), getLangByLabelGroups('messages','message_notification_list')), config('http_response.success'));
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_notification_deleted')), config('http_response.success'));
    }

    public function read($id)
    {
        try
        {
            $notification = Notification::find($id);
            $notification->update(['read_status' => true]);
            return response()->json(prepareResult(false, new NotificationResource($notification), getLangByLabelGroups('notification_status','notification_status_updated')), config('http_response.success'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function userNotificationDelete()
    {
        try
        {
            Notification::where('user_id',Auth::id())->delete();
            return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_notification_deleted')), config('http_response.success'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}
