<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserType;
use App\Http\Resources\UserTypeResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class UserTypeController extends Controller
{
    public function index(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $userTypes = UserType::simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $userTypes = UserType::get();
            }
            return response(prepareResult(false, UserTypeResource::collection($userTypes), getLangByLabelGroups('messages','message__user_type_list')), config('http_response.success'));
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
            'title'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $userType = new UserType;
            $userType->title                = $request->title;
            $userType->description          = $request->description;
            $userType->save();
            DB::commit();
            return response()->json(prepareResult(false, new UserTypeResource($userType), getLangByLabelGroups('messages','message_user_type_created')), config('http_response.created'));
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
     * @param  \App\UserType  $userType
     * @return \Illuminate\Http\Response
     */
    public function show(UserType $userType)
    {
        return response()->json(prepareResult(false, new UserTypeResource($userType), getLangByLabelGroups('messages','message_user_type_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UserType  $userType
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,UserType $userType)
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
            $userType->title                = $request->title;
            $userType->description          = $request->description;
            $userType->save();
            DB::commit();
            return response()->json(prepareResult(false, new UserTypeResource($userType), getLangByLabelGroups('messages','message_user_type_updated')), config('http_response.success'));
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
     * @param \App\UserType $userType
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(UserType $userType)
    {
        $userType->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_user_type_deleted')), config('http_response.success'));
    }
}
