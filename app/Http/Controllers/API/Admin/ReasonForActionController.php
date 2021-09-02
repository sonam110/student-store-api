<?php

namespace App\Http\Controllers\API\Admin;


use App\Http\Controllers\Controller;
use App\Models\ReasonForAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class ReasonForActionController extends Controller
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
                $reasonForActions = ReasonForAction::simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $reasonForActions = ReasonForAction::get();
            }
            return response(prepareResult(false, $reasonForActions, getLangByLabelGroups('messages','message_reason_for_action_list')), config('http_response.success'));
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
            'reason_for_action'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $reasonForAction = new ReasonForAction;
            $reasonForAction->language_id         	= $request->language_id;
            $reasonForAction->module_type_id        = $request->module_type_id;
            $reasonForAction->action        		= $request->action;
			$reasonForAction->reason_for_action 	= $request->reason_for_action;
			$reasonForAction->status    			= $request->status;
            $reasonForAction->text_field_enabled    = $request->text_field_enabled;
            $reasonForAction->save();
            DB::commit();
            return response()->json(prepareResult(false, $reasonForAction, getLangByLabelGroups('messages','message_reason_for_action_created')), config('http_response.created'));
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
     * @param  \App\ReasonForAction  $reasonForAction
     * @return \Illuminate\Http\Response
     */
    public function show(ReasonForAction $reasonForAction)
    {
        return response()->json(prepareResult(false, $reasonForAction, getLangByLabelGroups('messages','message_reason_for_action_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ReasonForAction  $reasonForAction
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,ReasonForAction $reasonForAction)
    {
        $validation = Validator::make($request->all(), [
            'reason_for_action' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $reasonForAction->language_id         	= $request->language_id;
            $reasonForAction->module_type_id           = $request->module_type_id;
            $reasonForAction->action        	    = $request->action;
			$reasonForAction->reason_for_action    	= $request->reason_for_action;
			$reasonForAction->status    			= $request->status;
            $reasonForAction->text_field_enabled    = $request->text_field_enabled;
            $reasonForAction->save();
            DB::commit();
            return response()->json(prepareResult(false, $reasonForAction, getLangByLabelGroups('messages','message_reason_for_action_updated')), config('http_response.success'));
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
     * @param \App\ReasonForAction $reasonForAction
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(ReasonForAction $reasonForAction)
    {
        $reasonForAction->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_reason_for_action_deleted')), config('http_response.success'));
    }
}