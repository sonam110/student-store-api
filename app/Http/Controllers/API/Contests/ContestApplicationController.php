<?php

namespace App\Http\Controllers\API\Contests;

use App\Http\Controllers\Controller;
use App\Models\ContestApplication;
use App\Models\Contest;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use Event;
use mervick\aesEverywhere\AES256;
use App\Models\Language;

class ContestApplicationController extends Controller
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

            if(!empty($request->per_page_record))
            {
                $contestApplications = ContestApplication::where('user_id',Auth::id())->orderBy('created_at','DESC')->with('contest.user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','contest.categoryMaster','contest.subCategory','contest.cancellationRanges','user','orderItem:id,contest_application_id,order_id','orderItem.order:id,order_number')
                    ->with(['contest.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['contest.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }])
                ->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $contestApplications = ContestApplication::where('user_id',Auth::id())->orderBy('created_at','DESC')->with('contest.user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','contest.categoryMaster','contest.subCategory','contest.cancellationRanges','user','orderItem:id,contest_application_id,order_id','orderItem.order:id,order_number')
                    ->with(['contest.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['contest.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }])
                    ->get();
            }
    		return response(prepareResult(false, $contestApplications, getLangByLabelGroups('messages','message_contest_application_list')), config('http_response.success'));
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
    		'contest_id'  => 'required'
    	]);

    	if ($validation->fails()) {
    		return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
    	}

    	DB::beginTransaction();
    	try
    	{
            $contest_start_date = date("Y-m-d", strtotime($request->contest_start_date));
            $contest_end_date = date("Y-m-d", strtotime($request->contest_end_date));

            $contest = Contest::find($request->contest_id);

            if($contestApplication = ContestApplication::where('user_id',Auth::id())->where('contest_id',$contest->id)->where('application_status','!=','canceled')->first())
            {
                $contestApplication->document = $request->document;
                $contestApplication->application_status = $request->application_status;
                $contestApplication->save();

                // Notification Start

                $title = 'Document Updated';
                $body =  'New Documen uploaded by '.AES256::decrypt(Auth::user()->first_name, env('ENCRYPTION_KEY')).'for Application Received on Contest '.$contest->title;
                $user = $contest->user;
                $type = 'Contest Application';
                pushNotification($title,$body,$user,$type,true,'seller','contest',$contestApplication->id,'created');

                // Notification End
            }
            else
            {
                $contestApplication = new ContestApplication;
                $contestApplication->user_id            = Auth::id();
                $contestApplication->contest_id         = $request->contest_id;
                $contestApplication->contest_type       = $request->contest_type;
                $contestApplication->contest_title      = $request->contest_title;
                $contestApplication->application_status = $request->application_status;
                $contestApplication->subscription_status= $request->subscription_status;
                $contestApplication->subscription_remark= $request->subscription_remark;
                $contestApplication->document= $request->document;
                $contestApplication->save();

                // Notification Start

                $title = 'New Contest Application';
                $body =  'New Application Received for Contest '.$contest->title;
                $user = $contest->user;
                $type = 'Contest Application';
                pushNotification($title,$body,$user,$type,true,'seller','contest',$contestApplication->id,'created');

                // Notification End
            }
            
    		DB::commit();
    		return response()->json(prepareResult(false, $contestApplication, getLangByLabelGroups('messages','message_contest_application_created')), config('http_response.created'));
    	}
    	catch (\Throwable $exception)
    	{
    		DB::rollback();
    		\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }

    public function show(ContestApplication $contestApplication)
    {
        $lang_id = $this->lang_id;

    	$contestApplication = ContestApplication::where('id',$contestApplication->id)->with('contest.user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','contest.categoryMaster','contest.subCategory','contest.cancellationRanges','user')
        ->with(['contest.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '1');
        }])
        ->with(['contest.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '0');
        }])
        ->first();
    	return response()->json(prepareResult(false, $contestApplication, getLangByLabelGroups('messages','message_contest_application_list')), config('http_response.success'));
    }

    public function destroy(ContestApplication $contestApplication)
    {
    	$contestApplication->delete();
    	return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_contest_application_deleted')), config('http_response.success'));
    }
    

    public function statusUpdate(Request $request, $id)
    {
    	try
    	{
    		$contestApplication = ContestApplication::find($id);
            if(!$contestApplication)
            {
                return response()->json(prepareResult(true, 'No record found...', getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
            }
            if($request->application_status == 'canceled')
            {
                $contestApplication->reason_for_cancellation = $request->reason_for_cancellation;
                $contestApplication->reason_id_for_cancellation = $request->reason_id_for_cancellation;
                $contestApplication->cancelled_by = Auth::id();

                $contest = $contestApplication->contest;
                $remainingHours = \Carbon\Carbon::parse($contest->start_time)->diffInHours(now());
                if($contest->use_cancellation_policy == true)
                {
                    $notInAnyCase = false;
                    $cancellationRanges = $contest->cancellationRanges;
                    foreach ($cancellationRanges as $key => $value) {
                        if($remainingHours >= $value->from && $remainingHours < $value->to)
                        {
                            $notInAnyCase = true;
                            $orderedItem = OrderItem::where('contest_application_id',$id)->first();
                            if($orderedItem)
                            {
                                $refundOrderItemId = $orderedItem->id;
                                
                                //Update commission and reward
                                $refundOrderItemPrice = ($orderedItem->price_after_apply_reward_points) * (100 - $value->deduct_percentage_value)/100;
                                $student_store_commission = round(($orderedItem->student_store_commission * ($value->deduct_percentage_value)/100), 2);
                                $cool_company_commission = round(($orderedItem->cool_company_commission * ($value->deduct_percentage_value)/100), 2);

                                
                                $orderedItem->returned_rewards = ceil($orderedItem->used_item_reward_points * (100 - $value->deduct_percentage_value)/100);
                                $orderedItem->student_store_commission = $student_store_commission;
                                $orderedItem->cool_company_commission = $cool_company_commission;

                                $remainingAmount = round(($orderedItem->price - $refundOrderItemPrice), 2);

                                $orderedItem->vat_amount = round((($remainingAmount * $orderedItem->vat_percent)/100), 2);

                                $orderedItem->amount_transferred_to_vendor = round($orderedItem->price - ($refundOrderItemPrice + $student_store_commission + $cool_company_commission), 2);

                                $orderedItem->save();

                                $refundOrderItemQuantity = $orderedItem->quantity;
                                $refundOrderItemReason = 'cancellation';
                                
                                $isRefunded = refund($refundOrderItemId,$refundOrderItemPrice,$refundOrderItemQuantity,$refundOrderItemReason);

                                if($isRefunded=='failed')
                                {
                                    return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
                                }
                                $orderedItem->canceled_refunded_amount = $refundOrderItemPrice * $refundOrderItemQuantity;
                                $orderedItem->earned_reward_points = 0;
                                $orderedItem->reward_point_status = 'completed';
                                $orderedItem->item_status = 'canceled';

                                $orderedItem->save();
                            }
                            break;
                        }
                    }
                    if(!$notInAnyCase)
                    {
                        //if no policy set then return all amount
                        $orderedItem = OrderItem::where('contest_application_id',$id)->first();
                        if($orderedItem)
                        {
                            $getContestInfo = Contest::where('id', $orderedItem->contest_id)->where('status', '!=', 'completed')->first();
                            if($getContestInfo)
                            {
                                $refundOrderItemId = $orderedItem->id;
                                $refundOrderItemPrice = $orderedItem->price_after_apply_reward_points;

                                $refundOrderItemQuantity = $orderedItem->quantity;
                                $refundOrderItemReason = 'cancellation';

                                $orderedItem->amount_transferred_to_vendor = 0;
                                $orderedItem->student_store_commission = 0;
                                $orderedItem->cool_company_commission = 0;
                                $orderedItem->vat_amount = 0;

                                $orderedItem->canceled_refunded_amount = $refundOrderItemPrice * $refundOrderItemQuantity;
                                $orderedItem->returned_rewards = ceil($orderedItem->used_item_reward_points / $refundOrderItemQuantity);
                                $orderedItem->save();

                                $isRefunded = refund($refundOrderItemId,$refundOrderItemPrice,$refundOrderItemQuantity,$refundOrderItemReason);

                                if($isRefunded=='failed')
                                {
                                    return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
                                }

                                $orderedItem->amount_transferred_to_vendor = 0;
                                $orderedItem->student_store_commission = 0;
                                $orderedItem->cool_company_commission = 0;
                                $orderedItem->vat_amount = 0;

                                $orderedItem->canceled_refunded_amount = $refundOrderItemPrice * $refundOrderItemQuantity;
                                $orderedItem->returned_rewards = ceil($orderedItem->used_item_reward_points / $refundOrderItemQuantity);
                                $orderedItem->earned_reward_points = 0;
                                $orderedItem->reward_point_status = 'completed';
                                $orderedItem->item_status = 'canceled';

                                $orderedItem->save();
                            }
                        }
                    }
                }
                else
                {
                    //if no policy set then return all amount
                    $orderedItem = OrderItem::where('contest_application_id',$id)->first();
                    if($orderedItem)
                    {
                        $getContestInfo = Contest::where('id', $orderedItem->contest_id)->where('status', '!=', 'completed')->first();
                        if($getContestInfo)
                        {
                            $refundOrderItemId = $orderedItem->id;
                            $refundOrderItemPrice = $orderedItem->price_after_apply_reward_points;

                            $refundOrderItemQuantity = $orderedItem->quantity;
                            $refundOrderItemReason = 'cancellation';
                            
                            
                            $orderedItem->amount_transferred_to_vendor = 0;
                            $orderedItem->student_store_commission = 0;
                            $orderedItem->cool_company_commission = 0;
                            $orderedItem->vat_amount = 0;

                            $orderedItem->canceled_refunded_amount = $refundOrderItemPrice * $refundOrderItemQuantity;
                            $orderedItem->returned_rewards = ceil($orderedItem->used_item_reward_points / $refundOrderItemQuantity);
                            $orderedItem->save();
                                
                            
                            $isRefunded = refund($refundOrderItemId,$refundOrderItemPrice,$refundOrderItemQuantity,$refundOrderItemReason);

                            if($isRefunded=='failed')
                            {
                                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
                            }

                            $orderedItem->amount_transferred_to_vendor = 0;
                            $orderedItem->student_store_commission = 0;
                            $orderedItem->cool_company_commission = 0;
                            $orderedItem->vat_amount = 0;

                            $orderedItem->canceled_refunded_amount = $refundOrderItemPrice * $refundOrderItemQuantity;
                            $orderedItem->returned_rewards = ceil($orderedItem->used_item_reward_points / $refundOrderItemQuantity);
                            $orderedItem->earned_reward_points = 0;
                            $orderedItem->reward_point_status = 'completed';
                            $orderedItem->item_status = 'canceled';

                            $orderedItem->save();
                        }
                    }
                }
            }
            elseif($request->application_status == 'rejected')
            {
                $contestApplication->reason_for_rejection = $request->reason_for_rejection;
                $contestApplication->reason_id_for_rejection = $request->reason_id_for_rejection;
            }
            $contestApplication->application_status = $request->application_status;
            $contestApplication->save();
    		// Notification Start

            $title = 'Status Updated';
            
            if(Auth::id() == $contestApplication->user_id)
            {
                $user = $contestApplication->contest->user;
                $user_type = 'seller';
                $screen = 'created';
            }
            else
            {
                $user = $contestApplication->user;
                $user_type = 'buyer';
                $screen = 'joined';
            }
            $body =  'Status updated to '.$request->application_status.' by '.AES256::decrypt(Auth::user()->first_name, env('ENCRYPTION_KEY')).'for Application on Contest '.$contestApplication->contest->title;
            $type = 'Contest Application';
            pushNotification($title,$body,$user,$type,true,$user_type,'contest',$contestApplication->id,$screen);

            // Notification End

    		return response()->json(prepareResult(false, $contestApplication, getLangByLabelGroups('contest_application_status','contest_application_status'.$request->application_status)), config('http_response.success'));
    	}
    	catch (\Throwable $exception)
    	{
    		DB::rollback();
    		\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }
}
