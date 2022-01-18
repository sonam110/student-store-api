<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\StudentDetail;
use App\Models\ServiceProviderDetail;
use Str;
use DB;
use Auth;
use Hash;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Models\Package;
use App\Models\UserPackageSubscription;
use App\Models\AppSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CvsViewLog;
use App\Models\SharedRewardPoint;
use App\Models\EmailTemplate;
use App\Mail\OrderMail;
use Mail;
use App\Models\VendorFundTransfer;
use App\Models\JobApplication;
use App\Models\UserCvDetail;
use mervick\aesEverywhere\AES256;

class UserProfileController extends Controller
{ 
	public function passwordUpdate(Request $request)
	{
		$validation = \Validator::make($request->all(),[ 
			'old_password'	=> 'required',
			'new_password'  => 'required|max:55',
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}

		try
		{
			$user = Auth::user();
			if(($user->is_minor == true) && ($request->login_with_guardian_data == false))
			{
				if(Hash::check($request->old_password, $user->guardian_password)) 
				{
					$user->guardian_password = bcrypt($request->new_password);
					if($user->save())
					{
						return response(prepareResult(false, $user, getLangByLabelGroups('messages','message_password_updated')), config('http_response.created'));
					}
					else
					{
			            return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
					}
				}
				else
				{
					return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_old_password_error')),config('http_response.not_found'));
				}
			}
			else
			{
				if(Hash::check($request->old_password, $user->password)) 
				{
					$user->password = bcrypt($request->new_password);
					if($user->save())
					{
						return response(prepareResult(false, $user, getLangByLabelGroups('messages','message_password_updated')), config('http_response.created'));
					}
					else
					{
            			return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
					}
				}
				else
				{
					return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_old_password_error')),config('http_response.not_found'));
				}
			}
			
		}
		catch (\Throwable $exception)
		{
			DB::rollback();
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}
	
	public function basicDetailUpdate(Request $request)
	{
		$validation = \Validator::make($request->all(),[ 
			'first_name'        => 'required',
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}

		$user = Auth::user();
		if($request->dob)
		{
			$dob = date("Y-m-d", strtotime($request->dob));
		}
		else
		{
			$dob = $user->dob;
		}

		// if(!empty($request->show_email))
		// {
		// 	$show_email = $request->show_email;
		// }
		// else
		// {
		// 	$show_email = $user->show_email;
		// }

		// if(!empty($request->show_contact_number))
		// {
		// 	$show_contact_number = $request->show_contact_number;
		// }
		// else
		// {
		// 	$show_contact_number = $user->show_contact_number;
		// }

		$user->first_name           				= $request->first_name;
		$user->last_name            				= $request->last_name;
		$user->gender               				= $request->gender;
		$user->short_intro          				= $request->short_intro;
		$user->dob                  				= $dob;
		$user->profile_pic_path     				= $request->profile_pic_path;
		$user->profile_pic_thumb_path     			= env('CDN_DOC_THUMB_URL').basename($request->profile_pic_path);
		// $user->is_email_verified 					= $request->is_email_verified;
		// $user->is_contact_number_verified 			= $request->is_contact_number_verified;
		$user->cp_first_name 						= $request->cp_first_name;
		$user->cp_last_name 						= $request->cp_last_name;
		$user->cp_email 							= $request->cp_email;
		$user->cp_contact_number 					= $request->cp_contact_number;
		$user->cp_gender 							= $request->cp_gender;
		$user->is_minor 							= $request->is_minor;
		$user->social_security_number 				= $request->social_security_number;
		$user->guardian_first_name 					= $request->guardian_first_name;
		$user->guardian_last_name 					= $request->guardian_last_name;
		// $user->guardian_email 						= $request->guardian_email;
		// $user->guardian_contact_number 				= $request->guardian_contact_number;
		// $user->is_guardian_email_verified 			= $request->is_guardian_email_verified;
		// $user->is_guardian_contact_number_verified 	= $request->is_guardian_contact_number_verified;
		$user->show_email 						= $request->show_email;
		$user->show_contact_number 				= $request->show_contact_number;

		$user->bank_account_type 	= (empty($request->bank_account_type)) ? $request->bank_account_type : 1;
		$user->bank_name 			= $request->bank_name;
		$user->bank_account_num 	= $request->bank_account_num;
		$user->bank_identifier_code = $request->bank_identifier_code;
		
		if($user->save())
		{
			return response(prepareResult(false, $user, getLangByLabelGroups('messages','message_basic_detail_updated')), config('http_response.created'));
		}
		else
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function extraDetailUpdate(Request $request)
	{
		$user = Auth::user();
		if($user->userType->title == 'Student')
		{
			if(StudentDetail::where('user_id',$user->id)->count()>0)
			{
				$userDetail = StudentDetail::where('user_id',$user->id)->first();
			}
			else
			{
				$userDetail = new StudentDetail;
			}
			$userDetail->user_id 					= Auth::id();
			$userDetail->enrollment_no 				= $request->enrollment_no;
			$userDetail->education_level 			= $request->education_level;
			$userDetail->board_university 			= $request->board_university;
			$userDetail->institute_name 			= $request->institute_name;
			$userDetail->no_of_years_of_study 		= $request->no_of_years_of_study;
			$userDetail->student_id_card_img_path	= $request->student_id_card_img_path;
			$userDetail->completion_year			= $request->completion_year;
			$userDetail->status						= $user->status;
		}
		elseif($user->userType->title == 'Service Provider')
		{
			if(ServiceProviderDetail::where('user_id',$user->id)->count()>0)
			{
				$userDetail = ServiceProviderDetail::where('user_id',$user->id)->first();
			}
			else
			{
				$userDetail = new ServiceProviderDetail;
			}
			
			$userDetail->user_id 					= Auth::id();
			$userDetail->registration_type_id 		= $request->registration_type_id;
			$userDetail->service_provider_type_id 	= $request->service_provider_type_id;
			$userDetail->company_name 				= $request->company_name;
			$userDetail->organization_number 		= $request->organization_number;
			$userDetail->about_company 				= $request->about_company;
			$userDetail->company_website_url 		= $request->company_website_url;
			$userDetail->company_logo_path 			= $request->company_logo_path;
			$userDetail->company_logo_thumb_path 	= env('CDN_DOC_THUMB_URL').basename($request->company_logo_path);

			$userDetail->vat_number 				= $request->vat_number;
			$userDetail->vat_registration_file_path = $request->vat_registration_file_path;
			$userDetail->year_of_establishment 		= $request->year_of_establishment;
			$userDetail->status						= $user->status;
		}

		if($userDetail->save())
		{
			$user['userDetail'] = $userDetail;
			return response(prepareResult(false, $user, getLangByLabelGroups('messages','message_extra_detail_updated')), config('http_response.created'));
		}
		else
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function addPackage(Request $request)
	{
		$validation = \Validator::make($request->all(),[ 
			'user_packages'        => 'required'
		]);

		foreach ($request->user_packages as $key => $user_package) 
		{
			$package = Package::find($user_package);
			if(!$package)
			{
				return response()->json(prepareResult(true, 'Package not found.', getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
			}
			
			//update package status before active new one
			$oldPackages = UserPackageSubscription::select('id','subscription_status')
            ->where('subscription_status', 1)
            ->where('module', $package->module)
            ->where('user_id', Auth::id())
            ->first();
            if($oldPackages)
            {
            	$oldPackages->subscription_status = 0;
            	$oldPackages->save();
            }
			
			$userPackageSubscription = new UserPackageSubscription;
			$userPackageSubscription->user_id 				= Auth::id();
			$userPackageSubscription->subscription_id 		= $request->subscription_id;
			$userPackageSubscription->stripe_invoice_id 	= $request->stripe_invoice_id;
			$userPackageSubscription->payby 				= $request->payby;
			$userPackageSubscription->package_id 			= $user_package;
			$userPackageSubscription->package_valid_till	= date('Y-m-d',strtotime('+'.$package->duration .'days'));
			$userPackageSubscription->subscription_status 	= 1;
			$userPackageSubscription->module               	= $package->module;
			$userPackageSubscription->type_of_package       = $package->type_of_package;
			$userPackageSubscription->job_ads               = $package->job_ads;
			$userPackageSubscription->publications_day      = $package->publications_day;
			$userPackageSubscription->duration              = $package->duration;
			$userPackageSubscription->cvs_view              = $package->cvs_view;
			$userPackageSubscription->employees_per_job_ad  = $package->employees_per_job_ad;
			$userPackageSubscription->no_of_boost           = $package->no_of_boost;
			$userPackageSubscription->boost_no_of_days      = $package->boost_no_of_days;
			$userPackageSubscription->most_popular          = $package->most_popular;
			$userPackageSubscription->most_popular_no_of_days= $package->most_popular_no_of_days;
			$userPackageSubscription->top_selling          	= $package->top_selling;
			$userPackageSubscription->top_selling_no_of_days= $package->top_selling_no_of_days;
			$userPackageSubscription->price               	= $package->price;
			$userPackageSubscription->start_up_fee          = $package->start_up_fee;
			$userPackageSubscription->subscription          = $package->subscription;
			$userPackageSubscription->commission_per_sale   = $package->commission_per_sale;
			$userPackageSubscription->number_of_product     = $package->number_of_product;
			$userPackageSubscription->number_of_service     = $package->number_of_service;
			$userPackageSubscription->number_of_book		= $package->number_of_book;
			$userPackageSubscription->number_of_contest		= $package->number_of_contest;
			$userPackageSubscription->number_of_event		= $package->number_of_event;
			$userPackageSubscription->notice_month          = $package->notice_month;
			$userPackageSubscription->locations             = $package->locations;
			$userPackageSubscription->organization          = $package->organization;
			$userPackageSubscription->attendees             = $package->attendees;
			$userPackageSubscription->range_of_age          = $package->range_of_age;
			$userPackageSubscription->cost_for_each_attendee= $package->cost_for_each_attendee;
			$userPackageSubscription->top_up_fee            = $package->top_up_fee;
			$userPackageSubscription->save();
			if($userPackageSubscription)
            {
                //update price if package changed
                $type = $package->module;
                $userID = Auth::id();
                packageUpdatePrice($type, $userID);
            }
		}
		$user = Auth::user();

        //Mail Start
        $emailTemplate = EmailTemplate::where('template_for','package_upgrade')->where('language_id',$user->language_id)->first();
		if(empty($emailTemplate))
		{
			$emailTemplate = EmailTemplate::where('template_for','package_upgrade')->first();
		}

        $body = $emailTemplate->body;

        $arrayVal = [
        	'{{user_name}}' => AES256::decrypt($user->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($user->last_name, env('ENCRYPTION_KEY')),
        	'{{module}}' => 	$userPackageSubscription->module,
        	'{{valid_till}}' => $userPackageSubscription->package_valid_till,
        	'{{package_type}}' => $userPackageSubscription->type_of_package,
        ];
        $body = strReplaceAssoc($arrayVal, $body);
        
        $details = [
        	'title' => $emailTemplate->subject,
        	'body' => $body
        ];
        
        Mail::to(AES256::decrypt($user->email, env('ENCRYPTION_KEY')))->send(new OrderMail($details));
		//Mail End

		if($user)
		{
			return response(prepareResult(false, new UserResource($user), getLangByLabelGroups('messages','message_user_updated')), config('http_response.created'));
		}
		else
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function shareRewardPoints(Request $request)
	{
		$validation = \Validator::make($request->all(),[ 
			'contact_number'	=> 'required',
			'reward_points'		=> 'required'
		]);

		if($request->reward_points > Auth::user()->reward_points)
		{
			return response()->json(prepareResult(true, ['yoh have only '.Auth::user()->reward_points.' reward points to share.'], getLangByLabelGroups('messages','message_less_reward_points')), config('http_response.internal_server_error'));
		}
		$receiver = User::where('contact_number',str_replace(' ','', $request->contact_number))->first();

		if(!$receiver)
		{
			return response()->json(prepareResult(true, ['User Not Exist'], getLangByLabelGroups('messages','message_user_not_exists')), config('http_response.internal_server_error'));
		}

		if($receiver->user_type_id != 2)
		{
			return response()->json(prepareResult(true, ['Not Student'], getLangByLabelGroups('messages','message_user_not_student')), config('http_response.internal_server_error'));
		}

		DB::beginTransaction();
        try
        {
			$sharedRewardPoint 						= new SharedRewardPoint;
			$sharedRewardPoint->sender_id 			= Auth::id();
			$sharedRewardPoint->receiver_id 		= $receiver->id;
			$sharedRewardPoint->reward_points 		= $request->reward_points;
			$sharedRewardPoint->save();


			
			User::find($receiver->id)->update(['reward_points' => $receiver->reward_points + $request->reward_points]);

			$sender = Auth::user();
			User::find(Auth::id())->update(['reward_points' => $sender->reward_points - $request->reward_points]);

			// Notification Start

			

			$title = 'Reward Points Shared';
			$body =  AES256::decrypt($sender->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($sender->last_name, env('ENCRYPTION_KEY')).' has shared '.$request->reward_points.' reward points for you.';
			$user = $receiver;
			$type = 'Reward Points';
			pushNotification($title,$body,$user,$type,true,'user','Reward Point',$sharedRewardPoint->id,'reward-points');

			DB::commit();
			return response(prepareResult(false, new UserResource($user), getLangByLabelGroups('messages','message_created')), config('http_response.created'));
		}
		catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
	}

	public function rewardPointDetails()
	{
		$data = [];
		$data['available_reward_pts'] 		= Auth::user()->reward_points;
		$data['pending_reward_for_transfer']= Auth::user()->orderItems->where('reward_point_status','pending')->where('earned_reward_points','>', 0)->sum('earned_reward_points');
		$user['pending_reward_pts'] 		= Auth::user()->orderItems->where('reward_point_status','pending')->where('earned_reward_points','>', 0)->count();
		$data['used_reward_pts'] 			= Auth::user()->orders->where('reward_point_status','used')->count();
		$data['reward_pt_policy'] 			= AppSetting::first()->reward_points_policy;
		$data['customer_reward_pt_value'] 	= AppSetting::first()->customer_rewards_pt_value;
		$data['reward_pts_used_on_orders'] 	= Order::where('user_id',Auth::id())->where('used_reward_points','>','0')->get(['order_number','grand_total','created_at','order_status','used_reward_points']);
		$data['reward_pts_earned_on_items'] = OrderItem::join('orders', function ($join) {
                $join->on('order_items.order_id', '=', 'orders.id');
            })
		->where('order_items.user_id',Auth::id())
		->where('order_items.earned_reward_points', '>', 0)
		->get(['orders.order_number','order_items.created_at','order_items.title','order_items.product_type','order_items.cover_image','order_items.item_status','order_items.price','order_items.quantity','order_items.earned_reward_points','order_items.reward_point_status']);
		$data['reward_points_sharing_history'] 		= SharedRewardPoint::where('sender_id',Auth::id())->orWhere('receiver_id',Auth::id())->with('receiver:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','sender:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path')->orderBy('created_at','desc')->get();

		return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_reward_points_detail')), config('http_response.created'));
	}

	public function cvsView(Request $request, $user_cv_detail_id)
	{
		if(CvsViewLog::where('user_cv_detail_id', $user_cv_detail_id)->where('user_id', Auth::id())->count()<1)
		{
			$user_package = UserPackageSubscription::where('user_id', Auth::id())
				->where('module','job')
				->whereDate('package_valid_till','>=', date('Y-m-d'))
				->whereDate('subscription_status', 1)
				->orderBy('created_at','desc')
				->first();

			if(empty($user_package))
			{
			    return response()->json(prepareResult(true, ['No Package Subscribed'], getLangByLabelGroups('messages','message_no_package_subscribed_error')), config('http_response.internal_server_error'));
			}

			if($user_package->used_cvs_view >= $user_package->cvs_view)
			{
				return response()->json(prepareResult(true, ['Package Use Exhasted'], getLangByLabelGroups('messages','message_cvs_view_exhausted_error')), config('http_response.internal_server_error'));
			}

			if($user_package->cvs_view<1)
			{
				return response()->json(prepareResult(true, ['CV view is not allowed in the purchased package.'], getLangByLabelGroups('messages','message_cvs_view_exhausted_error')), config('http_response.internal_server_error'));
			}

			$getUserId = UserCvDetail::select('user_id')->find($user_cv_detail_id);

			$cvsViewLog = new CvsViewLog;
			$cvsViewLog->user_id 						= Auth::id();
			$cvsViewLog->user_cv_detail_id 				= $user_cv_detail_id;
			$cvsViewLog->applicant_id 					= $getUserId->user_id;
			if($request->job_id) {
				$cvsViewLog->job_id = $request->job_id;
			}
			$cvsViewLog->valid_till 					= $user_package->package_valid_till;
			$cvsViewLog->user_package_subscription_id 	= $user_package->id;
			$cvsViewLog->save();

			$user_package->update(['used_cvs_view'=>($user_package->used_cvs_view + 1)]);
			$user = Auth::user();
			if($user)
			{
				return response(prepareResult(false, new UserResource($user), getLangByLabelGroups('messages','message_user_updated')), config('http_response.created'));
			}
			else
			{
            	return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
			}
		}
		return response()->json(prepareResult(true, ['Already added.'], 'Already added.'), config('http_response.accepted'));
	}
	
	public function getCvsView(Request $request)
	{
		try {
			$data = CvsViewLog::where('user_id', Auth()->id())->with('company:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path', 'user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path', 'user.cvDetail','job:id,title,slug');
            if(!empty($request->per_page_record))
            {
            	$results = $data->orderBy('created_at','DESC')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
            	$results = $data->get();
            }
            return response(prepareResult(false, $results, getLangByLabelGroups('messages','message_reward_points_detail')), config('http_response.created'));
        } catch (\Throwable $e) {
            return response()->json(prepareResult(true, $e->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
	}

	public function updateJobViewed($job_application_id)
	{
		$updateViewed = JobApplication::find($job_application_id);
		if($updateViewed)
		{
			if($updateViewed->is_viewed = 1) 
			{
				return response()->json(prepareResult(true, 'Job already viewed.', getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
			}
			$updateViewed->is_viewed = 1;
			$updateViewed->save();
			return response(prepareResult(false, $updateViewed, getLangByLabelGroups('messages','message_success_title')), config('http_response.created'));
		}
		return response()->json(prepareResult(true, 'Job application not found.', getLangByLabelGroups('messages','message_error')), config('http_response.not_found'));
	}

	public function languageUpdate(Request $request)
	{
		$user = Auth::user();
		$user->language_id   = $request->language_id;
		$user->save();
		return response(prepareResult(false, new UserResource($user), getLangByLabelGroups('messages','message_user_updated')), config('http_response.created'));
	}

	public function coolCompanyFreelancer()
	{
		$data = Auth::user()->coolCompanyFreelancer;
		return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_cool_company_freelancer_list')), config('http_response.created'));
	}

	public function unreadNotifications()
	{
		$data = Auth::user()->unreadChats->count();
		return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_unread_notiication_count')), config('http_response.created'));
	}

	public function transactionDetails(Request $request)
	{
		if(!empty($request->per_page_record))
		{
		    $transaction_details = VendorFundTransfer::where('user_id',Auth::id())->orderBy('created_at','desc')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
		}
		else
		{
		    $transaction_details = VendorFundTransfer::where('user_id',Auth::id())->orderBy('created_at','desc')->get();
		}
		
		return response(prepareResult(false, $transaction_details, getLangByLabelGroups('messages','message_reward_points_detail')), config('http_response.created'));
	}

	public function earningDetails(Request $request)
	{
		$data = [];
		$data['total_earned_amount'] = OrderItem::select('order_items.id')
		->join('products_services_books',function ($join) {
			$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
		})
		->where('products_services_books.user_id',Auth::id())
		->where('order_items.is_transferred_to_vendor',1)
		->sum('order_items.amount_transferred_to_vendor');
		+ OrderItem::select('order_items.id')
		->join('contest_applications',function ($join) {
			$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
		})
		->join('contests',function ($join) {
			$join->on('contest_applications.contest_id', '=', 'contests.id');
		})
		->where('contests.user_id',Auth::id())
		->where('order_items.is_transferred_to_vendor',1)
		->sum('order_items.amount_transferred_to_vendor');



		$data['total_pending_amount'] = OrderItem::select('order_items.id')
		->join('products_services_books',function ($join) {
			$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
		})
		->where('products_services_books.user_id',Auth::id())
		->where('order_items.is_transferred_to_vendor',0)
		->where('order_items.is_replaced','0')
		->where('order_items.is_returned','0')
		->where('order_items.is_disputed','0')
		->sum('order_items.amount_transferred_to_vendor');
		+ OrderItem::select('order_items.id')
		->join('contest_applications',function ($join) {
			$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
		})
		->join('contests',function ($join) {
			$join->on('contest_applications.contest_id', '=', 'contests.id');
		})
		->where('contests.user_id',Auth::id())
		->where('order_items.is_replaced','0')
		->where('order_items.is_returned','0')
		->where('order_items.is_disputed','0')
		->where('order_items.is_transferred_to_vendor',0)
		->sum('order_items.amount_transferred_to_vendor');
		
		return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_reward_points_detail')), config('http_response.created'));
	}
}
