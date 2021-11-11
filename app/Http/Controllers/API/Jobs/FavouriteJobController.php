<?php

namespace App\Http\Controllers\API\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\FavouriteJob;
use App\Models\Language;
use Auth;
use DB;

class FavouriteJobController extends Controller
{
    function __construct()
    {
        $this->lang_id = Language::first()->id;
        if(!empty(request()->lang_id))
        {
            $this->lang_id = request()->lang_id;
        }
    }

    public function index(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;

            if(Auth::user()->user_type_id=='2')
            {
                $query = FavouriteJob::select('favourite_jobs.*')
                        ->join('sp_jobs', function ($join) {
                            $join->on('favourite_jobs.job_id', '=', 'sp_jobs.id');
                        })
                        ->where('sp_jobs.application_end_date','>=', date('Y-m-d'))
                        ->where('favourite_jobs.sa_id', Auth::id())
                        ->orderBy('favourite_jobs.created_at','DESC')
                        ->with('job.user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','job.user.serviceProviderDetail','job.addressDetail','job.jobTags:id,job_id,title','job.categoryMaster','job.subCategory')
                        ->with(['job.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                            $q->select('id','category_master_id','title','slug')
                                ->where('language_id', $lang_id)
                                ->where('is_parent', '1');
                        }])
                        ->with(['job.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                            $q->select('id','category_master_id','title','slug')
                                ->where('language_id', $lang_id)
                                ->where('is_parent', '0');
                        }]);
            } else {
                $query = FavouriteJob::query()->where('sp_id', Auth::id())->orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,email,dob,contact_number,profile_pic_path,profile_pic_thumb_path','user.cvDetail','user.studentDetail','user.educations','user.experiences','user.defaultAddress')
                ->with(['job.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '1');
                }])
                ->with(['job.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '0');
                }]);
            }

            if(!empty($request->per_page_record))
            {
                $favouriteJobs = $query->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $favouriteJobs = $query->get();
            }
            return response(prepareResult(false, $favouriteJobs, getLangByLabelGroups('messages','messages_favourite_job_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $favouriteJobs = new FavouriteJob;
            if(Auth::user()->user_type_id=='2')
            {
                $validation = Validator::make($request->all(), [
                    'job_id'       => 'required'
                ]);

                if ($validation->fails()) {
                    return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
                }

                if(FavouriteJob::where('job_id', $request->job_id)->where('sa_id', Auth::id())->count()>0)
                {
                    return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_already_exist')), config('http_response.internal_server_error'));
                }
                //student makes the job as favorite
                $favouriteJobs->job_id  = $request->job_id;
                $favouriteJobs->sa_id   = Auth::id();
            }
            else
            {
                $validation = Validator::make($request->all(), [
                    'sa_id'       => 'required'
                ]);

                if ($validation->fails()) {
                    return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
                }

                if(FavouriteJob::where('sa_id', $request->sa_id)->where('sp_id', Auth::id())->count()>0)
                {
                    return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_already_exist')), config('http_response.internal_server_error'));
                }
                //service provider makes the student as favorite
                $favouriteJobs->sa_id   = $request->sa_id;
                $favouriteJobs->sp_id   = Auth::id();
            }
            $favouriteJobs->save();
            DB::commit();
            return response()->json(prepareResult(false, $favouriteJobs, getLangByLabelGroups('messages','messages_favourite_job_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function destroy(FavouriteJob $favouriteJob)
    {
        $favouriteJob->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_favourite_job_deleted')), config('http_response.success'));
    }
}
