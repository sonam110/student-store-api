<?php

namespace App\Http\Controllers\API\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserCvDetail;
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
use App\Models\Abuse;

class JobController extends Controller
{
    public function index(Request $request)
    {
        try
        {
            $jobs = Job::where('is_published', '1')->where('job_status', '1')->orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','jobTags:id,job_id,title','addressDetail','categoryMaster','subCategory','isApplied','isFavourite');

            if($request->category_master_id)
            {
                $jobs = $jobs->where('category_master_id',$request->category_master_id);
            }

            if(!empty($request->per_page_record))
            {
                $jobs = $jobs->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $jobs = $jobs->get();
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
            $user_package = UserPackageSubscription::where('user_id',Auth::id())->where('module','Job')->orderBy('created_at','desc')->first();
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
                $job->user_id                       = Auth::id();
                $job->language_id                   = $request->language_id;
                $job->address_detail_id             = $request->address_detail_id;
                $job->category_master_id            = $request->category_master_id;
                $job->sub_category_slug             = $request->sub_category_slug;
                $job->title                         = $request->title;
                $job->slug                          = Str::slug(substr($request->title, 0, 175)).$jobNumber;
                $job->description                   = $request->description;
                $job->duties_and_responsibilities   = $request->duties_and_responsibilities;
                $job->nice_to_have_skills           = json_encode($request->nice_to_have_skills);
                // $job->meta_description              = substr($request->description, 0, 250);
                $job->meta_description              = $request->meta_description;
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
                $job->published_at                  = $published_at;
                $job->meta_title                    = $request->meta_title;
                $job->meta_keywords                 = $request->meta_keywords;
                $job->job_status                    = '0';
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
                            $jobTag->user_id    = Auth::id();
                            $jobTag->save();
                        }
                    }

                    foreach ($request->nice_to_have_skills as $key => $nice_to_have) {
                        if(JobTag::where('nice_to_have', $nice_to_have)->where('job_id', $job->id)->count()<1)
                        {
                            $jobTag = new JobTag;
                            $jobTag->job_id         = $job->id;
                            $jobTag->nice_to_have   = $nice_to_have;
                            $jobTag->user_id        = Auth::id();
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
            $job = Job::with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','jobTags:id,job_id,title','addressDetail','categoryMaster','subCategory')->find($job->id);
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
        if($fav = FavouriteJob::where('job_id',$job->id)->where('sa_id',Auth::id())->first())
        {
            $favouriteJob = true;
            $favouriteId = $fav->id;
        }
        else
        {
            $favouriteJob = false;
            $favouriteId = null;
        }
        if(JobApplication::where('job_id',$job->id)->where('user_id',Auth::id())->first())
        {
            $applied = true;
        }
        else
        {
            $applied = false;
        }
        if($abuse = Abuse::where('job_id',$job->id)->where('user_id',Auth::id())->first())
        {
            $is_abuse_reported = true;
        }
        else
        {
            $is_abuse_reported = false;
        }
        $job = Job::with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path,show_email,show_contact_number','user.serviceProviderDetail','jobTags:id,job_id,title','addressDetail','categoryMaster','subCategory')->withCount('jobApplications','acceptedJobApplications')->find($job->id);
        $job['favourite_job'] = $favouriteJob;
        $job['favourite_id'] = $favouriteId;
        $job['is_applied'] = $applied;
        $job['is_abuse_reported'] = $is_abuse_reported;
        $job['likes_count'] = FavouriteJob::where('job_id',$job->id)->count();
        return response()->json(prepareResult(false, $job, getLangByLabelGroups('messages','messages_job_list')), config('http_response.success'));
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
            // $job->meta_description              = substr($request->description, 0, 250);
            $job->meta_description              = $request->meta_description;
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
            $job->published_at                  = $published_at;
            $job->meta_title                    = $request->meta_title;
            $job->meta_keywords                 = $request->meta_keywords;
            $job->save();
            if($job)
            {
                foreach ($request->tags as $key => $tag) {
                    if(JobTag::where('title', $tag)->where('job_id', $job->id)->count()<1)
                    {
                        $jobTag = new JobTag;
                        $jobTag->job_id     = $job->id;
                        $jobTag->title      = $tag;
                        $jobTag->user_id    = Auth::id();
                        $jobTag->save();
                    }
                }

                foreach ($request->nice_to_have_skills as $key => $nice_to_have) {
                    if(JobTag::where('nice_to_have', $nice_to_have)->where('job_id', $job->id)->count()<1)
                    {
                        $jobTag = new JobTag;
                        $jobTag->job_id         = $job->id;
                        $jobTag->nice_to_have   = $nice_to_have;
                        $jobTag->user_id        = Auth::id();
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

    public function destroy(Job $job)
    {
        if($job->jobApplications->count() > 0)
        {
            return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','messages_job_applicatons_exists')), config('http_response.success'));
        }
        $job->delete();
        JobApplication::where('job_id',$job->id)->delete();
        JobTag::where('job_id',$job->id)->delete();
        FavouriteJob::where('job_id',$job->id)->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_job_deleted')), config('http_response.success'));
    }

    public function jobFilter(Request $request)
    {
        try
        {
            $searchType = $request->searchType; //filter, promotions, latest, closingSoon, random, criteria job
            $jobs = Job::select('sp_jobs.*')
                    ->where('is_published', '1')
                    ->where('job_status', '1')
                    ->orderBy('created_at','DESC')
                    ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','jobTags:id,job_id,title','addressDetail','categoryMaster','subCategory','isApplied','isFavourite');
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
                    $jobs->join('address_details', function ($join) use ($request) {
                        $join->on('sp_jobs.address_detail_id', '=', 'address_details.id')
                        ->whereIn('city', $request->city);
                    });
                }
                $jobs->where('application_start_date','<=', date('Y-m-d'))
                    ->where('application_end_date','>=', date('Y-m-d'));
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
                        ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','jobTags:id,job_id,title','addressDetail','categoryMaster','subCategory');
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

    public function jobLandingPage(Request $request)
    {
        $content = new Request();
        $content->searchType = 'promotions';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $jobs_promotions = $this->jobFilter($content);
        
        $content = new Request();
        $content->searchType = 'latest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $jobs_latest = $this->jobFilter($content);
        
        $content = new Request();
        $content->searchType = 'closingSoon';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $jobs_closing_soon = $this->jobFilter($content);
        
        $content = new Request();
        $content->searchType = 'random';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $jobs_random = $this->jobFilter($content);
        
        
        
        $returnObj = [
                'jobs' => [
                    'jobs_promotions'  		=> $jobs_promotions, 
                    'jobs_closing_soon'  	=> $jobs_closing_soon,
                    'jobs_random'        	=> $jobs_random, 
                    'jobs_latest'        	=> $jobs_latest,
                ]
            ];
        
        return response(prepareResult(false, $returnObj, getLangByLabelGroups('messages','messages_jobs_list')), config('http_response.success'));
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
        if($request->action=='promote') 
        {
            $validation = Validator::make($request->all(), [
                'is_promoted'           => 'required|boolean',
                // 'promotion_start_date'  => 'required|date',
                // 'promotion_end_date'    => 'required|date|after_or_equal:promotion_start_date',
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
            if($request->action=='promote') 
            {
                $getJob->is_promoted = $request->is_promoted;

                if($request->is_promoted == true)
                {

                    // $getJob->promotion_start_date   = $request->promotion_start_date;
                    // $getJob->promotion_end_date     = $request->promotion_end_date;

                    $getJob->promotion_start_date = date('Y-m-d');
                    $user_package = UserPackageSubscription::where('user_id',Auth::id())->where('module','Job')->orderBy('created_at','desc')->first();
                    if($user_package->no_of_boost == $user_package->used_no_of_boost)
                    {
                        DB::rollback();
                        return response()->json(prepareResult(true, ['Package Use Exhasted'], getLangByLabelGroups('messages','message_no_of_boost_exhausted_error')), config('http_response.internal_server_error'));
                    }
                    $getJob->promotion_end_date  = date('Y-m-d',strtotime('+'.$user_package->boost_no_of_days.'days'));
                    $user_package->update(['used_no_of_boost'=>($user_package->used_no_of_boost + 1)]);
                    $title = 'Job Promoted';
                    $body =  'Job '.$getJob->title.'has been successfully Promoted  from '.$getJob->promotion_start_date.' to '.$getJob->promotion_end_date.'.';
                }
                else
                {
                    $title = 'Job Removed from Promoted';
                    $body =  'Job '.$getJob->title.'has been successfully Removed from Promotion.';
                }

            }
            $getJob->save();

            
            $type = 'Job Action';
            pushNotification($title,$body,Auth::user(),$type,true,'creator','job',$getJob->id,'posted-jobs');

            DB::commit();
            return response()->json(prepareResult(false, $getJob, getLangByLabelGroups('messages','messages_job_'.$request->action)), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function jobApplications(Request $request, $id)
    {
    	try
    	{
    	    if(!empty($request->per_page_record))
    	    {
    	        $jobApplications = JobApplication::where('job_id',$id)->orderBy('created_at','DESC')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
    	    }
    	    else
    	    {
    	        $jobApplications = jobApplication::where('job_id',$id)->orderBy('created_at','DESC')->get();
    	    }
    	    return response(prepareResult(false, JobApplicationResource::collection($jobApplications), getLangByLabelGroups('messages','messages_job_application_list')), config('http_response.success'));
    	}
    	catch (\Throwable $exception) 
    	{
    	    return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }

    public function jobSPJobs(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $jobs = Job::where('user_id', Auth::id())->orderBy('created_at','DESC')->with('jobTags:id,job_id,title','categoryMaster','subCategory','isApplied','isFavourite')->withCount('jobApplications','acceptedJobApplications')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $jobs = Job::where('user_id', Auth::id())->orderBy('created_at','DESC')->with('jobTags:id,job_id,title','categoryMaster','subCategory','isApplied','isFavourite')->withCount('jobApplications','acceptedJobApplications')->get();
            }
            return response(prepareResult(false, $jobs, getLangByLabelGroups('messages','messages_job_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function jobSPJobsApplications(Request $request)
    {
        try
        {
            $jobs = Job::where('user_id', Auth::id())->get(['id'])->toArray();

            if(!empty($request->per_page_record))
            {
                $jobApplications = JobApplication::whereIn('job_id',$jobs)->orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path,status','user.cvDetail')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $jobApplications = JobApplication::whereIn('job_id',$jobs)->orderBy('created_at','DESC')->with('user:id, first_name, last_name,gender, dob, email, contact_number, profile_pic_path, status','user.cvDetail')->get();
            }
            return response(prepareResult(false, $jobApplications, getLangByLabelGroups('messages','messages_job_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function applicantsFilter(Request $request)
    {
        try
        {
            $searchType = $request->searchType; //filter, criteria, random, recent
            //in criteria: title, skills, city, job type, work exp.
            $applicants = JobApplication::select('job_applications.*')
                    ->join('sp_jobs', function ($join) {
                        $join->on('job_applications.job_id', '=', 'sp_jobs.id');
                    })
                    // ->join('users', function ($join) {
                    //     $join->on('job_applications.user_id', '=', 'users.id');
                    // })
                    ->orderBy('job_applications.created_at','DESC')
                    ->join('user_cv_details', function ($join) {
                        $join->on('job_applications.user_id', '=', 'user_cv_details.user_id');
                    })
                    ->with('job:id,title','user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.cvDetail.jobTags','user.defaultAddress');

            if($searchType=='filter')
            {
                if($request->auth_applicable == true)
                {
                    $applicants->where('sp_jobs.user_id', Auth::id());
                }
                if(!empty($request->job_environment))
                {
                    $applicants->where(function($query) use ($request) {
                        foreach ($request->job_environment as $key => $job_environment) {
                            if ($key === 0) {
                                $query->where('user_cv_details.preferred_job_env', 'LIKE', '%'.$job_environment.'%');
                                continue;
                            }
                            $query->orWhere('user_cv_details.preferred_job_env', 'LIKE', '%'.$job_environment.'%');
                        }
                    });
                }
                if(!empty($request->min_years_of_experience))
                {
                    $applicants->where('user_cv_details.total_experience', '>=', $request->min_years_of_experience);
                }
                if(!empty($request->max_years_of_experience))
                {
                    $applicants->where('user_cv_details.total_experience', '<=', $request->max_years_of_experience);
                }
                if(!empty($request->known_languages))
                {
                    $applicants->where(function($query) use ($request) {
                        foreach ($request->known_languages as $key => $known_languages) {
                            if ($key === 0) {
                                $query->where('user_cv_details.languages_known', 'LIKE', '%'.$known_languages.'%');
                                continue;
                            }
                            $query->orWhere('user_cv_details.languages_known', 'LIKE', '%'.$known_languages.'%');
                        }
                    });
                }
                if(!empty($request->job_tags))
                {
                    $applicants->where(function($query) use ($request) {
                        foreach ($request->job_tags as $key => $job_tags) {
                            if ($key === 0) {
                                $query->where('user_cv_details.key_skills', 'LIKE', '%'.$job_tags.'%');
                                continue;
                            }
                            $query->orWhere('user_cv_details.key_skills', 'LIKE', '%'.$job_tags.'%');
                        }
                    });
                }
                if(!empty($request->search_title))
                {
                    $applicants->join('user_work_experiences', function ($join) {
                        $join->on('job_applications.user_id', '=', 'user_work_experiences.user_id');
                    });
                    $applicants->where('user_work_experiences.title', 'LIKE', '%'.$request->search_title.'%');
                }
            }
            elseif($searchType=='criteria')
            {
                //Job env, work-exp, city, title, skills,

                $jobsIds = Job::select('sp_jobs.id','sp_jobs.title','sp_jobs.job_environment','sp_jobs.years_of_experience','sp_jobs.address_detail_id')
                        ->with('jobTags:id,job_id,title', 'addressDetail:id,city')
                        ->where('sp_jobs.is_published', '1')
                        ->where('sp_jobs.application_start_date','<=', date('Y-m-d'))
                        ->where('sp_jobs.application_end_date','>=', date('Y-m-d'));
                if($request->auth_applicable == true)
                {
                    $jobsIds->where('sp_jobs.user_id', Auth::id());
                }

                if(!empty($request->per_page_record))
                {
                    $jobsIds = $jobsIds->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
                }
                else
                {
                    $jobsIds = $jobsIds->get();
                }

                $titleMatched = array();
                $skillsMatched = array();
                $workExpMatched = array();
                $cityMatched = array();
                $jobEnvMatched = array();

                //Title
                foreach ($jobsIds as $key => $job) {
                    $usersIds = UserCvDetail::select('user_id')->where('user_cv_details.title', 'LIKE', '%'.$job->title.'%')->limit(100)->inRandomOrder()->get();
                    foreach ($usersIds as $key => $user) {
                        $titleMatched[] = ['user_id' => $user->user_id, 'job_id' => $job->id];
                    }

                }

                //Skills
                foreach ($jobsIds as $key => $job) {
                    foreach ($job->jobTags as $tagkey => $tags) {
                        $usersIds = UserCvDetail::select('user_id')->where('user_cv_details.key_skills', 'LIKE', '%'.$tags->title.'%')->limit(100)->inRandomOrder()->get();
                        foreach ($usersIds as $key => $user) {
                            $skillsMatched[] = ['user_id' => $user->user_id, 'job_id' => $job->id];
                            
                        }
                    }
                }

                //Work Exp
                foreach ($jobsIds as $key => $job) {
                    $usersIds = UserCvDetail::select('user_id')->where('user_cv_details.total_experience', '>=', $job->years_of_experience)->limit(100)->inRandomOrder()->get();
                    foreach ($usersIds as $key => $user) {
                        $workExpMatched[] = ['user_id' => $user->user_id, 'job_id' => $job->id];
                    }
                }

                //City
                foreach ($jobsIds as $key => $job) {
                    $city = $job->addressDetail->city;
                    $usersIds = UserCvDetail::select('user_id')
                            ->with(['addressDetail' => function($q) use ($city) {
                                $q->where('city', $city);
                            }])
                            ->limit(100)
                            ->inRandomOrder()
                            ->get();
                    foreach ($usersIds as $key => $user) {
                        $cityMatched[] = ['user_id' => $user->user_id, 'job_id' => $job->id];
                        
                    }
                }

                //Job Env
                foreach ($jobsIds as $key => $job) {
                    if(!empty($job->job_environment))
                    {
                        $job_environments = json_decode($job->job_environment, true);
                        $usersIds = UserCvDetail::select('user_id')
                            ->where(function($query) use ($job_environments) {
                                foreach ($job_environments as $key => $job_environment) {
                                    if ($key === 0) {
                                        $query->where('preferred_job_env', 'LIKE', '%'.$job_environment.'%');
                                        continue;
                                    }
                                    $query->orWhere('preferred_job_env', 'LIKE', '%'.$job_environment.'%');
                                }
                            })
                            ->limit(100)->inRandomOrder()->get();
                        foreach ($usersIds as $key => $user) {
                            $jobEnvMatched[] = ['user_id' => $user->user_id, 'job_id' => $job->id];
                        }
                    }
                }

                $mergeArray = array_merge_recursive($titleMatched,$skillsMatched,$workExpMatched,$cityMatched,$jobEnvMatched);
                if(sizeof($mergeArray)>0)
                {
                    foreach($mergeArray as $key => $val) {
                        $new_arr[$val['job_id']][]=$val['user_id'];
                    }

                    $final = array();
                    foreach ($new_arr as $key => $value) {
                        $final[$key] = array_count_values($value);  
                    }

                    $newRec = array();
                    foreach ($final as $jobkey => $value) {
                        foreach ($value as $userkey => $nvalue) {
                            if($nvalue>2)
                            {
                                $newRec[$jobkey][] = $userkey;
                            }   
                        }   
                    }
                    $applicants = array();
                    $count = 0;
                    foreach ($newRec as $key => $job_users_id) {
                        foreach ($job_users_id as $userkey => $user) {
                            $applicantsUser[$count] = ['user' => User::select('id','first_name','last_name','gender','dob','email','contact_number','profile_pic_path')->with('cvDetail','defaultAddress')->where('users.id', $user)
                            ->first()];
                            $applicantsUser[$count]['job'] = Job::select('id','title')->where('sp_jobs.id', $key)
                            ->first();
                            $count++;
                        }
                    };
                }
                else
                {
                    $applicantsUser = [];
                }

                if(!empty($request->per_page_record))
                {
                    $jobsIds = Job::select('sp_jobs.id')
                        ->where('sp_jobs.is_published', '1')
                        ->where('sp_jobs.application_start_date','<=', date('Y-m-d'))
                        ->where('sp_jobs.application_end_date','>=', date('Y-m-d'))
                        ->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);

                    $returnObj = [
                        'jobsIds' => $jobsIds,
                        'bestMatches' => $applicantsUser
                    ];
                    return response(prepareResult(false, $returnObj, getLangByLabelGroups('messages','messages_job_list')), config('http_response.success'));
                }

                return response(prepareResult(false, $applicantsUser, getLangByLabelGroups('messages','messages_job_list')), config('http_response.success'));
            }
            elseif($searchType=='random')
            {
                if($request->auth_applicable == true)
                {
                    $applicants->where('sp_jobs.user_id', Auth::id());
                }
                $applicants->orderBy('job_applications.id','DESC')
                    ->where('sp_jobs.is_published', '1')
                    ->where('sp_jobs.application_start_date','<=', date('Y-m-d'))
                    ->where('sp_jobs.application_end_date','>=', date('Y-m-d'))
                    ->inRandomOrder();
            }
            elseif($searchType=='recent')
            {
                if($request->auth_applicable == true)
                {
                    $applicants->where('sp_jobs.user_id', Auth::id());
                }
                $applicants->orderBy('job_applications.id','DESC')
                    ->where('sp_jobs.is_published', '1')
                    ->where('sp_jobs.application_start_date','<=', date('Y-m-d'))
                    ->where('sp_jobs.application_end_date','>=', date('Y-m-d'));
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
            // dd($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}
