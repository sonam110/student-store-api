<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use App\Models\EmailTemplate;
use Mail;
use App\Mail\ContactUsMail;

class ContactUsController extends Controller
{

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
            $contactUs = new ContactUs;
            $contactUs->user_id                        	= Auth::id();
            $contactUs->title                          	= $request->title;
            $contactUs->message                  		= $request->message;
            $contactUs->files       					= json_encode($request->images);
            $contactUs->email              				= $request->email;
            $contactUs->status                         	= true;
            $contactUs->save();

            $emailTemplate = EmailTemplate::where('template_for','contact-us')->first();
            $details = [
                'title' => $emailTemplate->subject,
                'body' => $emailTemplate->body
                // 'url' => url('/password/reset/'.$token.'?email='.$myEmail)
            ];
            
            Mail::to($request->email)->send(new ContactUsMail($details));


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