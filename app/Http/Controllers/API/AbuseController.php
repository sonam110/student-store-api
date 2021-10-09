<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\Abuse;
use App\Models\ProductsServicesBook;
use App\Models\Contest;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use App\Models\EmailTemplate;
use Mail;
use App\Mail\AbuseMail;

class AbuseController extends Controller
{

    public function store(Request $request)
    {        
        $validation = Validator::make($request->all(), [
            'reason_id_for_abuse'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $contactUs = new Abuse;
            $contactUs->user_id                        	= Auth::id();
            $contactUs->products_services_book_id       = !empty($request->products_services_book_id) ? $request->products_services_book_id : NULL;
            $contactUs->contest_id                      = !empty($request->contest_id) ? $request->contest_id : NULL;
            $contactUs->job_id                          = !empty($request->job_id) ? $request->job_id : NULL;
            $contactUs->reason_id_for_abuse             = $request->reason_id_for_abuse;
            $contactUs->reason_for_abuse                = $request->reason_for_abuse;
            $contactUs->status              			= 'pending';
            $contactUs->save();

            if(!empty($request->products_services_book_id))
            {
                $product = ProductsServicesBook::find($request->products_services_book_id);
                $module = 'Product';
                $body =  'An Abuse has been reported for the '.$product->type.' '.$product->title.' .';
            }
            elseif(!empty($request->contest_id))
            {
                $product = Contest::find($request->contest_id);
                $module = 'Contest';
                $body =  'An Abuse has been reported for the '.$product->type.' '.$product->title.' .';
            }
            else
            {
                $product = Job::find($request->job_id);
                $module = 'Job';
                $body =  'An Abuse has been reported for the job '.$product->title.' .';
            }

            

            $title = 'Abuse Reported';
            
                
            $type = 'Abuse';
            pushNotification($title,$body,$product->user,$type,true,'buyer',$module,$product->id,'Abuse-list');

            DB::commit();
            return response()->json(prepareResult(false, $contactUs, getLangByLabelGroups('messages','message_contact_us_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}