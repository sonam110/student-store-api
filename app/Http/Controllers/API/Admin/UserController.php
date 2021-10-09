<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use App\Models\UserPackageSubscription;
use App\Models\StudentDetail;
use App\Models\ServiceProviderDetail;
use App\Models\AddressDetail;
use App\Models\AppSetting;
use App\Models\PaymentCardDetail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\VendorFundTransfer;




class UserController extends Controller
{
	public function index(Request $request)
	{
		try
		{
			if(!empty($request->per_page_record))
			{
			    $users = User::where('user_type_id','!=', '1')->orderBy('created_at','DESC')->with('language','userType','studentDetail','serviceProviderDetail','defaultAddress','defaultPaymentCard')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
			    $users = User::where('user_type_id','!=', '1')->orderBy('created_at','DESC')->with('language','userType','studentDetail','serviceProviderDetail','defaultAddress','defaultPaymentCard')->get();
			}
			return response(prepareResult(false, $users, getLangByLabelGroups('messages','message_user_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function userByType($user_type_id)
	{
		try
		{
			$users = User::where('user_type_id',$user_type_id )->orderBy('created_at','DESC')->get();
			return response(prepareResult(false, UserResource::collection($users), getLangByLabelGroups('messages','message_user_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}




	public function show(User $user)
	{		
		return response()->json(prepareResult(false, new UserResource($user), getLangByLabelGroups('messages','message_user_list')), config('http_response.success'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\User  $user
	 * @return \Illuminate\Http\Response
	 */
	
	public function update(Request $request,User $user)
	{
		$validation = \Validator::make($request->all(),[ 
			'first_name'        => 'required|max:55',
			'email'             => 'email|required|unique:users,email,'.$user->id,
			'contact_number'    => 'numeric|required|unique:users,contact_number,'.$user->id,
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}
		if($request->dob)
		{
			$dob = date("Y-m-d", strtotime($request->dob));
		}
		else
		{
			$dob = null;
		}

		$user->first_name           = $request->first_name;
		$user->last_name            = $request->last_name;
		$user->email                = $request->email;
		$user->contact_number       = $request->contact_number;
		$user->password             = bcrypt($request->password);
		$user->gender               = $request->gender;
		$user->dob                  = $dob;
		$user->profile_pic_path     = $request->profile_pic_path;
		$user->profile_pic_thumb_path     = env('CDN_DOC_THUMB_URL').basename($request->profile_pic_path);
		$user->user_type_id         = $request->user_type_id;
		$user->language_id          = $request->language_id;
		$user->is_verified          = true;
		$user->is_agreed_on_terms   = true;
		$user->is_prime_user        = true;
		$user->is_deleted           = false;
		$user->status               = true;
		$user->last_login           = now();
		
		if($user->save())
		{
			return response(prepareResult(false, $user, getLangByLabelGroups('messages','message_user_updated')), config('http_response.created'));
		}
		else
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function basicDetailUpdate(Request $request, $user_id)
	{
		$validation = \Validator::make($request->all(),[ 
			'first_name'        => 'required|max:55',
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}

		$user = User::find($user_id);
		if($request->dob)
		{
			$dob = date("Y-m-d", strtotime($request->dob));
		}
		else
		{
			$dob = $user->dob;
		}

		$user->first_name           				= $request->first_name;
		$user->last_name            				= $request->last_name;
		$user->gender               				= $request->gender;
		$user->short_intro          				= $request->short_intro;
		$user->dob                  				= $dob;
		$user->profile_pic_path     				= $request->profile_pic_path;
		$user->profile_pic_thumb_path     			= env('CDN_DOC_THUMB_URL').basename($request->profile_pic_path);
		$user->cp_first_name 						= $request->cp_first_name;
		$user->cp_last_name 						= $request->cp_last_name;
		$user->cp_email 							= $request->cp_email;
		$user->cp_contact_number 					= $request->cp_contact_number;
		$user->cp_gender 							= $request->cp_gender;
		$user->is_minor 							= $request->is_minor;
		// $user->guardian_first_name 					= $request->guardian_first_name;
		// $user->guardian_last_name 					= $request->guardian_last_name;
		// $user->guardian_email 						= $request->guardian_email;
		// $user->guardian_contact_number 				= $request->guardian_contact_number;
		// $user->is_guardian_email_verified 			= $request->is_guardian_email_verified;
		// $user->is_guardian_contact_number_verified 	= $request->is_guardian_contact_number_verified;
		$user->show_email 						= $request->show_email;
		$user->show_contact_number 				= $request->show_contact_number;

		$user->bank_account_type 	= $request->bank_account_type;
		$user->bank_name 			= $request->bank_name;
		$user->bank_account_num 	= $request->bank_account_num;
		$user->bank_identifier_code = $request->bank_identifier_code;
		
		if($user->save())
		{
			return response(prepareResult(false, $user, getLangByLabelGroups('messages','message_basic_detail_updated')), config('http_response.created'));
		}
		else
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function extraDetailUpdate(Request $request, $user_id)
	{
		$validation = \Validator::make($request->all(),[ 
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}

		$user = User::find($user_id);
		if($user->userType->title == 'Student')
		{
			$userDetail = StudentDetail::where('user_id',$user->id)->first();
			$userDetail->user_id 					= $user_id;
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

			$userDetail = ServiceProviderDetail::where('user_id',$user->id)->first();
			$userDetail->user_id 					= $user_id;
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
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function languageUpdate(Request $request, $user_id)
	{
		$user = User::find($user_id);
		$user->language_id   = $request->language_id;
		$user->save();
		return response(prepareResult(false, new UserResource($user), getLangByLabelGroups('messages','message_user_updated')), config('http_response.created'));
	}

   
	public function destroy(User $user)
	{
		$user->delete();
		return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_user_deleted')), config('http_response.success'));
	}

	public function statusUpdate(Request $request, $id)
	{
		$user = User::find($id);
		$user->status = $request->status;
		if($user->save())
		{

			if($user->user_type_id == 2)
			{
				$studentDetail = StudentDetail::where('user_id',$request->user_id)->first();
				$studentDetail->status = $request->status;
				$studentDetail->save();
			}
			elseif($user->user_type_id == 3)
			{
				$servceProviderDetail = ServiceProviderDetail::where('user_id',$request->user_id)->first();
				$servceProviderDetail->status = $request->status;
				$servceProviderDetail->save();
			}

			return response()->json(prepareResult(false, $user, getLangByLabelGroups('messages','message_user_status_updated')), config('http_response.success'));
		}
		else
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function multipleStatusUpdate(Request $request)
	{
	    $validation = Validator::make($request->all(), [
	            'status'    => 'required',
	            'user_id'    => 'required'
	        ]);

	    if ($validation->fails()) {
	        return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
	    }

	    DB::beginTransaction();
	    try
	    {
	        $users = User::whereIn('id',$request->user_id)->get();
	        foreach ($users as $key => $user) {
	            $user->status = $request->status;
	            $user->save();


	            if($user->user_type_id == 2)
	            {
	            	$studentDetail = StudentDetail::where('user_id',$user->id)->first();
	            	$studentDetail->status = $request->status;
	            	$studentDetail->save();
	            }
	            elseif($user->user_type_id == 3)
	            {
	            	$servceProviderDetail = ServiceProviderDetail::where('user_id',$user->id)->first();
	            	$servceProviderDetail->status = $request->status;
	            	$servceProviderDetail->save();
	            }
	            
	            $title = 'User Status Updated';
	            $body =  'User '.$user->title.' status has been successfully updated.';

	            $type = 'User Action';
	            pushNotification($title,$body,$user,$type,true,'creator','user',$user->id,'my-listing');
	        }

	        DB::commit();
	        return response()->json(prepareResult(false, $users, getLangByLabelGroups('messages','messages_user_'.$request->action)), config('http_response.created'));
	    }
	    catch (\Throwable $exception)
	    {
	        DB::rollback();
	        return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
	    }
	}

	public function userFilter(Request $request)
    {
        try
        {
        	$users = User::where('users.user_type_id','!=', '1')->orderBy('users.created_at','desc');


        	if(!empty($request->name))
            {
            	$users->where('users.first_name', 'LIKE', '%'.$request->name.'%')->orWhere('users.last_name', 'LIKE', '%'.$request->name.'%');
            }
            if(!empty($request->first_name))
            {
                $users->where('users.first_name', 'LIKE', '%'.$request->first_name.'%');
            }
            if(!empty($request->last_name))
            {
                $users->where('users.last_name', 'LIKE', '%'.$request->last_name.'%');
            }
            if(!empty($request->is_minor))
            {
                $users->where('users.is_minor', $request->is_minor) ;
            }
            if(!empty($request->user_type_id))
            {
                $users->where('users.user_type_id',  $request->user_type_id);

            	if($request->user_type_id == 2)
            	{
            		$users->join('student_details', function ($join) {
		                $join->on('users.id', '=', 'student_details.user_id');
		            });
		            if(!empty($request->education_level))
		            {
		            	$users->where('student_details.education_level', 'LIKE', '%'.$request->education_level.'%');
		            }
		            if(!empty($request->education_institution))
		            {
		            	$users->where('student_details.institute_name', 'LIKE', '%'.$request->education_institution.'%');
		            }
            	}
            	elseif($request->user_type_id == 3) 
            	{
            		$users->join('service_provider_details', function ($join) {
		                $join->on('users.id', '=', 'service_provider_details.user_id');
		            });

		            if(!empty($request->registration_type_id))
		            {
		            	$users->where('service_provider_details.registration_type_id',  $request->registration_type_id);
		            }
		            if(!empty($request->service_provider_type_id))
		            {
		            	$users->where('service_provider_details.service_provider_type_id',  $request->service_provider_type_id);
		            }
            	}
            }
            if(!empty($request->email))
            {
                $users->where('users.email', 'LIKE', '%'.$request->email.'%');
            }
            if(!empty($request->contact_number))
            {
            	$users->where('users.contact_number', 'LIKE', '%'.$request->contact_number.'%')->orWhere('users.guardian_contact_number', 'LIKE', '%'.$request->contact_number.'%');
            }
            if(!empty($request->userStatus))
            {
                $users->whereIn('users.status', $request->userStatus);
            }

            if(!empty($request->cp_gender))
            {
                $users->where('users.cp_gender', $request->cp_gender);
            }

            if(!empty($request->gender))
            {
                $users->where('users.gender', $request->gender);
            }

            if (!empty($request->package_id)) {
            	$users->join('user_package_subscriptions', function ($join) {
		                $join->on('users.id', '=', 'user_package_subscriptions.user_id');
		            })
            	->where('package_id',$request->package_id);
            }

            if(!empty($request->per_page_record))
            {
                $usersData = $users->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $usersData = $users->get();
            }
            return response(prepareResult(false, $usersData, getLangByLabelGroups('messages','messages_job_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function userPackageSubscriptions(Request $request)
    {
    	try
		{
			if(!empty($request->per_page_record))
			{
			    $userPackageSubscriptions = UserPackageSubscription::select('id','user_id')->groupBy('user_id')->orderBy('created_at','DESC')->with('user:id,first_name,last_name,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.userPackageSubscriptions')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
			    $userPackageSubscriptions = UserPackageSubscription::select('id','user_id')->groupBy('user_id')->orderBy('created_at','DESC')->with('user:id,first_name,last_name,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.userPackageSubscriptions')->get();
			}
			return response(prepareResult(false, $userPackageSubscriptions, getLangByLabelGroups('messages','message_user_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		} 
    }

    

    public function addressList(Request $request,$user_id)
	{
		$user = User::find($user_id);
		if(!empty($request->per_page_record))
		{
		    $user_address_list = AddressDetail::where('user_id',$user_id)->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
		}
		else
		{
		    $user_address_list = AddressDetail::where('user_id',$user_id)->get();
		}
		// $user_address_list = $user->addressDetails;		
		return response()->json(prepareResult(false, $user_address_list, getLangByLabelGroups('messages','message_user_address_list')), config('http_response.success'));
	}

	public function paymentCardList(Request $request,$user_id)
	{	
		$user = User::find($user_id);
		if(!empty($request->per_page_record))
		{
		    $user_payment_card_list = PaymentCardDetail::where('user_id',$user_id)->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
		}
		else
		{
		    $user_payment_card_list = PaymentCardDetail::where('user_id',$user_id)->get();
		}
		// $user_payment_card_list = $user->paymentCardDetails;		
		return response()->json(prepareResult(false, $user_payment_card_list, getLangByLabelGroups('messages','message_payment_card_list')), config('http_response.success'));
	}

	public function rewardPointDetails($id)
	{
		$user = User::find($id);
		$user['available_reward_pts'] 		= $user->reward_points;
		$user['pending_reward_pts'] 		= $user->orderItems->where('reward_point_status','pending')->count();
		$user['used_reward_pts'] 			= $user->orders->where('reward_point_status','used')->count();
		$user['reward_pt_policy'] 			= AppSetting::first()->reward_points_policy;
		$user['customer_reward_pt_value'] 	= AppSetting::first()->customer_rewards_pt_value;
		$user['reward_pts_used_on_orders'] 	= Order::where('user_id',$user->id)->where('used_reward_points','>','0')->get(['order_number','grand_total','created_at','order_status','used_reward_points']);
		$user['reward_pts_earned_on_items'] = OrderItem::join('orders', function ($join) {
                $join->on('order_items.order_id', '=', 'orders.id');
            })
		->where('order_items.user_id',$id)
		->where('order_items.earned_reward_points', '>', 0)
		->get(['orders.order_number','order_items.created_at','order_items.title','order_items.product_type','order_items.cover_image','order_items.item_status','order_items.price','order_items.quantity','order_items.earned_reward_points','order_items.reward_point_status']);

		return response(prepareResult(false, $user, getLangByLabelGroups('messages','message_reward_points_detail')), config('http_response.created'));
	}


	public function transactionDetails(Request $request)
	{
		if(!empty($request->per_page_record))
		{
		    $transaction_details = VendorFundTransfer::where('user_id',$request->user_id)->orderBy('created_at','desc')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
		}
		else
		{
		    $transaction_details = VendorFundTransfer::where('user_id',$request->user_id)->orderBy('created_at','desc')->get();
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
		->where('products_services_books.user_id',$request->user_id)
		->where('order_items.is_transferred_to_vendor',1)
		->sum('order_items.amount_transferred_to_vendor');
		+ OrderItem::select('order_items.id')
		->join('contest_applications',function ($join) {
			$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
		})
		->join('contests',function ($join) {
			$join->on('contest_applications.contest_id', '=', 'contests.id');
		})
		->where('contests.user_id',$request->user_id)
		->where('order_items.is_transferred_to_vendor',1)
		->sum('order_items.amount_transferred_to_vendor');



		$data['total_pending_amount'] = OrderItem::select('order_items.id')
		->join('products_services_books',function ($join) {
			$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
		})
		->where('products_services_books.user_id',$request->user_id)
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
		->where('contests.user_id',$request->user_id)
		->where('order_items.is_replaced','0')
		->where('order_items.is_returned','0')
		->where('order_items.is_disputed','0')
		->where('order_items.is_transferred_to_vendor',0)
		->sum('order_items.amount_transferred_to_vendor');


		$data['student_store_commission'] = OrderItem::select('order_items.id')
		->join('products_services_books',function ($join) {
			$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
		})
		->where('products_services_books.user_id',$request->user_id)
		->where('order_items.is_replaced','0')
		->where('order_items.is_returned','0')
		->where('order_items.is_disputed','0')
		->sum('order_items.student_store_commission');
		+ OrderItem::select('order_items.id')
		->join('contest_applications',function ($join) {
			$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
		})
		->join('contests',function ($join) {
			$join->on('contest_applications.contest_id', '=', 'contests.id');
		})
		->where('contests.user_id',$request->user_id)
		->sum('order_items.student_store_commission');


		$data['cool_company_commission'] = OrderItem::select('order_items.id')
		->join('products_services_books',function ($join) {
			$join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
		})
		->where('products_services_books.user_id',$request->user_id)
		->where('order_items.is_replaced','0')
		->where('order_items.is_returned','0')
		->where('order_items.is_disputed','0')
		->sum('order_items.cool_company_commission');
		+ OrderItem::select('order_items.id')
		->join('contest_applications',function ($join) {
			$join->on('order_items.contest_application_id', '=', 'contest_applications.id');
		})
		->join('contests',function ($join) {
			$join->on('contest_applications.contest_id', '=', 'contests.id');
		})
		->where('contests.user_id',$request->user_id)
		->sum('order_items.cool_company_commission');
		
		return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_reward_points_detail')), config('http_response.created'));
	}
}
