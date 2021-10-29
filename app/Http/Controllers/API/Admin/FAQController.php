<?php

namespace App\Http\Controllers\API\Admin;


use App\Http\Controllers\Controller;
use App\Models\FAQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class FAQController extends Controller
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
                $faqs = FAQ::with('language:id,title')->orderBy('auto_id','ASC')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $faqs = FAQ::with('language:id,title')->orderBy('auto_id','ASC')->get();
            }
            return response(prepareResult(false, $faqs, getLangByLabelGroups('messages','message_faq_list')), config('http_response.success'));
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
            'question'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $faq = new FAQ;
            $faq->language_id          	= $request->language_id;
            $faq->module_type_id   		= $request->module_type_id;
            $faq->question             	= $request->question;
            $faq->answer               	= $request->answer;
            $faq->save();
            DB::commit();
            return response()->json(prepareResult(false, $faq, getLangByLabelGroups('messages','message_faq_created')), config('http_response.created'));
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
     * @param  \App\FAQ  $faq
     * @return \Illuminate\Http\Response
     */
    public function show(FAQ $faq)
    {
        return response()->json(prepareResult(false, $faq, getLangByLabelGroups('messages','message_faq_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FAQ  $faq
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,FAQ $faq)
    {
        $validation = Validator::make($request->all(), [
            'question' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $faq->language_id          	= $request->language_id;
            $faq->module_type_id   		= $request->module_type_id;
            $faq->question             	= $request->question;
            $faq->answer               	= $request->answer;
            $faq->save();
            DB::commit();
            return response()->json(prepareResult(false, $faq, getLangByLabelGroups('messages','message_faq_updated')), config('http_response.success'));
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
     * @param \App\FAQ $faq
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(FAQ $faq)
    {
        $faq->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_faq_deleted')), config('http_response.success'));
    }
}