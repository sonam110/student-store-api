<?php

namespace App\Http\Controllers\API\Admin;


use App\Http\Controllers\Controller;
use App\Models\Abuse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use App\Models\ProductsServicesBook;
use App\Models\Contest;
use App\Models\Job;
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

        foreach ($abuse as $key => $value) {

            if(!empty($value->products_services_book_id))
            {
                $product = ProductsServicesBook::find($value->products_services_book_id);
                $module = 'Product';
                $body =  'Abuse status has been updated to  '.$request->status.' .';
            }
            elseif(!empty($value->contest_id))
            {
                $product = Contest::find($value->contest_id);
                $module = 'Contest';
                $body =  'Abuse status has been updated to  '.$request->status.' .';
            }
            else
            {
                $product = Job::find($value->job_id);
                $module = 'Job';
                $body =  'Abuse status has been updated to  '.$request->status.' .';
            }

            

            $title = 'Abuse Status updated';
            
                
            $type = 'Abuse';
            pushNotification($title,$body,$value->user,$type,true,'buyer',$module,$product->id,'Abuse-list');
            # code...
        }
        return response()->json(prepareResult(false, $abuse, getLangByLabelGroups('messages','message_abuse_updated')), config('http_response.success'));
    }

    
    public function destroy(Abuse $abuse)
    {
        $abuse->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_abuse_deleted')), config('http_response.success'));
    }
}