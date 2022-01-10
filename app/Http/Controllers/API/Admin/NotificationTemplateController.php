<?php

namespace App\Http\Controllers\API\Admin;


use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class NotificationTemplateController extends Controller
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
                $emailTemplates = NotificationTemplate::simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $emailTemplates = NotificationTemplate::get();
            }
            return response(prepareResult(false, $emailTemplates, getLangByLabelGroups('messages','message_email_template_list')), config('http_response.success'));
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
            'title'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $emailTemplate = new NotificationTemplate;
            $emailTemplate->language_id         = $request->language_id;
            $emailTemplate->template_for        = $request->template_for;
			$emailTemplate->title    			= $request->title;
			$emailTemplate->body    			= $request->body;
            $emailTemplate->attributes          = implode(',', $request->attributes);
			$emailTemplate->status    			= $request->status;
            $emailTemplate->save();
            DB::commit();
            return response()->json(prepareResult(false, $emailTemplate, getLangByLabelGroups('messages','message_email_template_created')), config('http_response.created'));
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
     * @param  \App\NotificationTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(NotificationTemplate $emailTemplate)
    {
        return response()->json(prepareResult(false, $emailTemplate, getLangByLabelGroups('messages','message_email_template_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\NotificationTemplate  $emailTemplate
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,NotificationTemplate $emailTemplate)
    {
        $validation = Validator::make($request->all(), [
            'title' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $emailTemplate->language_id         = $request->language_id;
            $emailTemplate->template_for        = $request->template_for;
			$emailTemplate->from    			= $request->from;
			$emailTemplate->title    			= $request->title;
			$emailTemplate->body    			= $request->body;
            $emailTemplate->attributes          = implode(',', $request->attributes);
			$emailTemplate->status    			= $request->status;
            $emailTemplate->save();
            DB::commit();
            return response()->json(prepareResult(false, $emailTemplate, getLangByLabelGroups('messages','message_email_template_updated')), config('http_response.success'));
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
     * @param \App\NotificationTemplate $emailTemplate
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(NotificationTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_email_template_deleted')), config('http_response.success'));
    }
}