<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\Abuse;
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
            $contactUs->products_services_book_id       = $request->products_services_book_id;
            $contactUs->contest_id                      = $request->contest_id;
            $contactUs->job_id                          = $request->job_id;
            $contactUs->reason_id_for_abuse             = $request->reason_id_for_abuse;
            $contactUs->reason_for_abuse                = $request->reason_for_abuse;
            $contactUs->status              			= $request->status;
            $contactUs->save();

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