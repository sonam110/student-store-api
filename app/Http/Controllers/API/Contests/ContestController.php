<?php

namespace App\Http\Controllers\API\Contests;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Contest;
use App\Models\ContestCancellationRange;
use App\Models\ContestApplication; 
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use App\Models\ContactList;
use App\Models\ContestTag;
use App\Models\UserPackageSubscription;
use App\Models\OrderItem;
use App\Models\Abuse;

class ContestController extends Controller
{
    public function index(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $contests = Contest::where('user_id', Auth::id())->orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','categoryMaster','subCategory','cancellationRanges','user.serviceProviderDetail:id,user_id,company_logo_path,company_logo_thumb_path','isApplied','contestWinners')->withCount('contestApplications')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $contests = Contest::where('user_id', Auth::id())->orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','categoryMaster','subCategory','cancellationRanges','user.serviceProviderDetail:id,user_id,company_logo_path,company_logo_thumb_path','isApplied','contestWinners')->withCount('contestApplications')->get();
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
            // 'address_detail_id' 		=> 'required',
            'title'             		=> 'required',
            'type'          			=> 'required',
            'description'       		=> 'required',
            // 'application_start_date'    => 'required|date',
            // 'application_end_date'      => 'required|date|after_or_equal:application_start_date',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }
        DB::beginTransaction();
        try
        {
            // $user_package = UserPackageSubscription::where('user_id',Auth::id())->where('module','Contest')->orderBy('created_at','desc')->first();
            // if(empty($user_package))
            // {
            //     return response()->json(prepareResult(true, ['No Package Subscribed'], getLangByLabelGroups('messages','message_no_package_subscribed_error')), config('http_response.internal_server_error'));
            // }
            // elseif($user_package->number_of_contest == $user_package->used_number_of_contest)
            // {
            //     return response()->json(prepareResult(true, ['Package Use Exhausted'], getLangByLabelGroups('messages','message_job_ads_exhausted_error')), config('http_response.internal_server_error'));
            // }
            // else
            // {
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

                $start_date = date("Y-m-d", strtotime($request->start_date));
                $end_date = date("Y-m-d", strtotime($request->end_date));
                $application_start_date = date("Y-m-d", strtotime($request->application_start_date));
                $application_end_date = date("Y-m-d", strtotime($request->application_end_date)); 

                $contest = new Contest;
                $contest->user_id                   			= Auth::id();
                $contest->address_detail_id         			= $request->address_detail_id;
                $contest->registration_type_id                  = $request->registration_type_id;
                $contest->service_provider_type_id              = $request->service_provider_type_id;
                $contest->category_master_id        			= $request->category_master_id;
                $contest->sub_category_slug        				= $request->sub_category_slug;
                $contest->title                     			= $request->title;
                $contest->slug                      			= Str::slug(substr($request->title, 0, 175)).$contestNumber;
                $contest->description          					= $request->description;
                $contest->type                  				= $request->type;
                $contest->cover_image_path          			= $request->cover_image_path;
                $contest->cover_image_thumb_path                = env('CDN_DOC_THUMB_URL').basename($request->cover_image_path);
                $contest->sponsor_detail            			= $request->sponsor_detail;
                $contest->start_date                			= $start_date;
                $contest->start_time                    		= $request->start_time;
                $contest->end_time                  			= $request->end_time;
                $contest->application_start_date    			= $application_start_date;
                $contest->application_end_date          		= $application_end_date;
                $contest->max_participants              		= $request->max_participants;
                $contest->no_of_winners                 		= $request->no_of_winners;
                $contest->winner_prizes                			= json_encode($request->winner_prizes);
                $contest->mode                   				= $request->mode;
                $contest->meeting_link            				= $request->meeting_link;
                $contest->address               				= $request->address;
                $contest->target_country                        = $request->target_country;
                $contest->target_city                           = json_encode($request->target_city);
                $contest->education_level                   	= $request->education_level;
                $contest->educational_institition    			= $request->educational_institition;
                $contest->age_restriction    					= $request->age_restriction;
                $contest->min_age                   			= $request->min_age;
                $contest->max_age                   			= $request->max_age;
                $contest->others                   				= $request->others;
                $contest->condition_for_joining         		= $request->condition_for_joining;
                $contest->available_for                   		= $request->available_for;
                $contest->condition_description         		= $request->condition_description;
                $contest->condition_file_path           		= $request->condition_file_path;
                $contest->jury_members                  		= $request->jury_members;
                $contest->is_free                   			= $request->is_free;
                $contest->basic_price_wo_vat                    = $request->basic_price_wo_vat;
                $contest->subscription_fees             		= $request->subscription_fees;
                $contest->use_cancellation_policy           	= $request->use_cancellation_policy;
                $contest->provide_participation_certificate  	= $request->provide_participation_certificate;
                $contest->is_on_offer                   		= $request->is_on_offer;
                $contest->discount_type                 		= $request->discount_type;
                $contest->discount_value                		= $request->discount_value;
                $contest->discounted_price                      = $request->discounted_price;
                $contest->required_file_upload                  = $request->required_file_upload;
                $contest->file_title             				= $request->file_title;
                $contest->is_reward_point_applicable            = $request->is_reward_point_applicable;
                $contest->reward_points                  		= $request->reward_points;
                $contest->is_min_participants                   = $request->is_min_participants;
                $contest->min_participants                      = $request->min_participants;
                $contest->is_published                  		= $request->is_published;
                $contest->published_at                  		= $published_at;
                $contest->meta_title                            = $request->meta_title;
                $contest->meta_keywords                         = $request->meta_keywords;
                $contest->meta_description                      = $request->meta_description;
                $contest->is_deleted                    		= false;
                $contest->status                    			= 'pending';
                $contest->tags                         = json_encode($request->tags);
                //$contest->status                                = 'verified';
                $contest->save();

                // $user_package->update(['used_number_of_contest'=>($user_package->used_number_of_contest + 1),'used_number_of_event'=>($user_package->used_number_of_event + 1)]);

                if(!empty($request->cancellation_ranges))
                {

                	foreach ($request->cancellation_ranges as $key => $cancellation_range) 
                	{
                		if(!empty($cancellation_range["from"]) || $cancellation_range["from"] == '0')
                		{
                			$cancellation 							= new ContestCancellationRange;
                			$cancellation->contest_id 				= $contest->id;
                			$cancellation->from 					= $cancellation_range["from"];
                			$cancellation->to 						= $cancellation_range["to"];
                			$cancellation->deduct_percentage_value 	= $cancellation_range["deduct_percentage_value"];
                			$cancellation->save();
                		}
                	}
                }



                if(!empty($request->tags) && is_array($request->tags))
                {
                    foreach ($request->tags as $key => $tag) {
                        $allTypeTag = new ContestTag;
                        $allTypeTag->contest_id                 = $contest->id;
                        $allTypeTag->user_id                    = Auth::id();
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
                
            // }
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
        if($contestApplication = ContestApplication::where('application_status','!=','canceled')->where('contest_id',$contest->id)->where('user_id',Auth::id())->first())
        {
            $applied = true;
            $authApplication = $contestApplication;

        }
        else
        {
            $applied = false;
            $authApplication = null;
        }
        if($message = ContactList::where('contest_id',$contest->id)->where('buyer_id',Auth::id())->first())
        {
            $is_chat_initiated = true;
            $contactListId = $message->id;
        }
        else
        {
            $is_chat_initiated = false;
            $contactListId = null;
        }
        if($abuse = Abuse::where('contest_id',$contest->id)->where('user_id',Auth::id())->first())
        {
            $is_abuse_reported = true;
        }
        else
        {
            $is_abuse_reported = false;
        }
        $contest = Contest::with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path,show_email,show_contact_number','categoryMaster','subCategory','cancellationRanges','user.serviceProviderDetail:id,user_id,company_logo_path,company_logo_thumb_path','contestWinners')->withCount('contestApplications')->find($contest->id);
        $contest['is_applied'] = $applied;
        $contest['auth_application'] = $authApplication;

        $contest['is_chat_initiated'] = $is_chat_initiated;
        $contest['contact_list_id'] = $contactListId;
        $contest['is_abuse_reported'] = $is_abuse_reported;
        $contest['latitude'] = $contest->addressDetail? $contest->addressDetail->latitude:null;
        $contest['longitude'] = $contest->addressDetail?$contest->addressDetail->longitude:null;
        $diff_in_hours = \Carbon\Carbon::parse($contest->start_date)->diffInHours();
        $contest['cancel_button_enabled'] = false;
        if($diff_in_hours > '24' && $contest->status != 'rejected')
        {
            $contest['cancel_button_enabled'] = true;
        }

        return response()->json(prepareResult(false, $contest, getLangByLabelGroups('messages','messages_contest_list')), config('http_response.success'));
    }

    public function update(Request $request, Contest $contest)
    {
        $validation = Validator::make($request->all(), [
            'title'             		=> 'required',
            'type'          			=> 'required',
            'description'       		=> 'required',
            // 'application_start_date'    => 'required|date',
            // 'application_end_date'      => 'required|date|after_or_equal:application_start_date',
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

            $contest->user_id                   			= Auth::id();
            $contest->address_detail_id         			= $request->address_detail_id;
            $contest->registration_type_id                  = $request->registration_type_id;
            $contest->service_provider_type_id              = $request->service_provider_type_id;
            $contest->category_master_id        			= $request->category_master_id;
            $contest->sub_category_slug        				= $request->sub_category_slug;
            $contest->title                     			= $request->title;
            $contest->slug                      			= Str::slug(substr($request->title, 0, 175)).$contestNumber;
            $contest->description          					= $request->description;
            $contest->type                  				= $request->type;
            $contest->cover_image_path          			= $request->cover_image_path;
            $contest->cover_image_thumb_path                = env('CDN_DOC_THUMB_URL').basename($request->cover_image_path);
            $contest->sponsor_detail            			= $request->sponsor_detail;
            $contest->start_date                			= $start_date;
            $contest->start_time                    		= $request->start_time;
            $contest->end_time                  			= $request->end_time;
            $contest->application_start_date    			= $application_start_date;
            $contest->application_end_date          		= $application_end_date;
            $contest->max_participants              		= $request->max_participants;
            $contest->no_of_winners                 		= $request->no_of_winners;
            $contest->winner_prizes                			= json_encode($request->winner_prizes);
            $contest->mode                   				= $request->mode;
            $contest->meeting_link            				= $request->meeting_link;
            $contest->address               				= $request->address;
            $contest->target_country                        = $request->target_country;
            $contest->target_city                           = json_encode($request->target_city);
            $contest->education_level                   	= $request->education_level;
            $contest->educational_institition    			= $request->educational_institition;
            $contest->age_restriction    					= $request->age_restriction;
            $contest->min_age                   			= $request->min_age;
            $contest->max_age                   			= $request->max_age;
            $contest->others                   				= $request->others;
            $contest->condition_for_joining         		= $request->condition_for_joining;
            $contest->available_for                   		= $request->available_for;
            $contest->condition_description         		= $request->condition_description;
            $contest->condition_file_path           		= $request->condition_file_path;
            $contest->jury_members                  		= $request->jury_members;
            $contest->is_free                   			= $request->is_free;
            $contest->basic_price_wo_vat                     = $request->basic_price_wo_vat;
            $contest->subscription_fees             		= $request->subscription_fees;
            $contest->use_cancellation_policy           	= $request->use_cancellation_policy;
            $contest->provide_participation_certificate  	= $request->provide_participation_certificate;
            $contest->is_on_offer                   		= $request->is_on_offer;
            $contest->discount_type                 		= $request->discount_type;
            $contest->discount_value                		= $request->discount_value;
            $contest->discounted_price                      = $request->discounted_price;
            $contest->required_file_upload                  = $request->required_file_upload;
            $contest->file_title             				= $request->file_title;
            $contest->is_reward_point_applicable            = $request->is_reward_point_applicable;
            $contest->reward_points                  		= $request->reward_points;
            $contest->is_min_participants                   = $request->is_min_participants;
            $contest->min_participants                      = $request->min_participants;
            $contest->is_published                  		= $request->is_published;
            $contest->published_at                  		= $published_at;
            $contest->is_deleted                    		= false;
            $contest->status                    			= $request->status;
            $contest->meta_title                            = $request->meta_title;
            $contest->meta_keywords                         = $request->meta_keywords;
            $contest->meta_description                      = $request->meta_description;
            $contest->tags                         = json_encode($request->tags);
            $contest->save();
            if(!empty($request->cancellation_ranges))
            {
            	ContestCancellationRange::where('contest_id',$contest->id)->delete();
            	foreach ($request->cancellation_ranges as $key => $cancellation_range) 
            	{
            		if(!empty($cancellation_range["from"]) || $cancellation_range["from"] == '0')
            		{
            			$cancellation 							= new ContestCancellationRange;
            			$cancellation->contest_id 				= $contest->id;
            			$cancellation->from 					= $cancellation_range["from"];
            			$cancellation->to 						= $cancellation_range["to"];
            			$cancellation->deduct_percentage_value 	= $cancellation_range["deduct_percentage_value"];
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
                    $allTypeTag->user_id                  = Auth::id();
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

    public function destroy(Contest $contest)
    {
        if($contest->contestApplications->count() > 0)
        {
            return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','messages_contest_applicatons_exists')), config('http_response.success'));
        }
        $contest->delete();
        ContestApplication::where('contest_id',$contest->id)->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_contest_deleted')), config('http_response.success'));
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
        if($request->action=='promote') 
        {
            $validation = Validator::make($request->all(), [
                'is_promoted'           => 'required|boolean',
                'promotion_start_date'  => 'required|date',
                'promotion_end_date'    => 'required|date|after_or_equal:promotion_start_date',
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
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
            }

            //For Update contest status
            if($request->action=='update-status')
            {
                if($request->status == 'canceled')
                {
                    $joinedApplications = ContestApplication::where('contest_id',$contest_id)->where('application_status','joined')->get();
                    $joinedContestApplicationId = [];
                    foreach ($joinedApplications as $key => $value) {
                        $joinedContestApplicationId[] = $value->id;
                    }
                    $joinedApplicationsStatusUpdate = ContestApplication::where('contest_id',$contest_id)
                                                ->where('application_status','joined')
                                                ->update([
                                                    'application_status'=>'canceled',
                                                    'reason_for_cancellation' => $request->reason_for_cancellation,
                                                    'reason_id_for_cancellation' => $request->reason_id_for_cancellation,
                                                    'cancelled_by' => Auth::id(),
                                                ]);
                    $orderedItems = OrderItem::whereIn('contest_application_id',$joinedContestApplicationId)->get();
                    foreach ($orderedItems as $key => $orderItem) {
                        $refundOrderItemId = $orderItem->id;
                        $refundOrderItemPrice = $orderItem->price;
                        $refundOrderItemQuantity = $orderItem->quantity;
                        $refundOrderItemReason = 'cancellation';
                        refund($refundOrderItemId,$refundOrderItemPrice,$refundOrderItemQuantity,$refundOrderItemReason);
                    }

                    $getContest->reason_for_cancellation = $request->reason_for_cancellation;
                    $getContest->reason_id_for_cancellation = $request->reason_id_for_cancellation;
                }
                $getContest->status = $request->status;
                if($request->status == 'completed')
                {
                    ContestApplication::where('contest_id',$contest_id)->where('application_status','joined')->update(['application_status'=>'completed']);
                }
            }
            if($request->action=='publish') 
            {
                $getContest->is_published = $request->is_published;
                if($request->is_published == true)
                {
                    $getContest->published_at = date('Y-m-d');
                }
            }
            if($request->action=='promote') 
            {
                $getContest->is_promoted = $request->is_promoted;
                $getContest->promotion_start_date   = $request->promotion_start_date;
                $getContest->promotion_end_date     = $request->promotion_end_date;
            }
            $getContest->save();

            DB::commit();
            return response()->json(prepareResult(false, $getContest, getLangByLabelGroups('messages','messages_contest_'.$request->action)), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function contestApplications(Request $request, $id)
    {
    	try
    	{
    	    if(!empty($request->per_page_record))
    	    {
    	        $contestApplications = ContestApplication::where('contest_id',$id)->orderBy('created_at','DESC')->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path,email,contact_number','user.defaultAddress:id,user_id,full_address')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
    	    }
    	    else
    	    {
    	        $contestApplications = contestApplication::where('contest_id',$id)->orderBy('created_at','DESC')->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path,email,contact_number','user.defaultAddress:id,user_id,full_address')->get();
    	    }
    	    return response(prepareResult(false, $contestApplications, getLangByLabelGroups('messages','messages_contest_application_list')), config('http_response.success'));
    	}
    	catch (\Throwable $exception) 
    	{
    	    return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }

    public function contestFilter(Request $request)
    {
        try
        {
            $searchType = $request->searchType; //filter, promotions, latest, closingSoon, random, criteria contest
            
            $user_type_id = '3';
            if($request->user_type == 'student')
            {
            	$user_type_id = '2';
            }

            // return $request->all();

            $contests = Contest::select('contests.*')
            		->join('users', function ($join) use ($user_type_id) {
            		    $join->on('contests.user_id', 'users.id')
            			->where('users.user_type_id', $user_type_id);
            		})
                    ->orderBy('contests.created_at','DESC')
                    // ->where('contests.user_id', '!=', Auth::id())
            		->where('contests.type', $request->type)
                    ->where('contests.is_published', '1')
                    ->where('contests.status', 'verified')
                    ->where('contests.application_start_date','<=', date('Y-m-d'))
                    ->where('contests.application_end_date','>=', date('Y-m-d'))
                    ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','addressDetail','categoryMaster','subCategory','cancellationRanges','user.serviceProviderDetail:id,user_id,company_logo_path,company_logo_thumb_path','isApplied','contestWinners');
            if($searchType=='only_category_filter')
            {
                if(!empty($request->category_master_id))
                {
                    $contests->where('category_master_id',$request->category_master_id);
                }
            }
            elseif($searchType=='filter')
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
                // if(!empty($request->free_subscription))
                // {
                //     $contests->where('is_free', $request->free_subscription);
                // }
                ($request->free_subscription) ? 
                    $contests->where('is_free', 1) : $contests->where('is_free' , 0);

                ($request->free_cancellation) ? 
                    $contests->where('use_cancellation_policy', 0) : $contests->where('use_cancellation_policy' , 1);
                

                // if($request->free_cancellation)
                // {
                //     // return "dffg";
                //     $contests->where('use_cancellation_policy', 0);
                // }
                // if(!$request->free_cancellation)
                // {
                //     $contests->where('use_cancellation_policy' , 1);
                // }

                if(!empty($request->available_for))
                {
                    $contests->where('available_for', $request->available_for);
                }

                if(!empty($request->search_title))
                {
                    $contests->where('title', 'LIKE', '%'.$request->search_title.'%');
                }

                //future: distance filter implement
                /*if(!empty($request->distance))
                {
                    $contests->where('distance', $request->distance);
                }*/
                if(!empty($request->city))
                {
                    $contests->where('target_city','LIKE', '%'.$request->city.'%');
                }
                if(!empty($request->user_id))
                {
                    $contests->where('user_id', $request->user_id);
                }
                $contests->where('application_start_date','<=', date('Y-m-d'))
                    ->where('application_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='promotions')
            {
                // $contests->where('is_promoted', '1')
                //     ->where('promotion_start_date','<=', date('Y-m-d'))
                //     ->where('promotion_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='most-popular')
            {
                $contests->where('application_start_date','<=', date('Y-m-d'))
                    ->where('application_end_date','>=', date('Y-m-d'));
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

    public function contestLandingPage(Request $request)
    {
        $content = new Request();
        $content->searchType = 'promotions';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $contests_promotions = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'most-popular';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $contests_most_popular = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'latest';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $contests_latest = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'closingSoon';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $contests_closing_soon = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'random';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $contests_random = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'promotions';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $events_promotions = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'most-popular';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $events_most_popular = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'latest';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $events_latest = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'closingSoon';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $events_closing_soon = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'random';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $events_random = $this->contestFilter($content);
        
        $returnObj = [
        		'contests' => [
        		    'contests_promotions'  		=> $contests_promotions, 
        		    'contests_closing_soon'  	=> $contests_closing_soon,
        		    'contests_random'        	=> $contests_random, 
        		    'contests_latest'        	=> $contests_latest,
        		    'contests_most_popular'  	=> $contests_most_popular, 
        		],
                'events' => [
                    'events_promotions'  	=> $events_promotions, 
                    'events_closing_soon'  	=> $events_closing_soon,
                    'events_random'        	=> $events_random, 
                    'events_latest'        	=> $events_latest,
                    'events_most_popular'  	=> $events_most_popular, 
                ]
            ];
        
        return response(prepareResult(false, $returnObj, getLangByLabelGroups('messages','messages_contests_list')), config('http_response.success'));
    }

    public function studentContestLandingPage(Request $request)
    {
        $content = new Request();
        $content->searchType = 'promotions';
        $content->user_type = 'student';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $contests_promotions = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'most-popular';
        $content->user_type = 'student';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $contests_most_popular = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'latest';
        $content->user_type = 'student';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $contests_latest = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'closingSoon';
        $content->user_type = 'student';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $contests_closing_soon = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'random';
        $content->user_type = 'student';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $contests_random = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'promotions';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $events_promotions = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'most-popular';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $events_most_popular = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'latest';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $events_latest = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'closingSoon';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $events_closing_soon = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'random';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $events_random = $this->contestFilter($content);
        
        
        
        $returnObj = [

        		'contests' => [
        		    'contests_promotions'  		=> $contests_promotions, 
        		    'contests_closing_soon'  	=> $contests_closing_soon,
        		    'contests_random'        	=> $contests_random, 
        		    'contests_latest'        	=> $contests_latest,
        		    'contests_most_popular'  	=> $contests_most_popular, 
        		],
                'events' => [
                    'events_promotions'  	=> $events_promotions, 
                    'events_closing_soon'  	=> $events_closing_soon,
                    'events_random'        	=> $events_random, 
                    'events_latest'        	=> $events_latest,
                    'events_most_popular'  	=> $events_most_popular, 
                ]
            ];
        
        return response(prepareResult(false, $returnObj, getLangByLabelGroups('messages','messages_contests_list')), config('http_response.success'));
    }
}
