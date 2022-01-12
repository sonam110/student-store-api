<?php

namespace App\Http\Controllers\API\Admin;


use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class SmsTemplateController extends Controller
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
                $smsTemplates = SmsTemplate::with('language')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $smsTemplates = SmsTemplate::with('language')->get();
            }
            return response(prepareResult(false, $smsTemplates, getLangByLabelGroups('messages','message_sms_template_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
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
            'message'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $smsTemplate = new SmsTemplate;
            $smsTemplate->language_id         	= $request->language_id;
            $smsTemplate->template_for        	= $request->template_for;
			$smsTemplate->message    	   		= $request->message;
			$smsTemplate->status    			= $request->status;
            $smsTemplate->save();
            DB::commit();
            return response()->json(prepareResult(false, $smsTemplate, getLangByLabelGroups('messages','message_sms_template_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
        	DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SmsTemplate  $smsTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(SmsTemplate $smsTemplate)
    {
        $smsTemplate['language'] = $smsTemplate->language;
        return response()->json(prepareResult(false, $smsTemplate, getLangByLabelGroups('messages','message_sms_template_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SmsTemplate  $smsTemplate
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,SmsTemplate $smsTemplate)
    {
        $validation = Validator::make($request->all(), [
            'message' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $smsTemplate->language_id         	= $request->language_id;
            $smsTemplate->template_for        	= $request->template_for;
			$smsTemplate->message    			= $request->message;
			$smsTemplate->status    			= $request->status;
            $smsTemplate->save();
            DB::commit();
            return response()->json(prepareResult(false, $smsTemplate, getLangByLabelGroups('messages','message_sms_template_updated')), config('http_response.success'));
        }
        catch (\Throwable $exception)
        {
        	DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\SmsTemplate $smsTemplate
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(SmsTemplate $smsTemplate)
    {
        $smsTemplate->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_sms_template_deleted')), config('http_response.success'));
    }
}