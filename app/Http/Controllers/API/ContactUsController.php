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
use mervick\aesEverywhere\AES256;
use App\Mail\ContactUsMail;

class ContactUsController extends Controller
{

    public function store(Request $request)
    {        
        $validation = Validator::make($request->all(), [
            'message'  => 'required',
            'email'  => 'required',
            'message_for' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $contactUs = new ContactUs;
            $contactUs->reason_id                       = $request->reason_id;
            $contactUs->message_for                     = $request->message_for;
            $contactUs->name                            = $request->name;
            $contactUs->phone                           = $request->phone;
            $contactUs->message                  		= $request->message;
            $contactUs->email              				= $request->email;
            $contactUs->images                          = $request->images ? json_encode($request->images) : Null;
            $contactUs->save();

            $emailTemplate = EmailTemplate::where('template_for','contact-us')->where('language_id',env('APP_DEFAULT_LANGUAGE'))->first();
            if(empty($emailTemplate))
            {
                $emailTemplate = EmailTemplate::where('template_for','contact-us')->first();
            }

            $body = $emailTemplate->body;

            $arrayVal = [
                '{{user_name}}' => $request->name,
            ];
            $body = strReplaceAssoc($arrayVal, $body);
            
            $details = [
                'title' => $emailTemplate->subject,
                'body' => $body,
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