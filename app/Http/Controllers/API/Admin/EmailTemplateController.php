<?php

namespace App\Http\Controllers\API\Admin;


use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class EmailTemplateController extends Controller
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
                $emailTemplates = EmailTemplate::simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $emailTemplates = EmailTemplate::get();
            }
            return response(prepareResult(false, $emailTemplates, getLangByLabelGroups('messages','message_email_template_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {        
        $validation = Validator::make($request->all(), [
            'subject'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $emailTemplate = new EmailTemplate;
            $emailTemplate->language_id         = $request->language_id;
            $emailTemplate->template_for        = $request->template_for;
            $emailTemplate->to    				= $request->to;
			$emailTemplate->cc    				= $request->cc;
			$emailTemplate->bcc    				= $request->bcc;
			$emailTemplate->from    			= $request->from;
			$emailTemplate->subject    			= $request->subject;
			$emailTemplate->body    			= $request->body;
			$emailTemplate->attachment_path    	= $request->attachment_path;
			$emailTemplate->status    			= $request->status;
            $emailTemplate->save();
            DB::commit();
            return response()->json(prepareResult(false, $emailTemplate, getLangByLabelGroups('messages','message_email_template_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
        	DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(EmailTemplate $emailTemplate)
    {
        return response()->json(prepareResult(false, $emailTemplate, getLangByLabelGroups('messages','message_email_template_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\EmailTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,EmailTemplate $emailTemplate)
    {
        $validation = Validator::make($request->all(), [
            'subject' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $emailTemplate->language_id         = $request->language_id;
            $emailTemplate->template_for                  = $request->template_for;
			$emailTemplate->to    				= $request->to;
			$emailTemplate->cc    				= $request->cc;
			$emailTemplate->bcc    				= $request->bcc;
			$emailTemplate->from    			= $request->from;
			$emailTemplate->subject    			= $request->subject;
			$emailTemplate->body    			= $request->body;
			$emailTemplate->attachment_path    	= $request->attachment_path;
			$emailTemplate->status    			= $request->status;
            $emailTemplate->save();
            DB::commit();
            return response()->json(prepareResult(false, $emailTemplate, getLangByLabelGroups('messages','message_email_template_updated')), config('http_response.success'));
        }
        catch (\Throwable $exception)
        {
        	DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\EmailTemplate $emailTemplate
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_email_template_deleted')), config('http_response.success'));
    }
}