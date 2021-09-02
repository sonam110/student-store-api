<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication; 
use App\Models\JobTag;
use App\Http\Resources\JobResource;
use App\Http\Resources\JobApplicationResource;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use App\Models\FavouriteJob;
use App\Models\Notification;
use Event;
use App\Events\JobPostNotification;
use App\Models\UserPackageSubscription;

class JobController extends Controller
{
    public function index(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $jobs = Job::orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path','jobTags:id,job_id,title','jobApplications.user')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $jobs = Job::orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path','jobTags:id,job_id,title','jobApplications.user')->get();
            }
            return response(prepareResult(false, $jobs, getLangByLabelGroups('messages','messages_job_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function jobs(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $jobs = Job::orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path','jobTags:id,job_id,title','jobApplications.user')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $jobs = Job::orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path','jobTags:id,job_id,title','jobApplications.user')->get();
            }
            return response(prepareResult(false, $jobs, getLangByLabelGroups('messages','messages_job_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'language_id'       => 'required',
            'address_detail_id' => 'required',
            'title'             => 'required',
            'job_type'          => 'required',
            // 'job_nature'        => 'required',
            'job_environment'   => 'required',
            'description'       => 'required',
            'application_start_date'    => 'required|date',
            'application_end_date'      => 'required|date|after_or_equal:application_start_date',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }
        DB::beginTransaction();
        try
        {
            if(!empty($request->user_id))
            {
                $user_id = $request->user_id;
            }
            else
            {
                $user_id = Auth::id();
            }

            $user_package = UserPackageSubscription::where('user_id',$user_id)->where('module','Job')->orderBy('created_at','desc')->first();
            if(empty($user_package))
            {
                return response()->json(prepareResult(true, ['No Package Subscribed'], getLangByLabelGroups('messages','message_no_package_subscribed_error')), config('http_response.internal_server_error'));
            }
            elseif($user_package->job_ads == $user_package->used_job_ads)
            {
                return response()->json(prepareResult(true, ['Package Use Exhausted'], getLangByLabelGroups('messages','message_job_ads_exhausted_error')), config('http_response.internal_server_error'));
            }
            else
            {
                $getLastJob = Job::select('id')->orderBy('created_at','DESC')->first();
                if($getLastJob) {
                    $jobNumber = $getLastJob->id;
                } else {
                    $jobNumber = 1;
                }

                if($request->is_published == true)
                { 
                    $published_at = date('Y-m-d');
                }
                else
                {
                    $published_at = null;
                }

                $job_start_date = date("Y-m-d", strtotime($request->job_start_date));
                $application_start_date = date("Y-m-d", strtotime($request->application_start_date));
                $application_end_date = date("Y-m-d", strtotime($request->application_end_date)); 

                $job                                = new Job;
                $job->user_id                       = $user_id;
                $job->language_id                   = $request->language_id;
                $job->address_detail_id             = $request->address_detail_id;
                $job->category_master_id            = $request->category_master_id;
                $job->sub_category_slug             = $request->sub_category_slug;
                $job->title                         = $request->title;
                $job->slug                          = Str::slug(substr($request->title, 0, 175)).$jobNumber;
                $job->description                   = $request->description;
                $job->duties_and_responsibilities   = $request->duties_and_responsibilities;
                $job->nice_to_have_skills           = json_encode($request->nice_to_have_skills);
                $job->meta_description              = substr($request->description, 0, 250);
                $job->short_summary                 = substr($request->description, 0, 175);
                $job->job_type                      = $request->job_type;
                $job->job_nature                    = $request->job_nature;
                $job->job_hours                     = $request->job_hours;
                $job->job_environment               = json_encode($request->job_environment);
                $job->years_of_experience           = $request->years_of_experience;
                $job->known_languages               = json_encode($request->known_languages);
                $job->job_start_date                = $job_start_date;
                $job->application_start_date        = $application_start_date;
                $job->application_end_date          = $application_end_date;
                $job->is_published                  = $request->is_published;
                $job->meta_title                    = $request->meta_title;
                $job->meta_keywords                 = $request->meta_keywords;
                $job->published_at                  = $published_at;
                $job->save();
                if($job)
                {
                    $user_package->update(['used_job_ads'=>($user_package->used_job_ads + 1)]);


                    foreach ($request->tags as $key => $tag) {
                        if(JobTag::where('title', $tag)->where('job_id', $job->id)->count()<1)
                        {
                            $jobTag = new JobTag;
                            $jobTag->job_id     = $job->id;
                            $jobTag->title      = $tag;
                            $jobTag->user_id    = $user_id;
                            $jobTag->save();
                        }
                    }

                    foreach ($request->nice_to_have_skills as $key => $nice_to_have) {
                        if(JobTag::where('nice_to_have', $nice_to_have)->where('job_id', $job->id)->count()<1)
                        {
                            $jobTag = new JobTag;
                            $jobTag->job_id         = $job->id;
                            $jobTag->nice_to_have   = $nice_to_have;
                            $jobTag->user_id        = $user_id;
                            $jobTag->save();
                        }
                    }

                    if($request->is_published == true)
                    { 
                        event(new JobPostNotification($job->id));
                    }
                }
            }
            
            DB::commit();
            $job = Job::with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path','jobTags:id,job_id,title','addressDetail','categoryMaster','subCategory')->find($job->id);
            return response()->json(prepareResult(false, $job, getLangByLabelGroups('messages','messages_job_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function show(Job $job)
    {
        try
        {
            $job_id = $job->id;
            $jobs = Job::with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path','jobTags:id,job_id,title','jobApplications.user')->find($job_id);
            return response(prepareResult(false, $jobs, getLangByLabelGroups('messages','messages_job_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function update(Request $request, Job $job)
    {
        $validation = Validator::make($request->all(), [
            'language_id'       => 'required',
            'address_detail_id' => 'required',
            'title'             => 'required',
            'job_type'          => 'required',
            // 'job_nature'        => 'required',
            'job_environment'   => 'required',
            'description'       => 'required',
            'application_start_date'    => 'required|date',
            'application_end_date'      => 'required|date|after_or_equal:application_start_date',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        { 
            //delete tags before update
            JobTag::where('job_id', $job->id)->delete();

            if($request->is_published == true)
            { 
                $published_at = date('Y-m-d');
            }
            else
            {
                $published_at = $job->published_at;
            }

            if(!empty($request->user_id))
            {
                $user_id = $request->user_id;
            }
            else
            {
                $user_id = Auth::id();
            }

            $job_start_date = date("Y-m-d", strtotime($request->job_start_date));
            $application_start_date = date("Y-m-d", strtotime($request->application_start_date));
            $application_end_date = date("Y-m-d", strtotime($request->application_end_date));

            $job->language_id                   = $request->language_id;
            $job->address_detail_id             = $request->address_detail_id;
            $job->category_master_id            = $request->category_master_id;
            $job->sub_category_slug             = $request->sub_category_slug;
            $job->title                         = $request->title;
            $job->description                   = $request->description;
            $job->duties_and_responsibilities   = $request->duties_and_responsibilities;
            $job->nice_to_have_skills           = json_encode($request->nice_to_have_skills);
            $job->meta_description              = substr($request->description, 0, 250);
            $job->short_summary                 = substr($request->description, 0, 175);
            $job->job_type                      = $request->job_type;
            $job->job_nature                    = $request->job_nature;
            $job->job_hours                     = $request->job_hours;
            $job->job_environment               = json_encode($request->job_environment);
            $job->years_of_experience           = $request->years_of_experience;
            $job->known_languages               = json_encode($request->known_languages);
            $job->job_start_date                = $job_start_date;
            $job->application_start_date        = $application_start_date;
            $job->application_end_date          = $application_end_date;
            $job->is_published                  = $request->is_published;
            $job->meta_title                    = $request->meta_title;
            $job->meta_keywords                 = $request->meta_keywords;
            $job->published_at                  = $published_at;
            $job->save();
            if($job)
            {
                foreach ($request->tags as $key => $tag) {
                    if(JobTag::where('title', $tag)->where('job_id', $job->id)->count()<1)
                    {
                        $jobTag = new JobTag;
                        $jobTag->job_id     = $job->id;
                        $jobTag->title      = $tag;
                        $jobTag->user_id    = $user_id;
                        $jobTag->save();
                    }
                }

                foreach ($request->nice_to_have_skills as $key => $nice_to_have) {
                    if(JobTag::where('nice_to_have', $nice_to_have)->where('job_id', $job->id)->count()<1)
                    {
                        $jobTag = new JobTag;
                        $jobTag->job_id         = $job->id;
                        $jobTag->nice_to_have   = $nice_to_have;
                        $jobTag->user_id        = $user_id;
                        $jobTag->save();
                    }
                }
                if($request->is_published == true)
                {
                    event(new JobPostNotification($job->id));
                }
            }
            DB::commit();
            return response()->json(prepareResult(false, new JobResource($job), getLangByLabelGroups('messages','messages_job_updated')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function jobAction($job_id, Request $request)
    {
        if($request->action=='update-status')
        {
            $validation = Validator::make($request->all(), [
                'job_status'    => 'required|boolean'
            ]);
        }

        if($request->action=='publish') 
        {
            $validation = Validator::make($request->all(), [
                'is_published'    => 'required|boolean'
            ]);
        }

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $getJob = Job::find($job_id);
            if(!$getJob)
            {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
            }

            //For Update job status
            if($request->action=='update-status')
            {
                $getJob->job_status = $request->job_status;

                $title = 'Job Status Updated';
                $body =  'Job '.$getJob->title.' status has been successfully updated.';
            }
            if($request->action=='publish') 
            {
                $getJob->is_published = $request->is_published;
                $getJob->published_at = date('Y-m-d');
                // event(new JobPostNotification($job_id));
                $title = 'Job Published';
                $body =  'Job '.$getJob->title.'has been successfully Published.';
            }
            $getJob->save();

            
            $type = 'Job Action';
            pushNotification($title,$body,$getJob->user,$type,true,'creator','job',$getJob->id,'posted-jobs');

            DB::commit();
            return response()->json(prepareResult(false, $getJob, getLangByLabelGroups('messages','messages_job_'.$request->action)), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }


    public function multipleStatusUpdate(Request $request)
    {
        $validation = Validator::make($request->all(), [
                'job_status'    => 'required',
                'job_id'    => 'required'
            ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $jobs = Job::whereIn('id',$request->job_id)->get();
            foreach ($jobs as $key => $job) {
                $job->job_status = $request->job_status;
                
                $title = 'Job Status Updated';
                $body =  'Job '.$job->title.' status has been successfully updated.';
                $job->save();

                $type = 'Job Action';
                pushNotification($title,$body,$job->user,$type,true,'creator','job',$job->id,'my-listing');
            }

            DB::commit();
            return response()->json(prepareResult(false, $jobs, getLangByLabelGroups('messages','messages_job_'.$request->action)), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function multiplePublishUpdate(Request $request)
    {
        $validation = Validator::make($request->all(), [
                'is_published'    => 'required|boolean',
                'job_id'    => 'required'
            ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $jobs = Job::whereIn('id',$request->job_id)->get();
            foreach ($jobs as $key => $job) {
                $job->is_published = $request->is_published;
                if($request->is_published == true)
                {
                    $job->published_at = date('Y-m-d');
                }
                
                $title = 'Job Published';
                $body =  'Job '.$job->title.'has been successfully Published.';
                $job->save();

                $type = 'Job Action';
                pushNotification($title,$body,$job->user,$type,true,'creator','job',$job->id,'my-listing');
            }

            DB::commit();
            return response()->json(prepareResult(false, $jobs, getLangByLabelGroups('messages','messages_job_'.$request->action)), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function filter(Request $request)
    {
        try
        {
            $searchType = $request->searchType; //filter, promotions, latest, closingSoon, random, criteria job
            $jobs = Job::select('sp_jobs.*')
                    ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path','user.serviceProviderDetail','jobTags:id,job_id,title','addressDetail','categoryMaster','subCategory','isApplied','isFavourite');
            if($searchType=='filter')
            {
                if(!empty($request->category_master_id))
                {
                    $jobs->where('category_master_id',$request->category_master_id);
                }
                if(!empty($request->sub_category_slug))
                {
                    $jobs->where('sub_category_slug',$request->sub_category_slug);
                }

                if(!empty($request->job_environment))
                {
                    $jobs->where(function($query) use ($request) {
                        foreach ($request->job_environment as $key => $job_environment) {
                            if ($key === 0) {
                                $query->where('job_environment', 'LIKE', '%'.$job_environment.'%');
                                continue;
                            }
                            $query->orWhere('job_environment', 'LIKE', '%'.$job_environment.'%');
                        }
                    });
                }
                if(!empty($request->published_date))
                {
                    $jobs->where('sp_jobs.published_at', '<=',  date("Y-m-d", strtotime($request->published_date)))->orderBy('sp_jobs.published_at','desc');
                }
                if(!empty($request->applying_date))
                {
                    $jobs->where('sp_jobs.application_end_date', '<=',  date("Y-m-d", strtotime($request->applying_date)))->orderBy('sp_jobs.application_end_date','asc');
                }
                if(!empty($request->min_years_of_experience))
                {
                    $jobs->where('years_of_experience', '>=', $request->min_years_of_experience);
                }
                if(!empty($request->max_years_of_experience))
                {
                    $jobs->where('years_of_experience', '<=', $request->max_years_of_experience);
                }
                if(!empty($request->known_languages))
                {
                    $jobs->where(function($query) use ($request) {
                        foreach ($request->known_languages as $key => $known_languages) {
                            if ($key === 0) {
                                $query->where('known_languages', 'LIKE', '%'.$known_languages.'%');
                                continue;
                            }
                            $query->orWhere('known_languages', 'LIKE', '%'.$known_languages.'%');
                        }
                    });
                }
                if(!empty($request->job_type))
                {
                    $jobs->where(function($query) use ($request) {
                        foreach ($request->job_type as $key => $job_type) {
                            if ($key === 0) {
                                $query->where('job_type', 'LIKE', '%'.$job_type.'%');
                                continue;
                            }
                            $query->orWhere('job_type', 'LIKE', '%'.$job_type.'%');
                        }
                    });
                }
                if(!empty($request->job_tags))
                {
                    $jobs->join('job_tags', function ($join) {
                        $join->on('sp_jobs.id', '=', 'job_tags.job_id');
                    });
                    $jobs->where(function($query) use ($request) {
                        foreach ($request->job_tags as $key => $job_tags) {
                            if ($key === 0) {
                                $query->where('job_tags.title', 'LIKE', '%'.$job_tags.'%');
                                continue;
                            }
                            $query->orWhere('job_tags.title', 'LIKE', '%'.$job_tags.'%');
                        }
                    });
                }
                if(!empty($request->search_title))
                {
                    $jobs->where('title', 'LIKE', '%'.$request->search_title.'%');
                }

                //future: distance filter implement
                /*if(!empty($request->distance))
                {
                    $jobs->where('distance', $request->distance);
                }*/
                if(!empty($request->city))
                {
                    $jobs->join('address_details', function ($join) {
                        $join->on('sp_jobs.address_detail_id', '=', 'address_details.id');
                    })
                    ->where('address_details.city', $request->city);
                }

                if(!empty($request->job_status))
                {
                    $jobs->where('job_status', $request->job_status);
                }
                if(!empty($request->user_id))
                {
                    $jobs->where('sp_jobs.user_id',$request->user_id);
                }
                // $jobs->where('application_start_date','<=', date('Y-m-d'))
                //     ->where('application_end_date','>=', date('Y-m-d'));

                if(!empty($request->company_name))
                {
                    $jobs->join('service_provider_details', function ($join) {
                        $join->on('sp_jobs.user_id', '=', 'service_provider_details.user_id');
                    })
                    ->where('service_provider_details.company_name','like','%'.$request->company_name.'%');
                }
            }
            elseif($searchType=='promotions')
            {
                $jobs->where('is_promoted', '1')
                    ->where('promotion_start_date','<=', date('Y-m-d'))
                    ->where('promotion_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='latest')
            {
                $jobs->orderBy('created_at','DESC')
                    ->where('application_start_date','<=', date('Y-m-d'))
                    ->where('application_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='closingSoon')
            {
                $jobs->whereBetween('application_end_date', [date('Y-m-d'), date('Y-m-d', strtotime("+2 days"))]);
            }
            elseif($searchType=='random')
            {
                $jobs->where('application_start_date','<=', date('Y-m-d'))
                    ->where('application_end_date','>=', date('Y-m-d'))
                    ->inRandomOrder();
            }
            elseif($searchType=='criteria')
            {
                //Job env, work-exp, city, title, skills, 
                $userCvDetail = UserCvDetail::where('user_id', Auth::id())->first();
                if(!$userCvDetail)
                {
                    return response()->json(prepareResult(true, 'CV not updated', getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
                }
                $jobAllArray = array();

                //Job env
                if(!empty($userCvDetail->preferred_job_env))
                {
                    $jobsIdsMatch = Job::select('sp_jobs.id')
                        ->where('is_published', '1')
                        ->where('application_start_date','<=', date('Y-m-d'))
                        ->where('application_end_date','>=', date('Y-m-d'));
                    $job_environments = json_decode($userCvDetail->preferred_job_env, true);
                    $jobsIdsMatch->where(function($query) use ($job_environments) {
                        foreach ($job_environments as $key => $job_environment) {
                            if ($key === 0) {
                                $query->where('job_environment', 'LIKE', '%'.$job_environment.'%');
                                continue;
                            }
                            $query->orWhere('job_environment', 'LIKE', '%'.$job_environment.'%');
                        }
                    });
                }
                $jobEnvs = $jobsIdsMatch->get();

                foreach ($jobEnvs as $key => $jobId) {
                    $jobAllArray[] = $jobId->id;
                }

                //work-exp
                if(!empty($userCvDetail->total_experience))
                {
                    $jobsIdsMatch = Job::select('sp_jobs.id')
                        ->where('is_published', '1')
                        ->where('application_start_date','<=', date('Y-m-d'))
                        ->where('application_end_date','>=', date('Y-m-d'));

                    $jobsIdsMatch->where('years_of_experience', '<=', $userCvDetail->total_experience);
                    $jobTotalExps = $jobsIdsMatch->get();
                
                    foreach ($jobTotalExps as $key => $jobId) {
                        $jobAllArray[] = $jobId->id;
                    }
                }

                //city
                if($userCvDetail->user->defaultAddress)
                {
                    $jobsIdsMatch = Job::select('sp_jobs.id')
                        ->where('is_published', '1')
                        ->where('application_start_date','<=', date('Y-m-d'))
                        ->where('application_end_date','>=', date('Y-m-d'));

                    $cityName = $userCvDetail->user->defaultAddress->city;
                    $jobsIdsMatch->with(['addressDetail' => function($q) use ($cityName) {
                        $q->where('city', $cityName);
                    }]);

                    $jobCities = $jobsIdsMatch->get();
                
                    foreach ($jobCities as $key => $jobId) {
                        $jobAllArray[] = $jobId->id;
                    }
                }

                //skills
                if(!empty($userCvDetail->key_skills))
                {
                    $jobsIdsMatch = Job::select('sp_jobs.id')
                        ->where('is_published', '1')
                        ->where('application_start_date','<=', date('Y-m-d'))
                        ->where('application_end_date','>=', date('Y-m-d'));

                    $key_skills = json_decode($userCvDetail->key_skills, true);

                    $jobsIdsMatch->join('job_tags', function ($join) {
                        $join->on('sp_jobs.id', '=', 'job_tags.job_id');
                    });
                    $jobsIdsMatch->where(function($query) use ($key_skills) {
                        foreach ($key_skills as $key => $skill) {
                            if ($key === 0) {
                                $query->where('job_tags.title', 'LIKE', '%'.$skill.'%');
                                continue;
                            }
                            $query->orWhere('job_tags.title', 'LIKE', '%'.$skill.'%');
                        }
                    });

                    $jobSkills = $jobsIdsMatch->get();
                
                    foreach ($jobSkills as $key => $jobId) {
                        $jobAllArray[] = $jobId->id;
                    }
                }

                //title
                if(!empty($userCvDetail->title))
                {
                    $jobsIdsMatch = Job::select('sp_jobs.id')
                        ->where('is_published', '1')
                        ->where('application_start_date','<=', date('Y-m-d'))
                        ->where('application_end_date','>=', date('Y-m-d'));

                    $cvTitle = $userCvDetail->title;
                    $jobsIdsMatch->where(function($query) use ($cvTitle) {
                        $query->where('sp_jobs.title', 'LIKE', '%'.$cvTitle.'%');
                    });

                    $jobTitles = $jobsIdsMatch->get();
                
                    foreach ($jobTitles as $key => $jobId) {
                        $jobAllArray[] = $jobId->id;
                    }
                }

                $actualArray = array();
                $allIds = array_count_values($jobAllArray);
                foreach ($allIds as $key => $value) {
                    if($value>2)
                    {
                        $actualArray[] = $key;
                    }
                }
                $jobs = Job::select('sp_jobs.*')
                        ->whereIn('sp_jobs.id',$actualArray)
                        ->where('is_published', '1')
                        ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path','user.serviceProviderDetail','jobTags:id,job_id,title','addressDetail','categoryMaster','subCategory');
            }
            if(!empty($request->per_page_record))
            {
                $jobsData = $jobs->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $jobsData = $jobs->get();
            }
            if($request->other_function=='yes')
            {
                return $jobsData;
            }
            return response(prepareResult(false, $jobsData, getLangByLabelGroups('messages','messages_job_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }


    public function destroy($job_id)
    {
        try
        {
            $job = Job::find($job_id);
            if($job->jobApplications->count() > 0)
            {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','messages_job_applicatons_exists')), config('http_response.success'));
            }
            $job->delete();
            return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_job_deleted')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}
