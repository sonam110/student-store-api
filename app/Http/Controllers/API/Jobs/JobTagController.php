<?php

namespace App\Http\Controllers\API\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\JobTag;
use Auth;
use DB;

class JobTagController extends Controller
{
    public function index()
    {
        try
        {
            $jobTags = JobTag::where('title', '!=', null)->select('title')->groupBy('title')->get();
            return response(prepareResult(false, $jobTags, getLangByLabelGroups('messages','messages_job_tags_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $jobTags = new JobTag;
            if($request->type=='cv')
            {
                if(!empty($request->title))
                {
                    if(JobTag::where('title', $request->title)->where('cv_id', $request->id)->count()>0)
                    {
                        return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_already_exist')), config('http_response.internal_server_error'));
                    }
                }
                else
                {
                    if(JobTag::where('nice_to_have', $request->nice_to_have)->where('cv_id', $request->id)->count()>0)
                    {
                        return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_already_exist')), config('http_response.internal_server_error'));
                    }
                }
                $jobTags->cv_id         = $request->id;
            }
            else
            {
                if(!empty($request->title))
                {
                    if(JobTag::where('title', $request->title)->where('job_id', $request->id)->count()>0)
                    {
                        return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_already_exist')), config('http_response.internal_server_error'));
                    }
                }
                else
                {
                    if(JobTag::where('nice_to_have', $request->nice_to_have)->where('job_id', $request->id)->count()>0)
                    {
                        return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_already_exist')), config('http_response.internal_server_error'));
                    }
                }
                $jobTags->job_id        = $request->id;
            }
            $jobTags->user_id       = Auth::id();
            $jobTags->title         = $request->title;
            $jobTags->nice_to_have  = $request->nice_to_have;
            $jobTags->save();
            DB::commit();
            return response()->json(prepareResult(false, $jobTags, getLangByLabelGroups('messages','messages_favourite_job_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }
}
