<?php

namespace App\Http\Controllers\API\Admin;


use App\Http\Controllers\Controller;
use App\Models\ReasonForAction;
use App\Models\ReasonForActionDetail;
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
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function store(Request $request)
    {
        foreach ($request->reason_for_actions as $key => $value) 
        {
            if($value['language_id']=='1')
            {
                if(ReasonForAction::where('module_type_id', $request->module_type_id)->where('action', $request->action)->where('reason_for_action', $value['reason_for_action'])->count() > 0)
                {
                    $reasonForAction = ReasonForAction::where('module_type_id', $request->module_type_id)->where('action', $request->action)->where('reason_for_action', $value['reason_for_action'])->first();
                }
                else
                {
                    $reasonForAction = new ReasonForAction;
                }

                $reasonForAction->module_type_id        = $request->module_type_id;
                $reasonForAction->action                = $request->action;
                $reasonForAction->reason_for_action     = $value['reason_for_action'];
                $reasonForAction->status                = 1;
                $reasonForAction->text_field_enabled    = $request->text_field_enabled;
                $reasonForAction->save();
                break;
            }
        }

        foreach ($request->reason_for_actions as $key => $value) 
        {
            if(ReasonForActionDetail::where('language_id', $value['language_id'])->where('reason_for_action_id', $reasonForAction->id)->where('slug', Str::slug($value['reason_for_action']))->count() > 0)
            {
                $reasonForActionDetail = ReasonForActionDetail::where('language_id', $value['language_id'])->where('reason_for_action_id', $reasonForAction->id)->where('slug', Str::slug($value['reason_for_action']))->first();
            }
            else
            {
                $reasonForActionDetail = new ReasonForActionDetail;
            }

            $reasonForActionDetail->reason_for_action_id    = $reasonForAction->id;
            $reasonForActionDetail->language_id     = $value['language_id'];
            $reasonForActionDetail->title   = $value['reason_for_action'];
            $reasonForActionDetail->slug     = Str::slug($value['reason_for_action']);
            $reasonForActionDetail->save();
        }
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_reason_for_action_created')), config('http_response.created'));
    }

    
    public function show(ReasonForAction $reasonForAction)
    {
        $reasonForActionReturn = ReasonForAction::with('reasonForActionDetails')->find($reasonForAction->id);
        return response()->json(prepareResult(false, $reasonForActionReturn, getLangByLabelGroups('messages','message_reason_for_action_list')), config('http_response.success'));
    }

    
    public function update(Request $request,ReasonForAction $reasonForAction)
    {
        foreach ($request->reason_for_actions as $key => $value) 
        {
            if($value['language_id']=='1')
            {
                $reasonForAction = ReasonForAction::find($request->reason_for_action_id);

                $reasonForAction->reason_for_action     = $value['reason_for_action'];
                $reasonForAction->text_field_enabled    = $request->text_field_enabled;
                $reasonForAction->save();
                break;
            }
        }

        foreach ($request->reason_for_actions as $key => $value) 
        {
            if(!empty($value['id']))
            {
                $reasonForActionDetail = ReasonForActionDetail::find($value['id']);
            }
            else
            {
                $reasonForActionDetail = new ReasonForActionDetail;
            }

            $reasonForActionDetail->reason_for_action_id    = $reasonForAction->id;
            $reasonForActionDetail->language_id     = $value['language_id'];
            $reasonForActionDetail->title   = $value['reason_for_action'];
            $reasonForActionDetail->slug     = Str::slug($value['reason_for_action']);
            $reasonForActionDetail->save();
        }

        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_reason_for_action_created')), config('http_response.created'));
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