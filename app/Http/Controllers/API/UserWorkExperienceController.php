<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\UserWorkExperience;
use App\Http\Resources\UserWorkExperienceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use PDF;
use mervick\aesEverywhere\AES256;

class UserWorkExperienceController extends Controller
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
            $userWorkExperiences = UserWorkExperience::orderBy('created_at','DESC')->get();
            return response(prepareResult(false, UserWorkExperienceResource::collection($userWorkExperiences), getLangByLabelGroups('messages','message_work_experience_list')), config('http_response.success'));
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

            $userWorkExperience = new UserWorkExperience;
            $userWorkExperience->user_id                        = Auth::id();
            $userWorkExperience->user_cv_detail_id              = $request->user_cv_detail_id;
            $userWorkExperience->title                          = $request->title;
            $userWorkExperience->employer_name                  = $request->employer_name;
            $userWorkExperience->activities_and_responsibilities= $request->activities_and_responsibilities;
            $userWorkExperience->currently_working              = $request->currently_working;
            $userWorkExperience->from_date                      = $from_date;
            $userWorkExperience->to_date                        = $to_date;
            $userWorkExperience->is_from_sweden                 = $request->is_from_sweden;
            $userWorkExperience->country                        = $request->country;
            $userWorkExperience->state                          = $request->state;
            $userWorkExperience->city                           = $request->city;
            $userWorkExperience->status                         = true;
            $userWorkExperience->save();


            DB::commit();

            $destinationPath = 'uploads/';
            $cv_name = Str::slug(substr(AES256::decrypt(Auth::user()->first_name, env('ENCRYPTION_KEY')), 0, 15)).'-'.Auth::user()->qr_code_number.'.pdf';

            createResume($cv_name,Auth::user());
            
            return response()->json(prepareResult(false, new UserWorkExperienceResource($userWorkExperience), getLangByLabelGroups('messages','message_work_experience_created')), config('http_response.created'));
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
     * @param  \App\UserWorkExperience  $userWorkExperience
     * @return \Illuminate\Http\Response
     */
    public function show(UserWorkExperience $userWorkExperience)
    {
        return response()->json(prepareResult(false, new UserWorkExperienceResource($userWorkExperience), getLangByLabelGroups('messages','message_work_experience_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UserWorkExperience  $userWorkExperience
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,UserWorkExperience $userWorkExperience)
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

            $userWorkExperience->user_id                        = Auth::id();
            $userWorkExperience->user_cv_detail_id              = $request->user_cv_detail_id;
            $userWorkExperience->title                          = $request->title;
            $userWorkExperience->employer_name                  = $request->employer_name;
            $userWorkExperience->activities_and_responsibilities= $request->activities_and_responsibilities;
            $userWorkExperience->currently_working              = $request->currently_working;
            $userWorkExperience->from_date                      = $from_date;
            $userWorkExperience->to_date                        = $to_date;
            $userWorkExperience->is_from_sweden                 = $request->is_from_sweden;
            $userWorkExperience->country                        = $request->country;
            $userWorkExperience->state                          = $request->state;
            $userWorkExperience->city                           = $request->city;
            $userWorkExperience->status                         = true;
            $userWorkExperience->save();

            DB::commit();

            $destinationPath = 'uploads/';
            $cv_name = Str::slug(substr(AES256::decrypt(Auth::user()->first_name, env('ENCRYPTION_KEY')), 0, 15)).'-'.Auth::user()->qr_code_number.'.pdf';

            createResume($cv_name,Auth::user());

            return response()->json(prepareResult(false, new UserWorkExperienceResource($userWorkExperience), getLangByLabelGroups('messages','message_work_experience_updated')), config('http_response.success'));
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
     * @param \App\UserWorkExperience $userWorkExperience
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(UserWorkExperience $userWorkExperience)
    {
        $userWorkExperience->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_work_experience_deleted')), config('http_response.success'));
    }
}