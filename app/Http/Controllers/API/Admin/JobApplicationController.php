<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Http\Resources\JobApplicationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class JobApplicationController extends Controller
{
	public function index(Request $request)
	{
		try
		{
			if(!empty($request->per_page_record))
			{
			    $jobApplications = JobApplication::orderBy('created_at','DESC')->with('user','job')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
			    $jobApplications = JobApplication::orderBy('created_at','DESC')->with('user','job')->get();
			}
			return response(prepareResult(false, $jobApplications, getLangByLabelGroups('messages','message_job_application_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}


	public function show(JobApplication $jobApplication)
	{		
		return response()->json(prepareResult(false, new JobApplicationResource($jobApplication), getLangByLabelGroups('messages','message_job_application_list')), config('http_response.success'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\JobApplication  $jobApplication
	 * @return \Illuminate\Http\Response
	 */
	

   
	public function destroy(JobApplication $jobApplication)
	{
		$jobApplication->delete();
		return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_job_application_deleted')), config('http_response.success'));
	}

	public function applicantFilter(Request $request)
    {
        try
        {
        	$applicants = JobApplication::select('job_applications.*')
        	        ->join('users', function ($join) {
        	            $join->on('job_applications.user_id', '=', 'users.id');
        	        })
                    ->join('sp_jobs', function ($join) {
                        $join->on('job_applications.job_id', '=', 'sp_jobs.id');
                    })
        	        ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path,status','user.cvDetail','user.defaultAddress','job.jobTags');



            if(!empty($request->first_name))
            {
                $applicants->where('users.first_name', 'LIKE', '%'.$request->first_name.'%');
            }
            if(!empty($request->last_name))
            {
                $applicants->where('users.last_name', 'LIKE', '%'.$request->last_name.'%');
            }

        	if(!empty($request->job_id))
            {
                $applicants->where('job_applications.job_id', $request->job_id);
            }
            if(!empty($request->application_status))
            {
                $applicants->where('job_applications.application_status', $request->application_status);
            }
            if(!empty($request->job_title))
            {
                $applicants->where('sp_jobs.title', 'LIKE', '%'. $request->job_title.'%');
            }

            if(!empty($request->job_environment))
            {
                $applicants->where(function($query) use ($request) {
                    foreach ($request->job_environment as $key => $job_environment) {
                        if ($key === 0) {
                            $query->where('job_environment', 'LIKE', '%'.$job_environment.'%');
                            continue;
                        }
                        $query->orWhere('job_environment', 'LIKE', '%'.$job_environment.'%');
                    }
                });
            }
            if(!empty($request->job_type))
            {
                $applicants->whereIn('sp_jobs.job_type', $request->job_type);
            }
            if(!empty($request->userStatus))
            {
                $applicants->whereIn('users.status', $request->userStatus);
            }

            if(!empty($request->email))
            {
                $applicants->where('users.email', 'LIKE', '%'.$request->email.'%');
            }

            if(!empty($request->user_id))
            {
                $applicants->where('users.id', 'LIKE', '%'.$request->user_id.'%');
            }

            if(!empty($request->per_page_record))
            {
                $applicantsData = $applicants->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $applicantsData = $applicants->get();
            }
            return response(prepareResult(false, $applicantsData, getLangByLabelGroups('messages','messages_job_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

}
