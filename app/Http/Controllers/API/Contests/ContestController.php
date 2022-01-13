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
use App\Models\NotificationTemplate;
use App\Models\RatingAndFeedback;
use App\Models\Language;
use App\Models\CategoryMaster;

class ContestController extends Controller
{
    function __construct()
    {
        $this->lang_id = Language::select('id')->first()->id;
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
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            if(!empty($request->per_page_record))
            {
                $contests = Contest::where('user_id', Auth::id())->orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','categoryMaster','subCategory','cancellationRanges','user.serviceProviderDetail:id,user_id,company_logo_path,company_logo_thumb_path','isApplied','contestWinners')
                ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '1');
                }])
                ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '0');
                }])
                ->withCount('contestApplications')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $contests = Contest::where('user_id', Auth::id())->orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','categoryMaster','subCategory','cancellationRanges','user.serviceProviderDetail:id,user_id,company_logo_path,company_logo_thumb_path','isApplied','contestWinners')
                ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '1');
                }])
                ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '0');
                }])
                ->withCount('contestApplications')->get();
            }
            return response(prepareResult(false, $contests, getLangByLabelGroups('messages','messages_contest_list')), config('http_response.success'));
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
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }
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

                //update price
                $amount = $request->basic_price_wo_vat;
                $is_on_offer = $request->is_on_offer;
                $discount_type = $request->discount_type;
                $discount_value = $request->discount_value;
                $vat_percentage = 0;
                $catVatId = CategoryMaster::select('vat')->find($request->category_master_id);
                if($catVatId)
                {
                    $vat_percentage = $catVatId->vat;
                }
                $user_id = Auth::id();
                
                $getCommVal = updateCommissions($amount, $is_on_offer, $discount_type, $discount_value, $vat_percentage, $user_id, $request->type);

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
                $contest->start_date                            = $start_date;
                $contest->end_date                			    = $end_date;
                $contest->start_time                    		= $request->start_time;
                $contest->end_time                  			= $request->end_time;
                $contest->application_start_date    			= $application_start_date;
                $contest->application_end_date          		= $application_end_date;
                $contest->max_participants              		= $request->max_participants;
                $contest->no_of_winners                 		= $request->no_of_winners;
                $contest->winner_prizes                			= (!empty($request->winner_prizes)) ? json_encode($request->winner_prizes) : null;
                $contest->mode                   				= $request->mode;
                $contest->meeting_link            				= $request->meeting_link;
                $contest->address               				= $request->address;
                $contest->target_country                        = $request->target_country;
                $contest->target_city                           = (!empty($request->target_city)) ? json_encode($request->target_city) : null;
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
                $contest->subscription_fees             		= $getCommVal['price_with_all_com_vat'];
                $contest->use_cancellation_policy           	= $request->use_cancellation_policy;
                $contest->provide_participation_certificate  	= $request->provide_participation_certificate;
                $contest->is_on_offer                   		= $request->is_on_offer;
                $contest->discount_type                 		= $request->discount_type;
                $contest->discount_value                		= $request->discount_value;
                $contest->discounted_price                      = $getCommVal['totalAmount'];

                $contest->vat_percentage = $vat_percentage;
                $contest->vat_amount = $getCommVal['vat_amount'];
                $contest->ss_commission_percent = $getCommVal['ss_commission_percent'];
                $contest->ss_commission_amount = $getCommVal['ss_commission_amount'];

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
                $contest->tags                         = (!empty($request->tags)) ? json_encode($request->tags) : null;
                //$contest->status                                = 'verified';
                $contest->save();

                // $user_package->update(['used_number_of_contest'=>($user_package->used_number_of_contest + 1),'used_number_of_event'=>($user_package->used_number_of_event + 1)]);

                if(!empty($request->cancellation_ranges))
                {
                    foreach ($request->cancellation_ranges as $key => $cancellation_range) 
                	{
                        if(!empty($cancellation_range["from"]) && !empty($cancellation_range["to"]) && !empty($cancellation_range["deduct_percentage_value"]))
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
               
            DB::commit();
            $contest = Contest::with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','categoryMaster','subCategory','addressDetail','cancellationRanges','user.serviceProviderDetail:id,user_id,company_logo_path,company_logo_thumb_path')
            ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
            ->find($contest->id);
            return response()->json(prepareResult(false, $contest, getLangByLabelGroups('messages','messages_contest_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function show(Request $request, Contest $contest)
    {
        $lang_id = $this->lang_id;
        if(empty($lang_id))
        {
            $lang_id = Language::select('id')->first()->id;
        }
        $authApplication = null;
        if(!empty($request->contest_application_id))
        {
            $contestApplication = ContestApplication::with('orderItem:id,contest_application_id,price,used_item_reward_points,price_after_apply_reward_points,item_status,canceled_refunded_amount,returned_rewards,earned_reward_points,reward_point_status')->where('id',$request->contest_application_id)->where('user_id', Auth::id())->first();
            $applied = true;
            $authApplication = $contestApplication;
        }
        elseif($contestApplication = ContestApplication::with('orderItem:id,contest_application_id,price,used_item_reward_points,price_after_apply_reward_points,item_status,canceled_refunded_amount,returned_rewards,earned_reward_points,reward_point_status')->where('application_status','!=','canceled')->where('contest_id',$contest->id)->where('user_id',Auth::id())->orderBy('auto_id','DESC')->first())
        {
            $applied = true;
            $authApplication = $contestApplication;

        }
        else
        {
            $applied = false;
            $authApplication = null;
        }

        if(RatingAndFeedback::where('contest_id',$contest->id)->where('from_user',Auth::id())->count() > 0)
        {
            $rated = true;

        }
        else
        {
            $rated = false;
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
        $contest = Contest::with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path,show_email,show_contact_number','user.defaultAddress:id,user_id,full_address','categoryMaster','subCategory','cancellationRanges','user.serviceProviderDetail:id,user_id,company_logo_path,company_logo_thumb_path,avg_rating','user.studentDetail:id,user_id,avg_rating','contestWinners')
        ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '1');
        }])
        ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '0');
        }])
        ->with(['serviceProviderType.serviceProviderTypeDetail' => function($q) use ($lang_id) {
            $q->select('id','service_provider_type_id','title','slug')
                ->where('language_id', $lang_id);
        }])
        ->with(['registrationType.registrationTypeDetail' => function($q) use ($lang_id) {
            $q->select('id','registration_type_id','title','slug')
                ->where('language_id', $lang_id);
        }])
        ->withCount('contestApplications','ratings')
        ->with(['ratings.customer' => function($query){
                        $query->take(3);
                    }])
        ->find($contest->id);
        $contest['is_applied'] = $applied;
        $contest['is_rated'] = $rated;
        $contest['auth_application'] = $authApplication;

        $contest['is_chat_initiated'] = $is_chat_initiated;
        $contest['contact_list_id'] = $contactListId;
        $contest['is_abuse_reported'] = $is_abuse_reported;
        $contest['latitude'] = $contest->addressDetail? $contest->addressDetail->latitude:null;
        $contest['longitude'] = $contest->addressDetail?$contest->addressDetail->longitude:null;
        $diff_in_hours = \Carbon\Carbon::parse($contest->start_date)->diffInHours();
        $contest['cancel_button_enabled'] = false;
        if($diff_in_hours >= 24 && ($contest->status == 'pending' || $contest->status == 'verified' || $contest->status == 'hold'))
        {
            $contest['cancel_button_enabled'] = true;
        }

        if($contest->user_id==auth()->id())
        {
            $contestCal = OrderItem::where('contest_id', $contest->id);
            $contest['total_ordered_amount'] = round($contestCal->sum(\DB::raw('order_items.price * order_items.quantity')), 2);
            $contest['total_canceled_refunded_amount'] = round($contestCal->sum('canceled_refunded_amount'), 2);
            $contest['total_earned_reward_points'] = round($contestCal->sum('earned_reward_points'), 2);
            $contest['total_amount_transferred_to_vendor'] = round($contestCal->sum('amount_transferred_to_vendor'), 2);
            $contest['total_student_store_commission'] = round($contestCal->sum('student_store_commission'), 2);
            $contest['total_cool_company_commission'] = round($contestCal->sum('cool_company_commission'), 2);
            $contest['total_vat_amount'] = round($contestCal->sum('vat_amount'), 2);
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

        if($contest->contestApplications->count()>0 && $contest->status!='hold')
        {
            return response()->json(prepareResult(true, 'You cannot edit this because some users are already participate this contest.', getLangByLabelGroups('messages','message_contest_completed_update')), config('http_response.success'));
        }

        if($contest->status=='completed')
        {
            return response()->json(prepareResult(true, 'Contest is comppleted so you can not update this contest after completion.', getLangByLabelGroups('messages','message_contest_completed_cannot_update')), config('http_response.success'));
        }

        DB::beginTransaction();
        try
        {
            if($contest->user_id != Auth::id())
            {
                return response(prepareResult(true, [], getLangByLabelGroups('messages','message_unauthorized')), config('http_response.unauthorized'));
            }

            $contestNumber = $contest->auto_id;

            if($request->is_published == true)
            { 
                $published_at = date('Y-m-d');
            }
            else
            {
                $published_at = null;
            }

            //update price
            $amount = $request->basic_price_wo_vat;
            $is_on_offer = $request->is_on_offer;
            $discount_type = $request->discount_type;
            $discount_value = $request->discount_value;
            $vat_percentage = 0;
            $catVatId = CategoryMaster::select('vat')->find($request->category_master_id);
            if($catVatId)
            {
                $vat_percentage = $catVatId->vat;
            }
            $user_id = Auth::id();
            
            $getCommVal = updateCommissions($amount, $is_on_offer, $discount_type, $discount_value, $vat_percentage, $user_id, $request->type);

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
            $contest->end_date                              = $end_date;
            $contest->start_time                    		= $request->start_time;
            $contest->end_time                  			= $request->end_time;
            $contest->application_start_date    			= $application_start_date;
            $contest->application_end_date          		= $application_end_date;
            $contest->max_participants              		= $request->max_participants;
            $contest->no_of_winners                 		= $request->no_of_winners;
            $contest->winner_prizes                			= (!empty($request->winner_prizes)) ? json_encode($request->winner_prizes) : null;
            $contest->mode                   				= $request->mode;
            $contest->meeting_link            				= $request->meeting_link;
            $contest->address               				= $request->address;
            $contest->target_country                        = $request->target_country;
            $contest->target_city                           = (!empty($request->target_city)) ? json_encode($request->target_city) : null;
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
            $contest->subscription_fees                     = $getCommVal['price_with_all_com_vat'];
            $contest->use_cancellation_policy           	= $request->use_cancellation_policy;
            $contest->provide_participation_certificate  	= $request->provide_participation_certificate;
            $contest->is_on_offer                   		= $request->is_on_offer;
            $contest->discount_type                 		= $request->discount_type;
            $contest->discount_value                		= $request->discount_value;
            $contest->discounted_price                      = $getCommVal['totalAmount'];

            $contest->vat_percentage = $vat_percentage;
            $contest->vat_amount = $getCommVal['vat_amount'];
            $contest->ss_commission_percent = $getCommVal['ss_commission_percent'];
            $contest->ss_commission_amount = $getCommVal['ss_commission_amount'];
                
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
            $contest->tags                         = (!empty($request->tags)) ? json_encode($request->tags) : null;
            $contest->save();
            if($request->use_cancellation_policy)
            {
            	ContestCancellationRange::where('contest_id',$contest->id)->delete();
            	foreach ($request->cancellation_ranges as $key => $cancellation_range) 
            	{
                    if((!empty($cancellation_range["from"]) || $cancellation_range["from"] == '0') && !empty($cancellation_range["to"]) && !empty($cancellation_range["deduct_percentage_value"]))
                    {
                		$cancellation                             = new ContestCancellationRange;
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
            \Log::error($exception);
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
                    
                    $orderedItems = OrderItem::whereIn('contest_application_id',$joinedContestApplicationId)->where('item_status','!=', 'canceled')->get();
                    foreach ($orderedItems as $key => $orderedItem) {
                        $refundOrderItemId = $orderedItem->id;
                        $refundOrderItemPrice = $orderedItem->price_after_apply_reward_points;
                        $refundOrderItemQuantity = $orderedItem->quantity;
                        $refundOrderItemReason = 'cancellation';

                        $isRefunded = refund($refundOrderItemId,$refundOrderItemPrice,$refundOrderItemQuantity,$refundOrderItemReason);

                        if($isRefunded=='failed')
                        {
                            return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
                        }

                        $orderedItem->canceled_refunded_amount = $refundOrderItemPrice * $refundOrderItemQuantity;
                        $orderedItem->canceled_refunded_amount = $refundOrderItemPrice * $refundOrderItemQuantity;
                        $orderedItem->returned_rewards = ceil($orderedItem->used_item_reward_points / $refundOrderItemQuantity);
                        $orderedItem->earned_reward_points = 0;
                        $orderedItem->reward_point_status = 'completed';
                        $orderedItem->item_status = 'canceled';

                        $orderedItem->save();
                    }

                    $joinedApplicationsStatusUpdate = ContestApplication::where('contest_id',$contest_id)
                        ->where('application_status','joined')
                        ->update([
                            'application_status'=>'canceled',
                            'reason_for_cancellation' => $request->reason_for_cancellation,
                            'reason_id_for_cancellation' => $request->reason_id_for_cancellation,
                            'cancelled_by' => Auth::id(),
                        ]);

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
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function contestApplications(Request $request, $id)
    {
    	try
    	{
    	    if(!empty($request->per_page_record))
    	    {
    	        $contestApplications = ContestApplication::where('contest_id',$id)->orderBy('created_at','DESC')->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path,email,contact_number','user.defaultAddress:id,user_id,full_address','orderItem:id,contest_application_id,price,used_item_reward_points,price_after_apply_reward_points,item_status,canceled_refunded_amount,returned_rewards,earned_reward_points,reward_point_status')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
    	    }
    	    else
    	    {
    	        $contestApplications = ContestApplication::where('contest_id',$id)->orderBy('created_at','DESC')->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path,email,contact_number','user.defaultAddress:id,user_id,full_address','orderItem:id,contest_application_id,price,used_item_reward_points,price_after_apply_reward_points,item_status,canceled_refunded_amount,returned_rewards,earned_reward_points,reward_point_status')->get();
    	    }
    	    return response(prepareResult(false, $contestApplications, getLangByLabelGroups('messages','messages_contest_application_list')), config('http_response.success'));
    	}
    	catch (\Throwable $exception) 
    	{
    	    \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }

    public function contestFilter(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

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
                    /*->where('contests.application_start_date','<=', date('Y-m-d'))
                    ->where('contests.application_end_date','>=', date('Y-m-d'))*/
                    ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','addressDetail','categoryMaster','subCategory','cancellationRanges','user.serviceProviderDetail:id,user_id,company_logo_path,company_logo_thumb_path','isApplied','contestWinners')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
            if($searchType=='only_category_filter')
            {
                if(!empty($request->category_master_id))
                {
                    $contests->where('category_master_id',$request->category_master_id);
                }
            }
            elseif($searchType=='filter')
            {
                if(!empty($request->available_for))
                {
                    $available_for = $request->available_for;
                    $contests->where(function ($query) use ($available_for) {
                        $query->whereNull('contests.available_for')
                              ->orWhere('contests.available_for', 'all')
                              ->orWhere('contests.available_for', $available_for);
                    });
                }

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
                    $contests->whereDate('application_start_date', '>=', date('Y-m-d', strtotime($request->start_date)));
                }
                if(!empty($request->end_date))
                {
                    $contests->whereDate('application_end_date', '<=', date('Y-m-d', strtotime($request->end_date)));
                }

                if(!empty($request->free_subscription))
                {
                    $contests->where('is_free', 1);
                }

                if(!empty($request->free_cancellation))
                {
                    $contests->where('use_cancellation_policy' , 0);
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
                $contests->whereBetween('application_end_date', [date('Y-m-d', strtotime("-1 days")), date('Y-m-d', strtotime("+2 days"))]);
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
            \Log::error($exception);
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
        $content->lang_id = $request->lang_id;
        $contests_promotions = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'most-popular';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_most_popular = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'latest';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_latest = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'closingSoon';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_closing_soon = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'random';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_random = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'promotions';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_promotions = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'most-popular';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_most_popular = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'latest';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_latest = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'closingSoon';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_closing_soon = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'random';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
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
        $content->lang_id = $request->lang_id;
        $contests_promotions = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'most-popular';
        $content->user_type = 'student';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_most_popular = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'latest';
        $content->user_type = 'student';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_latest = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'closingSoon';
        $content->user_type = 'student';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_closing_soon = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'random';
        $content->user_type = 'student';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_random = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'promotions';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_promotions = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'most-popular';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_most_popular = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'latest';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_latest = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'closingSoon';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_closing_soon = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'random';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
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
