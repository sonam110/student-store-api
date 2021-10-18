<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\UserEducationDetail;
use App\Http\Resources\UserEducationDetailResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use PDF;
use mervick\aesEverywhere\AES256;

class UserEducationDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        try
        {
            $userEducationDetails = UserEducationDetail::orderBy('created_at','DESC')->get();
            return response(prepareResult(false, UserEducationDetailResource::collection($userEducationDetails), getLangByLabelGroups('messages','message_education_detail_list')), config('http_response.success'));
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
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $from_date = date("Y-m-d", strtotime($request->from_date));
            $to_date = date("Y-m-d", strtotime($request->to_date));

            $userEducationDetail = new UserEducationDetail;
            $userEducationDetail->user_id                      	= Auth::id();
            $userEducationDetail->user_cv_detail_id             = $request->user_cv_detail_id;
            $userEducationDetail->title                         = $request->title;
            $userEducationDetail->description                   = $request->description;
            $userEducationDetail->ongoing              			= $request->ongoing;
            $userEducationDetail->from_date                     = $from_date;
            $userEducationDetail->to_date                       = $to_date;
            $userEducationDetail->is_from_sweden                = $request->is_from_sweden;
            $userEducationDetail->country                       = $request->country;
            $userEducationDetail->state                         = $request->state;
            $userEducationDetail->city                          = $request->city;
            $userEducationDetail->status                        = true;
            $userEducationDetail->save();
            DB::commit();

            $destinationPath = 'uploads/';
            $cv_name = Str::slug(substr(AES256::decrypt(Auth::user()->first_name, env('ENCRYPTION_KEY')), 0, 15)).'-'.Auth::user()->qr_code_number.'.pdf';

            createResume($cv_name,Auth::user());

            return response()->json(prepareResult(false, new UserEducationDetailResource($userEducationDetail), getLangByLabelGroups('messages','message_education_detail_created')), config('http_response.created'));
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
     * @param  \App\UserEducationDetail  $userEducationDetail
     * @return \Illuminate\Http\Response
     */
    public function show(UserEducationDetail $userEducationDetail)
    {
        return response()->json(prepareResult(false, new UserEducationDetailResource($userEducationDetail), getLangByLabelGroups('messages','message_education_detail_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UserEducationDetail  $userEducationDetail
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,UserEducationDetail $userEducationDetail)
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
            $from_date = date("Y-m-d", strtotime($request->from_date));
            $to_date = date("Y-m-d", strtotime($request->to_date));

            $userEducationDetail->user_id                      	= Auth::id();
            $userEducationDetail->user_cv_detail_id             = $request->user_cv_detail_id;
            $userEducationDetail->title                         = $request->title;
            $userEducationDetail->description                   = $request->description;
            $userEducationDetail->ongoing              			= $request->ongoing;
            $userEducationDetail->from_date                     = $from_date;
            $userEducationDetail->to_date                       = $to_date;
            $userEducationDetail->is_from_sweden                = $request->is_from_sweden;
            $userEducationDetail->country                       = $request->country;
            $userEducationDetail->state                         = $request->state;
            $userEducationDetail->city                          = $request->city;
            $userEducationDetail->status                        = true;
            $userEducationDetail->save();
            DB::commit();
            
            $destinationPath = 'uploads/';
            $cv_name = Str::slug(substr(AES256::decrypt(Auth::user()->first_name, env('ENCRYPTION_KEY')), 0, 15)).'-'.Auth::user()->qr_code_number.'.pdf';

            createResume($cv_name,Auth::user());

            return response()->json(prepareResult(false, new UserEducationDetailResource($userEducationDetail), getLangByLabelGroups('messages','message_education_detail_updated')), config('http_response.success'));
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
     * @param \App\UserEducationDetail $userEducationDetail
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(UserEducationDetail $userEducationDetail)
    {
        $userEducationDetail->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_education_detail_deleted')), config('http_response.success'));
    }
}