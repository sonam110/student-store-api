<?php

namespace App\Http\Controllers\API\Admin;


use App\Http\Controllers\Controller;
use App\Models\Abuse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class AbuseController extends Controller
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
            if(!empty($request->per_page_record))
            {
                $abuses = Abuse::with('product','contest','job','user')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $abuses = Abuse::with('product','contest','job','user')->get();
            }
            return response(prepareResult(false, $abuses, getLangByLabelGroups('messages','message_abuse_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    
    public function show(Abuse $abuse)
    {
        $abuse = Abuse::with('product','contest','job','user')->find($abuse->id);
        return response()->json(prepareResult(false, $abuse, getLangByLabelGroups('messages','message_abuse_list')), config('http_response.success'));
    }

    public function statusUpdate(Request $request,$id)
    {
        $abuse = Abuse::find($id)->update(['status'=>$request->status]);
        $abuse = Abuse::with('product','contest','job','user')->find($id);
        return response()->json(prepareResult(false, $abuse, getLangByLabelGroups('messages','message_abuse_updated')), config('http_response.success'));
    }

    public function multipleStatusUpdate(Request $request)
    {
        $abuse = Abuse::whereIn('id',$request->abuse_id)->update(['status'=>$request->status]);
        $abuse = Abuse::whereIn('id',$request->abuse_id)->with('product','contest','job','user')->get();
        return response()->json(prepareResult(false, $abuse, getLangByLabelGroups('messages','message_abuse_updated')), config('http_response.success'));
    }

    
    public function destroy(Abuse $abuse)
    {
        $abuse->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_abuse_deleted')), config('http_response.success'));
    }
}