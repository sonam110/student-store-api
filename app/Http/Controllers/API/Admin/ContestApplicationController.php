<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContestApplication;
use App\Http\Resources\ContestApplicationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use mervick\aesEverywhere\AES256;
use Auth;

class ContestApplicationController extends Controller
{
	public function index(Request $request)
	{
		try
		{
			$contestApplications = ContestApplication::with('contest','user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','contest.cancellationRanges','contest.categoryMaster','contest.subCategory')->orderBy('created_at','DESC');
			if(!empty($request->application_status))
			{
				$contestApplications = $contestApplications->where('application_status',$request->application_status);
			}
			if(!empty($request->per_page_record))
			{
				$contestApplications = $contestApplications->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$contestApplications = $contestApplications->get();
			}
			return response(prepareResult(false, $contestApplications, getLangByLabelGroups('messages','message_contest_application_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}


	public function show(ContestApplication $contestApplication)
	{
		$contestApplication = ContestApplication::with('contest','user','contest.cancellationRanges','contest.categoryMaster','contest.subCategory')->find($contestApplication->id);		
		return response()->json(prepareResult(false, $contestApplication, getLangByLabelGroups('messages','message_contest_application_list')), config('http_response.success'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\ContestApplication  $contestApplication
	 * @return \Illuminate\Http\Response
	 */
	


	public function destroy(ContestApplication $contestApplication)
	{
		$contestApplication->delete();
		return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_contest_application_deleted')), config('http_response.success'));
	}

	public function applicantFilter(Request $request)
	{
		try
		{
			$applicants = ContestApplication::select('contest_applications.*')
			->join('users', function ($join) {
				$join->on('contest_applications.user_id', '=', 'users.id');
			})
			->join('contests', function ($join) {
				$join->on('contest_applications.contest_id', '=', 'contests.id');
			})
			->orderBy('contest_applications.created_at','DESC')
			->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path,status','user.cvDetail','user.defaultAddress');

			
			if(!empty($request->type))
			{
				$applicants->where('contests.type',$request->type);
			}
			if(!empty($request->user_id))
			{
				$applicants->where('contest_applications.user_id',$request->user_id);
			}
			if(!empty($request->contest_id))
			{
				$applicants->where('contest_applications.contest_id',$request->contest_id);
			}
			if(!empty($request->contest_title))
			{
				$applicants->where('contest_title', 'LIKE', '%'.$request->contest_title.'%');
			}
			if(!empty($request->application_status))
			{
				$applicants->where('application_status', $request->application_status);
			}
			if(!empty($request->first_name))
			{
				$applicants->where('users.first_name', 'LIKE', '%'.$request->first_name.'%');
			}
			if(!empty($request->last_name))
			{
				$applicants->where('users.last_name', 'LIKE', '%'.$request->last_name.'%');
			}
			if(!empty($request->mode))
			{
				$applicants->where('contests.mode', $request->mode);
			}
			if(!empty($request->email))
			{
				$$applicants->where('users.email', 'LIKE', '%'.$request->email.'%');
			}
			if(!empty($request->userStatus))
			{
				$applicants->whereIn('users.status', $request->userStatus);
			}

			if(!empty($request->user_type))
			{
				if($request->user_type == 'student')
				{
					$user_type_id = '2';
				}
				elseif($request->user_type == 'company')
				{
					$user_type_id = '3';
				}
				else
				{
					$user_type_id = '4';
				}
				$applicants->where('users.user_type_id', $user_type_id);
			}

			if(!empty($request->per_page_record))
			{
				$applicantsData = $applicants->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$applicantsData = $applicants->get();
			}
			return response(prepareResult(false, $applicantsData, getLangByLabelGroups('messages','messages_contest_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}


	public function statusUpdate(Request $request, $id)
	{
		try
		{
			$contestApplication = ContestApplication::find($id);
            // return $contestApplication;
			if($request->application_status == 'canceled')
			{
				$contestApplication->reason_for_cancellation = $request->reason_for_cancellation;
				$contestApplication->reason_id_for_cancellation = $request->reason_id_for_cancellation;
				$contestApplication->cancelled_by = Auth::id();

				$contest = $contestApplication->contest;
				$remainingHours = \Carbon\Carbon::parse($contest->start_time)->diffInHours(now());
				if($contest->use_cancellation_policy == true)
				{
					$cancellationRanges = $contest->cancellationRanges;
					foreach ($cancellationRanges as $key => $value) {
						if($remainingHours >= $value->from && $remainingHours < $value->to)
						{
							$orderedItem = OrderItem::where('contest_application_id',$id)->get();
							$refundOrderItemId = $orderItem->id;
							$refundOrderItemPrice = ($orderItem->price)*($value->deduct_percentage_value)/100;
							$refundOrderItemQuantity = $orderItem->quantity;
							$refundOrderItemReason = 'cancellation';
							refund($refundOrderItemId,$refundOrderItemPrice,$refundOrderItemQuantity,$refundOrderItemReason);
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

			$user = $contestApplication->user;
			$user_type = 'buyer';
			$screen = 'joined';

			$body =  'Status updated to '.$request->application_status.' by '.AES256::decrypt(Auth::user()->first_name, env('ENCRYPTION_KEY')).'for Application on Contest '.$contestApplication->contest->title;
			$type = 'Contest Application';
			pushNotification($title,$body,$user,$type,true,$user_type,'contest',$contestApplication->id,$screen);

            // Notification End

			return response()->json(prepareResult(false, $contestApplication, getLangByLabelGroups('contest_application_status','contest_application_status'.$request->application_status)), config('http_response.success'));
		}
		catch (\Throwable $exception)
		{
			DB::rollback();
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function multipleStatusUpdate(Request $request)
	{
		try
		{
			$contestApplications = ContestApplication::whereIn('id',$request->contest_application_id)->get();

			foreach ($contestApplications as $key => $contestApplication) {
				if($request->application_status == 'canceled')
				{
					$contestApplication->reason_for_cancellation = $request->reason_for_cancellation;
					$contestApplication->reason_id_for_cancellation = $request->reason_id_for_cancellation;
					$contestApplication->cancelled_by = Auth::id();

					$contest = $contestApplication->contest;
					$remainingHours = \Carbon\Carbon::parse($contest->start_time)->diffInHours(now());
					if($contest->use_cancellation_policy == true)
					{
						$cancellationRanges = $contest->cancellationRanges;
						foreach ($cancellationRanges as $key => $value) {
							if($remainingHours >= $value->from && $remainingHours < $value->to)
							{
								$orderedItem = OrderItem::where('contest_application_id',$id)->get();
								$refundOrderItemId = $orderItem->id;
								$refundOrderItemPrice = ($orderItem->price)*($value->deduct_percentage_value)/100;
								$refundOrderItemQuantity = $orderItem->quantity;
								$refundOrderItemReason = 'cancellation';
								refund($refundOrderItemId,$refundOrderItemPrice,$refundOrderItemQuantity,$refundOrderItemReason);
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

				$user = $contestApplication->user;
				$user_type = 'buyer';
				$screen = 'joined';

				$body =  'Status updated to '.$request->application_status.' by '.AES256::decrypt(Auth::user()->first_name, env('ENCRYPTION_KEY')).'for Application on Contest '.$contestApplication->contest->title;
				$type = 'Contest Application';
				pushNotification($title,$body,$user,$type,true,$user_type,'contest',$contestApplication->id,$screen);
			}


            // Notification End

			return response()->json(prepareResult(false, $contestApplication, getLangByLabelGroups('contest_application_status','contest_application_status'.$request->application_status)), config('http_response.success'));
		}
		catch (\Throwable $exception)
		{
			DB::rollback();
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

}
