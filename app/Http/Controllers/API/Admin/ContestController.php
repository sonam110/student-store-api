<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contest;
use App\Models\User;
use Str;
use DB;
use Auth;
use Validator;
use App\Models\ContestApplication;
use App\Models\ContestCancellationRange;
use App\Models\UserPackageSubscription;
use App\Models\ContestTag;

class ContestController extends Controller
{
    public function index(Request $request)
    {
        try
        {
             if(!empty($request->per_page_record))
            {
                $contests = Contest::where('is_published', '1')->orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','cancellationRanges')->withCount('contestApplications')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $contests = Contest::where('is_published', '1')->orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','cancellationRanges')->withCount('contestApplications')->get();
            }
            return response(prepareResult(false, $contests, getLangByLabelGroups('messages','messages_contest_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function store(Request $request)
    {  
        $validation = Validator::make($request->all(), [
            'title'                     => 'required',
            'type'                      => 'required',
            'description'               => 'required',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }
        DB::beginTransaction();
        try
        {
            $getLastContest = Contest::select('id')->orderBy('created_at','DESC')->first();
            if($getLastContest) {
                $contestNumber = $getLastContest->auto_id;
            } else {
                $contestNumber = 1;
            }

            if($request->is_published == true)
            { 
                $published_at = date('Y-m-d');
            }
            else
            {
                $published_at = null;
            }

            if(!empty($request->user_id))
            {
                $user_id = $request->user_id;
            }
            else
            {
                $user_id = Auth::id();
            }

            $start_date = date("Y-m-d", strtotime($request->start_date));
            $end_date = date("Y-m-d", strtotime($request->end_date));
            $application_start_date = date("Y-m-d", strtotime($request->application_start_date));
            $application_end_date = date("Y-m-d", strtotime($request->application_end_date)); 

            $contest = new Contest;
            $contest->user_id                               = $user_id;
            $contest->address_detail_id                     = $request->address_detail_id;
            $contest->registration_type_id                  = $request->registration_type_id;
            $contest->service_provider_type_id              = $request->service_provider_type_id;
            $contest->category_master_id                    = $request->category_master_id;
            $contest->sub_category_slug                     = $request->sub_category_slug;
            $contest->title                                 = $request->title;
            $contest->slug                                  = Str::slug(substr($request->title, 0, 175)).$contestNumber;
            $contest->description                           = $request->description;
            $contest->type                                  = $request->type;
            $contest->cover_image_path                      = $request->cover_image_path;
            $contest->cover_image_thumb_path                = env('CDN_DOC_THUMB_URL').basename($request->cover_image_path);
            $contest->sponsor_detail                        = $request->sponsor_detail;
            $contest->start_date                            = $start_date;
            $contest->start_time                            = $request->start_time;
            $contest->end_time                              = $request->end_time;
            $contest->application_start_date                = $application_start_date;
            $contest->application_end_date                  = $application_end_date;
            $contest->max_participants                      = $request->max_participants;
            $contest->no_of_winners                         = $request->no_of_winners;
            $contest->winner_prizes                         = json_encode($request->winner_prizes);
            $contest->mode                                  = $request->mode;
            $contest->meeting_link                          = $request->meeting_link;
            $contest->address                               = $request->address;
            $contest->target_country                        = $request->target_country;
            $contest->target_city                           = json_encode($request->target_city);
            $contest->education_level                       = $request->education_level;
            $contest->educational_institition               = $request->educational_institition;
            $contest->age_restriction                       = $request->age_restriction;
            $contest->min_age                               = $request->min_age;
            $contest->max_age                               = $request->max_age;
            $contest->others                                = $request->others;
            $contest->condition_for_joining                 = $request->condition_for_joining;
            $contest->available_for                         = $request->available_for;
            $contest->condition_description                 = $request->condition_description;
            $contest->condition_file_path                   = $request->condition_file_path;
            $contest->jury_members                          = $request->jury_members;
            $contest->is_free                               = $request->is_free;
            $contest->subscription_fees                     = $request->subscription_fees;
            $contest->use_cancellation_policy               = $request->use_cancellation_policy;
            $contest->provide_participation_certificate     = $request->provide_participation_certificate;
            $contest->is_on_offer                           = $request->is_on_offer;
            $contest->discount_type                         = $request->discount_type;
            $contest->discount_value                        = $request->discount_value;
            $contest->discounted_price                      = $request->discounted_price;
            $contest->required_file_upload                  = $request->required_file_upload;
            $contest->file_title                            = $request->file_title;
            $contest->is_reward_point_applicable            = $request->is_reward_point_applicable;
            $contest->reward_points                         = $request->reward_points;
            $contest->is_min_participants                   = $request->is_min_participants;
            $contest->min_participants                      = $request->min_participants;
            $contest->is_published                          = $request->is_published;
            $contest->published_at                          = $published_at;
            $contest->meta_title                            = $request->meta_title;
            $contest->meta_keywords                         = $request->meta_keywords;
            $contest->meta_description                      = $request->meta_description;
            $contest->is_deleted                            = false;
            $contest->status                                = $request->status;
            $contest->tags                         = json_encode($request->tags);
            $contest->save();
            if(!empty($request->cancellation_ranges))
            {
                foreach ($request->cancellation_ranges as $key => $cancellation_range) 
                {
                    if(!empty($cancellation_range["from"]) || $cancellation_range["from"] == '0')
                    {
                        $cancellation                           = new ContestCancellationRange;
                        $cancellation->contest_id               = $contest->id;
                        $cancellation->from                     = $cancellation_range["from"];
                        $cancellation->to                       = $cancellation_range["to"];
                        $cancellation->deduct_percentage_value  = $cancellation_range["deduct_percentage_value"];
                        $cancellation->save();
                    }
                }
            }

            if(!empty($request->tags) && is_array($request->tags))
            {
                foreach ($request->tags as $key => $tag) {
                    $allTypeTag = new ContestTag;
                    $allTypeTag->contest_id                 = $contest->id;
                    $allTypeTag->user_id                    = $user_id;
                    $allTypeTag->title                      = $tag;
                    $allTypeTag->type                       = $request->type;
                    $allTypeTag->save();
                }
            }

            // Notification Start

            

            if($request->available_for == 'students')
            {
                $users = User::where('user_type_id',2);
            }
            elseif($request->available_for == 'companies')
            {
                $users = User::where('user_type_id',3);
            }
            else
            {
                $users = User::where('user_type_id',4);
            }

            if(!empty($request->min_age))
            {
               $end_dob = date('Y-m-d',strtotime('-'.$request->min_age.' year'));
               $users = $users->where('dob', '<=', $end_dob);
            }

            if(!empty($request->max_age))
            {
               $start_dob = date('Y-m-d',strtotime('-'.$request->max_age.' year'));
               $users = $users->where('dob', '>=', $start_dob);
            }

            if(!empty($request->educational_institition))
            {
               $users = $users->join('student_details', function ($join) {
                                    $join->on('users.id', '=', 'student_details.user_id');
                                })
                              ->where('student_details.institute_name',$request->educational_institition);           
           }

            $users = $users->get();
            $title = 'New Contest Posted';
            $body =  'New Contest '.$contest->title.' Posted.';
            $type = 'Contest Posted';
            pushMultipleNotification($title,$body,$users,$type,true,'buyer','contest',$contest->id,'landing_screen');

            // Notification End
            
            
            DB::commit();
            $contest = Contest::with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','categoryMaster','subCategory','addressDetail','cancellationRanges','user.serviceProviderDetail:id,user_id,company_logo_path,company_logo_thumb_path')->find($contest->id);
            return response()->json(prepareResult(false, $contest, getLangByLabelGroups('messages','messages_contest_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function show(Contest $contest)
    {
        try
        {
            $contest_id = $contest->id;
            $contest = Contest::with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','categoryMaster','subCategory','cancellationRanges','user.serviceProviderDetail:id,user_id,company_logo_path,company_logo_thumb_path','contestWinners')->withCount('contestApplications')->find($contest->id);

            $diff_in_hours = \Carbon\Carbon::parse($contest->start_date)->diffInHours();
            $contest['cancel_button_enabled'] = false;
            if($diff_in_hours > '24' && $contest->status != 'rejected')
            {
                $contest['cancel_button_enabled'] = true;
            }
            return response(prepareResult(false, $contest, getLangByLabelGroups('messages','messages_contest_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function update(Request $request, Contest $contest)
    {
        $validation = Validator::make($request->all(), [
            'title'                     => 'required',
            'type'                      => 'required',
            'description'               => 'required',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $contestNumber = $contest->auto_id;

            if($request->is_published == true)
            { 
                $published_at = date('Y-m-d');
            }
            else
            {
                $published_at = null;
            }

            $start_date = date("Y-m-d", strtotime($request->start_date));
            $end_date = date("Y-m-d", strtotime($request->end_date));
            $application_start_date = date("Y-m-d", strtotime($request->application_start_date));
            $application_end_date = date("Y-m-d", strtotime($request->application_end_date)); 

            if(!empty($request->user_id))
            {
                $user_id = $request->user_id;
            }
            else
            {
                $user_id = Auth::id();
            }

            $contest->user_id                               = $user_id;
            $contest->address_detail_id                     = $request->address_detail_id;
            $contest->registration_type_id                  = $request->registration_type_id;
            $contest->service_provider_type_id              = $request->service_provider_type_id;
            $contest->category_master_id                    = $request->category_master_id;
            $contest->sub_category_slug                     = $request->sub_category_slug;
            $contest->title                                 = $request->title;
            $contest->slug                                  = Str::slug(substr($request->title, 0, 175)).$contestNumber;
            $contest->description                           = $request->description;
            $contest->type                                  = $request->type;
            $contest->cover_image_path                      = $request->cover_image_path;
            $contest->cover_image_thumb_path                = env('CDN_DOC_THUMB_URL').basename($request->cover_image_path);
            $contest->sponsor_detail                        = $request->sponsor_detail;
            $contest->start_date                            = $start_date;
            $contest->start_time                            = $request->start_time;
            $contest->end_time                              = $request->end_time;
            $contest->application_start_date                = $application_start_date;
            $contest->application_end_date                  = $application_end_date;
            $contest->max_participants                      = $request->max_participants;
            $contest->no_of_winners                         = $request->no_of_winners;
            $contest->winner_prizes                         = json_encode($request->winner_prizes);
            $contest->mode                                  = $request->mode;
            $contest->meeting_link                          = $request->meeting_link;
            $contest->address                               = $request->address;
            $contest->target_country                        = $request->target_country;
            $contest->target_city                           = json_encode($request->target_city);
            $contest->education_level                       = $request->education_level;
            $contest->educational_institition               = $request->educational_institition;
            $contest->age_restriction                       = $request->age_restriction;
            $contest->min_age                               = $request->min_age;
            $contest->max_age                               = $request->max_age;
            $contest->others                                = $request->others;
            $contest->condition_for_joining                 = $request->condition_for_joining;
            $contest->available_for                         = $request->available_for;
            $contest->condition_description                 = $request->condition_description;
            $contest->condition_file_path                   = $request->condition_file_path;
            $contest->jury_members                          = $request->jury_members;
            $contest->is_free                               = $request->is_free;
            $contest->subscription_fees                     = $request->subscription_fees;
            $contest->use_cancellation_policy               = $request->use_cancellation_policy;
            $contest->provide_participation_certificate     = $request->provide_participation_certificate;
            $contest->is_on_offer                           = $request->is_on_offer;
            $contest->discount_type                         = $request->discount_type;
            $contest->discount_value                        = $request->discount_value;
            $contest->discounted_price                      = $request->discounted_price;
            $contest->required_file_upload                  = $request->required_file_upload;
            $contest->file_title                            = $request->file_title;
            $contest->is_reward_point_applicable            = $request->is_reward_point_applicable;
            $contest->reward_points                         = $request->reward_points;
            $contest->is_min_participants                   = $request->is_min_participants;
            $contest->min_participants                      = $request->min_participants;
            $contest->is_published                          = $request->is_published;
            $contest->published_at                          = $published_at;
            $contest->is_deleted                            = false;
            $contest->meta_title                            = $request->meta_title;
            $contest->meta_keywords                         = $request->meta_keywords;
            $contest->meta_description                      = $request->meta_description;
            $contest->status                                = $request->status;
            $contest->tags                         = json_encode($request->tags);
            $contest->save();
            if(!empty($request->cancellation_ranges))
            {
                ContestCancellationRange::where('contest_id',$contest->id)->delete();
                foreach ($request->cancellation_ranges as $key => $cancellation_range) 
                {
                    if(!empty($cancellation_range["from"]) || $cancellation_range["from"] == '0')
                    {
                        $cancellation                           = new ContestCancellationRange;
                        $cancellation->contest_id               = $contest->id;
                        $cancellation->from                     = $cancellation_range["from"];
                        $cancellation->to                       = $cancellation_range["to"];
                        $cancellation->deduct_percentage_value  = $cancellation_range["deduct_percentage_value"];
                        $cancellation->save();
                    }
                }
            }

            if(!empty($request->tags) && is_array($request->tags))
           {

                ContestTag::where('contest_id',$contest->id)->delete();
                foreach ($request->tags as $key => $tag) {
                    $allTypeTag = new ContestTag;
                    $allTypeTag->contest_id                 = $contest->id;
                    $allTypeTag->user_id                  = $user_id;
                    $allTypeTag->title                    = $tag;
                    $allTypeTag->type                     = $request->type;
                    $allTypeTag->save();
                }
            }

            DB::commit();
            return response()->json(prepareResult(false, $contest, getLangByLabelGroups('messages','messages_contest_updated')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function contestAction($contest_id, Request $request)
    {
        if($request->action=='update-status')
        {
            $validation = Validator::make($request->all(), [
                'status'    => 'required'
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
            $getContest = Contest::find($contest_id);
            if(!$getContest)
            {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_contest_doesnt_exist')), config('http_response.internal_server_error'));
            }

            //For Update contest status
            if($request->action=='update-status')
            {
                $getContest->status = $request->status;
                if($request->status == 'rejected')
                {
                    $getContest->reason_for_rejection = $request->reason_for_rejection;
                    $getContest->reason_id_for_rejection = $request->reason_id_for_rejection;
                }

                if($request->status == 'completed')
                {
                    ContestApplication::where('contest_id',$contest_id)->where('application_status','joined')->update(['application_status'=>'completed']);
                }

                $title = 'Contest Status Updated';
                $body =  'Contest '.$getContest->title.' status has been successfully updated.';
            }
            if($request->action=='publish') 
            {
                $getContest->is_published = $request->is_published;
                if($request->is_published == true)
                {
                    $getContest->published_at = date('Y-m-d');
                }

                $title = 'Contest Status Updated';
                $body =  'Contest '.$getContest->title.' status has been successfully updated.';
            }
            
            $getContest->save();

            $type = 'Contest Action';
            pushNotification($title,$body,$getContest->user,$type,true,'creator','contest',$getContest->id,'posted-contests');

            DB::commit();
            return response()->json(prepareResult(false, $getContest, getLangByLabelGroups('messages','messages_contest_updated')), config('http_response.created'));
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
                'status'    => 'required',
                'contest_id'    => 'required'
            ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $contests = Contest::whereIn('id',$request->contest_id)->get();
            foreach ($contests as $key => $contest) {
                $contest->status = $request->status;
                if($request->status == 'rejected')
                {
                    $contest->reason_for_rejection = $request->reason_for_rejection;
                    $contest->reason_id_for_rejection = $request->reason_id_for_rejection;
                }
                
                $title = 'Contest Status Updated';
                $body =  'Contest '.$contest->title.' status has been successfully updated.';
                $contest->save();

                $type = 'Contest Action';
                pushNotification($title,$body,$contest->user,$type,true,'creator','contest',$contest->id,'my-listing');
            }

            DB::commit();
            return response()->json(prepareResult(false, $contests, getLangByLabelGroups('messages','messages_contest_updated')), config('http_response.created'));
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
                'is_published'    => 'required',
                'contest_id'    => 'required'
            ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $contests = Contest::whereIn('id',$request->contest_id)->get();
            foreach ($contests as $key => $contest) {
                $contest->is_published = $request->is_published;
                if($request->is_published == true)
                {
                    $contest->published_at = date('Y-m-d');
                }
                
                $title = 'Contest Status Updated';
                $body =  'Contest '.$contest->title.' status has been successfully updated.';
                $contest->save();

                $type = 'Contest Action';
                pushNotification($title,$body,$contest->user,$type,true,'creator','contest',$contest->id,'my-listing');
            }

            DB::commit();
            return response()->json(prepareResult(false, $contests, getLangByLabelGroups('messages','messages_contest_updated')), config('http_response.created'));
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
            $searchType = $request->searchType; //filter, promotions, latest, closingSoon, random, criteria contest

            $contests = Contest::select('contests.*')
                    // ->where('contests.user_id', '!=', Auth::id())
                    
                    // ->where('contests.is_published', '1')
                    ->orderBy('contests.created_at','DESC')
                    ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','addressDetail','categoryMaster','subCategory','cancellationRanges','user.serviceProviderDetail:id,user_id,company_logo_path,company_logo_thumb_path','isApplied','contestWinners');
            if(!empty($request->type))
            {
                $contests->where('contests.type', $request->type);
            }
            if($request->user_type == 'student')
            {
                $contests->join('users', function ($join) {
                    $join->on('contests.user_id', '=', 'users.id')
                    ->where('user_type_id', 2);
                });
            }
            elseif($request->user_type == 'company')
            {
                $contests->join('users', function ($join) {
                    $join->on('contests.user_id', '=', 'users.id')
                    ->where('user_type_id', 3);
                });
            }

            if($searchType=='filter')
            {
                if(!empty($request->category_master_id))
                {
                    $contests->where('category_master_id',$request->category_master_id);
                }
                if(!empty($request->sub_category_slug))
                {
                    $contests->where('sub_category_slug',$request->sub_category_slug);
                }
                if(!empty($request->mode))
                {
                    $contests->where('mode', $request->mode);
                }
                if(!empty($request->published_date))
                {
                    $contests->where('contests.published_at', '<=',  date("Y-m-d", strtotime($request->published_date)))->orderBy('contests.published_at','desc');
                }
                if(!empty($request->applying_date))
                {
                    $contests->where('contests.application_end_date', '<=',  date("Y-m-d", strtotime($request->applying_date)))->orderBy('contests.application_end_date','asc');
                }
                if(!empty($request->start_date))
                {
                    $contests->where('start_date', '>=', $request->start_date);
                }
                if(!empty($request->end_date))
                {
                    $contests->where('start_date', '<=', $request->end_date);
                }
                /*if(!empty($request->free_subscription))
                {
                    $contests->where('is_free', $request->free_subscription);
                }
                if(!empty($request->free_cancellation))
                {
                    $contests->where('use_cancellation_policy','!=', $request->free_cancellation);
                }*/

                /*($request->free_subscription) ? 
                    $contests->where('is_free', 1) : $contests->where('is_free' , 0);

                ($request->free_cancellation) ? 
                    $contests->where('use_cancellation_policy', 0) : $contests->where('use_cancellation_policy' , 1);*/

                if(!empty($request->available_for))
                {
                    $contests->where('available_for', $request->available_for);
                }

                if(!empty($request->title))
                {
                    $contests->where('title', 'LIKE', '%'.$request->title.'%');
                }

                //future: distance filter implement
                if(!empty($request->distance))
                {
                    $contests->where('distance', $request->distance);
                }
                if(!empty($request->city))
                {
                    $contests->where('target_city','LIKE', '%'.$request->city.'%');
                }
                if(!empty($request->status))
                {
                    $contests->where('status', $request->status);
                }
                if(!empty($request->type))
                {
                    $contests->where('type', $request->type);
                }

                if(!empty($request->user_id))
                {
                    $contests->where('user_id', $request->user_id);
                }
                // $contests->where('application_start_date','<=', date('Y-m-d'))
                //     ->where('application_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='promotions')
            {
                $contests->where('is_promoted', '1')
                    ->where('promotion_start_date','<=', date('Y-m-d'))
                    ->where('promotion_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='most-popular')
            {
                // $contests->where('is_promoted', '1')
                //     ->where('promotion_start_date','<=', date('Y-m-d'))
                //     ->where('promotion_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='latest')
            {
                $contests->orderBy('created_at','DESC')
                    ->where('application_start_date','<=', date('Y-m-d'))
                    ->where('application_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='closingSoon')
            {
                $contests->whereBetween('application_end_date', [date('Y-m-d'), date('Y-m-d', strtotime("+2 days"))]);
            }
            elseif($searchType=='random')
            {
                $contests->where('application_start_date','<=', date('Y-m-d'))
                    ->where('application_end_date','>=', date('Y-m-d'))
                    ->inRandomOrder();
            }

            if(!empty($request->per_page_record))
            {
                $contestsData = $contests->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $contestsData = $contests->get();
            }
            if($request->other_function=='yes')
            {
                return $contestsData;
            }
            return response(prepareResult(false, $contestsData, getLangByLabelGroups('messages','messages_contest_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function destroy($contest_id)
    {
        try
        {
            $contest = Contest::find($contest_id);
            if($contest->contestApplications->count() > 0)
            {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','messages_contest_applicatons_exists')), config('http_response.success'));
            }
            $contest->delete();
            return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_contest_deleted')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}
