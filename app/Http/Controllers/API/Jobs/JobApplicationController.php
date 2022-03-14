<?php

namespace App\Http\Controllers\API\Jobs;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Http\Resources\JobApplicationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use Event;
use App\Events\JobApplicationNotification; 
use App\Models\Job;

class JobApplicationController extends Controller
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
                $jobApplications = JobApplication::where('user_id',Auth::id())->orderBy('created_at','DESC')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $jobApplications = JobApplication::where('user_id',Auth::id())->orderBy('created_at','DESC')->get();
            }
    		return response(prepareResult(false, JobApplicationResource::collection($jobApplications), getLangByLabelGroups('messages','message_job_application_list')), config('http_response.success'));
    	}
    	catch (\Throwable $exception) 
    	{
    		\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }

    public function store(Request $request)
    {        
    	$validation = Validator::make($request->all(), [
    		'job_title'  => 'required'
    	]);

    	if ($validation->fails()) {
    		return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
    	}

    	DB::beginTransaction();
    	try
    	{
            $job = Job::find($request->job_id);
            $job_start_date = date("Y-m-d", strtotime($job->job_start_date));
            $job_end_date   = date("Y-m-d", strtotime($job->application_end_date));

    		$jobApplication = new JobApplication;
    		$jobApplication->user_id            = Auth::id();
    		$jobApplication->job_id             = $request->job_id;
    		$jobApplication->user_cv_detail_id  = $request->user_cv_detail_id;
    		$jobApplication->job_title          = $request->job_title;
    		$jobApplication->application_status = !empty($request->application_status) ? $request->application_status : 'pending';
    		$jobApplication->job_start_date     = $job_start_date;
    		$jobApplication->job_end_date       = $job_end_date;
    		$jobApplication->application_remark = $request->application_remark;
    		$jobApplication->attachment_url     = $request->attachment_url;
    		$jobApplication->save();

            // event(new JobApplicationNotification($jobApplication->id));
            // Notification Start

            $title = 'New Job Application';
            $body =  'New Application Received for Job '.$job->title;
            $user = $job->user;
            $type = 'Job Application';
            pushNotification($title,$body,$user,$type,true,'creator','job',$jobApplication->id,'posted-jobs');

            // Notification End
    		DB::commit();
    		return response()->json(prepareResult(false, new JobApplicationResource($jobApplication), getLangByLabelGroups('messages','message_job_application_created')), config('http_response.created'));
    	}
    	catch (\Throwable $exception)
    	{
    		DB::rollback();
    		\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }

    public function show(JobApplication $jobApplication)
    {
    	$jobApplication = JobApplication::where('id',$jobApplication->id)->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','userCvDetail.jobTags')->first();
    	return response()->json(prepareResult(false, new JobApplicationResource($jobApplication), getLangByLabelGroups('messages','message_job_application_list')), config('http_response.success'));
    }




    public function destroy(JobApplication $jobApplication)
    {
    	$jobApplication->delete();
    	return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_job_application_deleted')), config('http_response.success'));
    }

    public function statusUpdate(Request $request, $id)
    {
    	try
    	{
            $jobApplication = JobApplication::find($id);
            if(!$jobApplication)
            {
                return response()->json(prepareResult(false, [], getLangByLabelGroups('book_home_page','data_not_found')), config('http_response.not_found'));
            }

    		$jobApplication->update(['application_status' => !empty($request->application_status) ? $request->application_status : 'pending']);
            // Notification Start

            $title = 'Status Updated';
            
            if(Auth::id() == $jobApplication->user_id)
            {
                $user = $jobApplication->job->user;
                $user_type = 'creator';
                $screen = 'posted-jobs';
            }
            else
            {
                $user = $jobApplication->user;
                $user_type = 'applicant';
                $screen = 'my-jobs';
            }
            $body =  'Status updated to '.$request->application_status.' for Application on Job '.$jobApplication->job->title;
            $type = 'Job Application';
            pushNotification($title,$body,$user,$type,true,$user_type,'job',$jobApplication->id,$screen);

            // Notification End
    		return response()->json(prepareResult(false, new JobApplicationResource($jobApplication), getLangByLabelGroups('job_application_status','job_application_status'.$request->application_status)), config('http_response.success'));
    	}
    	catch (\Throwable $exception)
    	{
    		DB::rollback();
    		\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }
}
