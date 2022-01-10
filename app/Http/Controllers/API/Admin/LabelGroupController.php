<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\LabelGroup;
use App\Http\Resources\LabelGroupResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class LabelGroupController extends Controller
{
    public function index(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $labelGroups = LabelGroup::simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $labelGroups = LabelGroup::get();
            }
            return response(prepareResult(false, $labelGroups, getLangByLabelGroups('messages','message_label_group_list')), config('http_response.success'));
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
            'name'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $labelGroup = new LabelGroup;
            $labelGroup->name                = $request->name;
            $labelGroup->status              = $request->status;
            $labelGroup->save();
            DB::commit();
            return response()->json(prepareResult(false, new LabelGroupResource($labelGroup), getLangByLabelGroups('messages','message_label_group_created')), config('http_response.created'));
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
     * @param  \App\LabelGroup  $labelGroup
     * @return \Illuminate\Http\Response
     */
    public function show(LabelGroup $labelGroup)
    {
        return response()->json(prepareResult(false, new LabelGroupResource($labelGroup), getLangByLabelGroups('messages','message_label_group_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LabelGroup  $labelGroup
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,LabelGroup $labelGroup)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $labelGroup->name                = $request->name;
            $labelGroup->status               = $request->status;
            $labelGroup->save();
            DB::commit();
            return response()->json(prepareResult(false, new LabelGroupResource($labelGroup), getLangByLabelGroups('messages','message_label_group_updated')), config('http_response.success'));
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
     * @param \App\LabelGroup $labelGroup
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(LabelGroup $labelGroup)
    {
        $labelGroup->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_label_group_deleted')), config('http_response.success'));
    }
}
