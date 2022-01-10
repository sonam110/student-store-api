<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModuleType;
use App\Http\Resources\ModuleTypeResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class ModuleTypeController extends Controller
{
    public function index(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $moduleTypes = ModuleType::simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $moduleTypes = ModuleType::get();
            }
            return response(prepareResult(false, ModuleTypeResource::collection($moduleTypes), getLangByLabelGroups('messages','message__module_type_list')), config('http_response.success'));
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
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $moduleType = new ModuleType;
            $moduleType->title            	= $request->title;
            $moduleType->slug 				= Str::slug($request->title);
            $moduleType->description        = $request->description;
            $moduleType->status    			= $request->status;
            $moduleType->save();
            DB::commit();
            return response()->json(prepareResult(false, new ModuleTypeResource($moduleType), getLangByLabelGroups('messages','message_module_type_created')), config('http_response.created'));
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
     * @param  \App\ModuleType  $moduleType
     * @return \Illuminate\Http\Response
     */
    public function show(ModuleType $moduleType)
    {
        return response()->json(prepareResult(false, new ModuleTypeResource($moduleType), getLangByLabelGroups('messages','message_module_type_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ModuleType  $moduleType
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,ModuleType $moduleType)
    {
        $validation = Validator::make($request->all(), [
            'title' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $moduleType->title            	= $request->title;
            $moduleType->slug 				= Str::slug($request->title);
            $moduleType->description        = $request->description;
            $moduleType->status    			= $request->status;
            $moduleType->save();
            DB::commit();
            return response()->json(prepareResult(false, new ModuleTypeResource($moduleType), getLangByLabelGroups('messages','message_module_type_updated')), config('http_response.success'));
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
     * @param \App\ModuleType $moduleType
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(ModuleType $moduleType)
    {
        $moduleType->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_module_type_deleted')), config('http_response.success'));
    }
}
