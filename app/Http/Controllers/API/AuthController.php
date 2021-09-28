<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Models\AddressDetail;
use App\Models\StudentDetail;
use App\Models\ServiceProviderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PDF;
use Str;
use DB;
use Mail;
use App\Mail\ForgotPasswordMail;
use App\Models\UserDeviceInfo;
use Auth;
use App\Models\UserPackageSubscription;
use App\Models\Package;
use App\Models\EmailTemplate;
use App\Models\SmsTemplate;
use App\Models\OtpVerification;
use App\Mail\RegistrationMail;
use mervick\aesEverywhere\AES256;
use Stripe;

class AuthController extends Controller
{
	public function strReplaceAssoc(array $replace, $subject) { 
		return str_replace(array_keys($replace), array_values($replace), $subject);
	}

	public function sendOtp($number,$otp)
	{
		$message = SmsTemplate::where('template_for','number verification')->first()->message;


		if(env('IS_ENABLED_SEND_SMS', false))
		{
			$ch = curl_init();
			$phone_number   = ltrim($number, '0');
			$receivers      = ((strlen($phone_number))==9) ? env('COUNTRY_CODE').$phone_number : $phone_number; 
			$sender         = env('SENDERID');
			$account        = env('PIXIE_ACCOUNT');
			$password       = env('PIXIE_PASS');
			$headerContent  = $message;
			$arrayVal = [
				'{{otp}}' => $otp
			];
			$message = $this->strReplaceAssoc($arrayVal, $headerContent);

			
			curl_setopt($ch, CURLOPT_URL,  "http://smsserver.pixie.se/sendsms?");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "account=$account&pwd=$password&sender=$sender&receivers=$receivers&message=$message");
			$buffer = curl_exec($ch);
			

			/*$curl = curl_init();
 
			curl_setopt_array($curl, array(
			  CURLOPT_URL => 'https://www.oursms.in/api/v1/generate-otp?app_key=KrHJYfM6UeSukIOVVJjaOf6qy&app_secret=bIquoJ56Z7SfOWB3iD1JcNkCZ&dlt_template_id=1507162755811107949&mobile_number='.$phone_number.'&v1='.$otp,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'GET',
			  CURLOPT_HTTPHEADER => array(
			    'Content-Type: application/json'
			  ),
			));

			$response = curl_exec($curl);
			curl_close($curl);*/
		}
	}

	public function getOtp(Request $request)
	{
		$validation = \Validator::make($request->all(),[ 
			'contact_number'=> 'required',
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}

		try
		{
			if($request->otp_for == 'registration')
			{
				$user = User::where('contact_number',$request->contact_number)->first();
				if($user)
				{
					return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_user_exists')), config('http_response.internal_server_error'));
				}
			}
			
			//$otp = rand(1000,9999);
			$otp = 1234;
			$this->sendOtp($request->contact_number,$otp);
			if(OtpVerification::where('mobile_number', $request->contact_number)->where('otp_for', $request->otp_for)->count()>0)
			{
				OtpVerification::where('mobile_number', $request->contact_number)->where('otp_for', $request->otp_for)->delete();
			}
			$otpStore = new OtpVerification;
			$otpStore->mobile_number 	= $request->contact_number;
			$otpStore->otp 				= $otp;
			$otpStore->otp_for 			= $request->otp_for;
			$otpStore->save();

			return response()->json(prepareResult(false, ['otp'=>'Sent'], getLangByLabelGroups('messages','message_otp_sent')), config('http_response.success'));
		}
		catch (\Throwable $exception)
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function otpVerification(Request $request)
	{
		if(OtpVerification::where('mobile_number', $request->contact_number)->where('otp_for', $request->otp_for)->where('otp', $request->otp)->count()>0)
		{
			OtpVerification::where('mobile_number', $request->contact_number)->where('otp_for', $request->otp_for)->where('otp', $request->otp)->delete();
			return response()->json(prepareResult(false, ['otp'=>'Otp Verifed.'], 'Verification successfully done.'), config('http_response.success'));
		}
		return response()->json(prepareResult(true, [],'Not found'), config('http_response.not_found'));
	}

	public function emailValidate(Request $request)
	{
		$validation = \Validator::make($request->all(),[ 
			'email'=> 'required|unique:users',
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}

		try
		{
			return response()->json(prepareResult(false, ['email'=>$request->email], getLangByLabelGroups('messages','message_email_validated')), config('http_response.success'));
		}
		catch (\Throwable $exception)
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function register(Request $request)
	{
		// return $request->all();
		$validation = \Validator::make($request->all(),[ 
			'first_name'      	=> 'required',
			'email'          	=> 'required|unique:users',
			'contact_number'    => 'required|unique:users',
			'password'      	=> 'required',
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}

		//QR Code
		$qrCodeNumber = User::QR_NUMBER_PREFIX.User::QR_NUMBER_SEPRATOR.(User::QR_NUMBER_START + User::count());
		if (extension_loaded('imagick'))
		{
			QrCode::size(500)
			->format('png')
			->errorCorrection('H')
			->gradient(34, 195, 80, 100, 3, 48,'diagonal')
			->merge(env('APP_LOGO'), .2, true)
			->generate(route('user-qr', [$qrCodeNumber]), public_path('uploads/qr/'.$qrCodeNumber.'.png'));

		}
		//QR Code

		DB::beginTransaction();

		if($request->dob)
		{
			$dob = date("Y-m-d", strtotime($request->dob));
		}
		else
		{
			$dob = null;
		}

		$profile_pic_path = $request->profile_pic_path;
		if(empty($request->profile_pic_path))
		{
			$profile_pic_path = 'https://www.nrtsms.com/images/no-image.png';
		}
		
		$user = new User;

		if($request->is_minor == true)
		{
			$user->first_name 							= $request->guardian_first_name;
			$user->last_name 							= $request->guardian_last_name;
			$user->email 								= $request->guardian_email;
			$user->contact_number 						= $request->guardian_contact_number;
			$user->password 							= bcrypt($request->guardian_password);
			$user->guardian_first_name 					= $request->first_name;
			$user->guardian_last_name 					= $request->last_name;
			$user->guardian_email 						= $request->email;
			$user->guardian_contact_number 				= $request->contact_number;
			$user->guardian_password 					= bcrypt($request->password);

		}
		else
		{
			$user->first_name 							= $request->first_name;
			$user->last_name 							= $request->last_name;
			$user->email 								= $request->email;
			$user->contact_number 						= $request->contact_number;
			$user->password 							= bcrypt($request->password);
			$user->guardian_first_name 					= $request->guardian_first_name;
			$user->guardian_last_name 					= $request->guardian_last_name;
			$user->guardian_email 						= $request->guardian_email;
			$user->guardian_contact_number 				= $request->guardian_contact_number;
			$user->guardian_password 					= bcrypt($request->guardian_password);
		}
		
		$user->is_email_verified 					= false;
		$user->is_contact_number_verified 			= $request->is_contact_number_verified;
		$user->dob 									= $dob;
		$user->profile_pic_path						= $profile_pic_path;
		$user->profile_pic_path						= env('CDN_DOC_THUMB_URL').basename($profile_pic_path);
		$user->user_type_id         				= $request->user_type_id;
		$user->gender         						= $request->gender;
		$user->language_id          				= $request->language_id;
		$user->cp_first_name 						= $request->cp_first_name;
		$user->cp_last_name 						= $request->cp_last_name;
		$user->cp_email 							= $request->cp_email;
		$user->cp_contact_number 					= $request->cp_contact_number;
		$user->cp_gender 							= $request->cp_gender;
		$user->is_minor 							= $request->is_minor;
		$user->social_security_number 				= $request->social_security_number;
		
		$user->is_guardian_email_verified 			= false;
		$user->is_guardian_contact_number_verified 	= $request->is_guardian_contact_number_verified;
		$user->qr_code_img_path     				= env('CDN_DOC_URL').'uploads/qr/'.$qrCodeNumber.'.png';
		$user->qr_code_number       				= $qrCodeNumber;
		$user->qr_code_valid_till   				= date('Y-m-d H:i:s', strtotime(env('ACCOUNT_EXPIRE_AFTER_DAYS').' days'));;
		$user->is_verified 							= true;
		$user->is_agreed_on_terms 					= true;
		$user->is_prime_user 						= true;
		$user->is_deleted 							= false;
		$user->status 								= 0;
		$user->last_login 				= now();
		if($user->save())
		{
			foreach ($request->address as $key => $address)
			{
				$addressDetail = new AddressDetail;
				$addressDetail->user_id 		= $user->id;
				$addressDetail->latitude 		= $address['latitude'];
				$addressDetail->longitude 		= $address['longitude'];
				$addressDetail->country 		= $address['country'];
				$addressDetail->state 			= $address['state'];
				$addressDetail->city 			= $address['city'];
				$addressDetail->full_address 	= $address['full_address'];
				$addressDetail->zip_code 		= $address['zip_code'];
				$addressDetail->address_type 	= $address['address_type'];
				$addressDetail->is_default 		= $address['is_default'];
				$addressDetail->status 			= 1;
				$addressDetail->save();
			}

			if($request->is_minor != "true" || $request->is_minor != 1)
			{
				foreach ($request->user_device_info as $key => $deviceInfo) 
				{
					$userDeviceInfo 					= new UserDeviceInfo;
					$userDeviceInfo->user_id 			= $user->id;
					$userDeviceInfo->fcm_token 			= $deviceInfo['fcm_token'];
					$userDeviceInfo->device_uuid 		= $deviceInfo['device_uuid'];
					$userDeviceInfo->platform 			= $deviceInfo['platform'];
					$userDeviceInfo->model 				= $deviceInfo['model'];
					$userDeviceInfo->os_version 		= $deviceInfo['os_version'];
					$userDeviceInfo->manufacturer 		= $deviceInfo['manufacturer'];
					$userDeviceInfo->serial_number 		= $deviceInfo['serial_number'];
					$userDeviceInfo->system_ip_address 	= $request->ip();
					$userDeviceInfo->status 			= 1;
					$userDeviceInfo->save();
				}
			}

			

			// $stripe = new \Stripe\StripeClient(
			// 			  env('STRIPE_SECRET')
			// 			);

			// $account = $stripe->accounts->create([
			//   'type' => 'custom',
			//   'country' => 'SE',
			//   'email' => $request->email,
			//   'capabilities' => [
			//     'card_payments' => ['requested' => true],
			//     'transfers' => ['requested' => true],
			//   ],
			// ]);

			if(!empty($request->user_packages))
			{
				foreach ($request->user_packages as $key => $user_package) 
				{
					$package = Package::find($user_package);
					$userPackageSubscription 							= new UserPackageSubscription;
					$userPackageSubscription->user_id 					= $user->id;
					$userPackageSubscription->package_id 				= $user_package;
					$userPackageSubscription->package_valid_till		= date('Y-m-d',strtotime('+'.$package->duration.'days'));
					$userPackageSubscription->subscription_status 		= 1;
					$userPackageSubscription->module					= $package->module;
					$userPackageSubscription->type_of_package			= $package->type_of_package;
					$userPackageSubscription->job_ads					= $package->job_ads;
					$userPackageSubscription->publications_day			= $package->publications_day;
					$userPackageSubscription->duration					= $package->duration;
					$userPackageSubscription->cvs_view					= $package->cvs_view;
					$userPackageSubscription->employees_per_job_ad		= $package->employees_per_job_ad;
					$userPackageSubscription->no_of_boost				= $package->no_of_boost;
					$userPackageSubscription->boost_no_of_days			= $package->boost_no_of_days;
					$userPackageSubscription->most_popular				= $package->most_popular;
					$userPackageSubscription->most_popular_no_of_days	= $package->most_popular_no_of_days;
					$userPackageSubscription->top_selling				= $package->top_selling;
					$userPackageSubscription->top_selling_no_of_days	= $package->top_selling_no_of_days;
					$userPackageSubscription->price						= $package->price;
					$userPackageSubscription->start_up_fee				= $package->start_up_fee;
					$userPackageSubscription->subscription				= $package->subscription;
					$userPackageSubscription->commission_per_sale		= $package->commission_per_sale;
					$userPackageSubscription->number_of_product			= $package->number_of_product;
					$userPackageSubscription->number_of_service			= $package->number_of_service;
					$userPackageSubscription->number_of_book			= $package->number_of_book;
					$userPackageSubscription->number_of_contest			= $package->number_of_contest;
					$userPackageSubscription->number_of_event			= $package->number_of_event;
					$userPackageSubscription->notice_month				= $package->notice_month;
					$userPackageSubscription->locations					= $package->locations;
					$userPackageSubscription->organization				= $package->organization;
					$userPackageSubscription->attendees					= $package->attendees;
					$userPackageSubscription->range_of_age				= $package->range_of_age;
					$userPackageSubscription->cost_for_each_attendee	= $package->cost_for_each_attendee;
					$userPackageSubscription->top_up_fee				= $package->top_up_fee;
					$userPackageSubscription->save();
				}
			}

			if($request->user_type_id == '4')
			{
				// $otp = rand(1000,9999);
				$otp = 1234;
				$email = AES256::decrypt($user->email, env('ENCRYPTION_KEY'));

				if(OtpVerification::where('mobile_number', $user->email)->where('otp_for', 'email_verification')->count()>0)
				{
					OtpVerification::where('mobile_number', $user->email)->where('otp_for', 'email_verification')->delete();
				}
				$otpStore = new OtpVerification;
				$otpStore->mobile_number 	= $email;
				$otpStore->otp 				= $otp;
				$otpStore->otp_for 			= 'email_verification';
				$otpStore->save();

				//------------------------Mail start-------------------------//


				$emailTemplate = EmailTemplate::where('template_for','registration')->first();

				$body = $emailTemplate->body;

				$arrayVal = [
					'{{user_name}}' => AES256::decrypt($user->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($user->last_name, env('ENCRYPTION_KEY')),
					'{{verification_link}}' => '<a href="'.env('FRONT_APP_URL').'email-verification/'.base64_encode($email).'/'.base64_encode($otp).'" style="background: linear-gradient(
90deg,#1da89c 0,#1da89c);
    border-color: #1da89c;display: inline-block;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    text-align: center;
    text-decoration: none;
    vertical-align: middle;
    cursor: pointer;
    -webkit-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-color: transparent;
    border: 1px solid transparent;
    padding: .375rem .75rem;
    font-size: 1rem;
    border-radius: .25rem;
    transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;">'.getLangByLabelGroups('messages','verify_your_email_address').'</a>',

				];
				$body = $this->strReplaceAssoc($arrayVal, $body);
				
				$details = [
					'title' => $emailTemplate->subject,
					'body' 	=> $body
				];
				
				Mail::to(AES256::decrypt($user->email, env('ENCRYPTION_KEY')))->send(new RegistrationMail($details));

				//------------------------Mail End-------------------------//
			}

			

			$accessToken = $user->createToken('authToken')->accessToken;

			$user['access_token'] = $accessToken;
			$user['address_detail'] = $addressDetail;
			// $user['url'] =  url('/api/email-verification/'.base64_encode($email).'/'.base64_encode($otp));
			if($spDetail = ServiceProviderDetail::where('user_id',$user->id)->first())
			{
				$user['company_logo'] = $spDetail->company_logo_path;
				$user['company_logo_thumb_path'] = $spDetail->company_logo_thumb_path;
			}
			// $user['account'] = $account;
			DB::commit();
			return response(prepareResult(false, $user, getLangByLabelGroups('messages','message_user_registered')), config('http_response.created'));
		}
		else
		{
			DB::rollback();
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function saveUserDetails(Request $request)
	{
		$validation = \Validator::make($request->all(),[ 
			'user_id' => 'required',
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}

		$user = User::find($request->user_id);
		if($user->user_type_id == '2')
		{
			$userDetail = new StudentDetail;
			$userDetail->user_id 					= $request->user_id;
			$userDetail->enrollment_no 				= $request->enrollment_no;
			$userDetail->education_level 			= $request->education_level;
			$userDetail->board_university 			= $request->board_university;
			$userDetail->institute_name 			= $request->institute_name;
			$userDetail->no_of_years_of_study 		= $request->no_of_years_of_study;
			$userDetail->student_id_card_img_path	= $request->student_id_card_img_path;
			$userDetail->completion_year			= $request->completion_year;
			$userDetail->status						= $user->status;
		}
		elseif($user->user_type_id == '3')
		{
			$userDetail = new ServiceProviderDetail;
			$userDetail->user_id 					= $request->user_id;
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
			// $otp = rand(1000,9999);
				$otp = 1234;
				$email = AES256::decrypt($user->email, env('ENCRYPTION_KEY'));

				if(OtpVerification::where('mobile_number', $user->email)->where('otp_for', 'email_verification')->count()>0)
				{
					OtpVerification::where('mobile_number', $user->email)->where('otp_for', 'email_verification')->delete();
				}
				$otpStore = new OtpVerification;
				$otpStore->mobile_number 	= $email;
				$otpStore->otp 				= $otp;
				$otpStore->otp_for 			= 'email_verification';
				$otpStore->save();

				//------------------------Mail start-------------------------//


				$emailTemplate = EmailTemplate::where('template_for','registration')->first();

				$body = $emailTemplate->body;

				$arrayVal = [
					'{{user_name}}' => AES256::decrypt($user->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($user->last_name, env('ENCRYPTION_KEY')),
					'{{verification_link}}' => '<a href="'.env('FRONT_APP_URL').'email-verification/'.base64_encode($email).'/'.base64_encode($otp).'" style="background: linear-gradient(
90deg,#1da89c 0,#1da89c);
    border-color: #1da89c;display: inline-block;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    text-align: center;
    text-decoration: none;
    vertical-align: middle;
    cursor: pointer;
    -webkit-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-color: transparent;
    border: 1px solid transparent;
    padding: .375rem .75rem;
    font-size: 1rem;
    border-radius: .25rem;
    transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;">'.getLangByLabelGroups('messages','verify_your_email_address').'</a>',

				];
				$body = $this->strReplaceAssoc($arrayVal, $body);
				
				$details = [
					'title' => $emailTemplate->subject,
					'body' => $body
				];
				
				Mail::to(AES256::decrypt($user->email, env('ENCRYPTION_KEY')))->send(new RegistrationMail($details));

				//------------------------Mail End-------------------------//

			$user['userDetail'] = $userDetail;
			if($spDetail = ServiceProviderDetail::where('user_id',$request->user_id)->first())
			{
				$user['company_logo'] = $spDetail->company_logo_path;
				$user['company_logo_thumb_path'] = $spDetail->company_logo_thumb_path;
			}
			return response(prepareResult(false, $user, getLangByLabelGroups('messages','message_user_detail_saved')), config('http_response.created'));
		}
		else
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function login(Request $request)
	{
		$validation = \Validator::make($request->all(),[ 
			// 'email'     => 'required|email',
			'password'  => 'required',
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}

		try
		{
			$user = User::where('email',$request->contact_number)->orWhere('contact_number',$request->contact_number)->orWhere('guardian_email',$request->contact_number)->orWhere('guardian_contact_number',$request->contact_number)->first(['id','first_name','last_name','email','contact_number','profile_pic_path','profile_pic_thumb_path','user_type_id','language_id','password','guardian_email','guardian_contact_number','is_minor']);
			

			if (!$user) {
				return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_user_not_exists')), config('http_response.not_found'));
			}

			if(Hash::check($request->password,$user->password) || Hash::check($request->password,$user->guardian_password)) 
			{
				if($user->status==2)
				{
					return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','account_is_blocked')), config('http_response.unauthorized'));
				}

				$user['login_with_guardian_data'] = false;


				if(($user->is_minor == true) && (($request->contact_number == AES256::decrypt($user->email, env('ENCRYPTION_KEY'))) or ($request->contact_number == AES256::decrypt($user->contact_number, env('ENCRYPTION_KEY')))))
				{
					$user['login_with_guardian_data'] = true;
				}

				if(($user->is_minor == true || $user->is_minor == 1) && ($user->login_with_guardian_data == false))
				{
				}
				else
				{
					foreach ($request->user_device_info as $key => $deviceInfo) {
						// UserDeviceInfo::where('user_id',Auth::id())->delete();

						// Auth::user()->userDeviceInfos->delete();
						// if($oldDeviceInfo = UserDeviceInfo::where('user_id',Auth::id())->where('device_uuid',$deviceInfo['device_uuid'])->orWhere('system_ip_address',$request->ip())->first())
						// {
						// 	$oldDeviceInfo->delete();
						// }

						$userDeviceInfo 					= new UserDeviceInfo;
						$userDeviceInfo->user_id 			= $user->id;
						$userDeviceInfo->fcm_token 			= $deviceInfo['fcm_token'];
						$userDeviceInfo->device_uuid 		= $deviceInfo['device_uuid'];
						$userDeviceInfo->platform 			= $deviceInfo['platform'];
						$userDeviceInfo->model 				= $deviceInfo['model'];
						$userDeviceInfo->os_version 		= $deviceInfo['os_version'];
						$userDeviceInfo->manufacturer 		= $deviceInfo['manufacturer'];
						$userDeviceInfo->serial_number 		= $deviceInfo['serial_number'];
						$userDeviceInfo->system_ip_address 	= $request->ip();
						$userDeviceInfo->status 			= 1;
						$userDeviceInfo->save();
					}
				}

				if($user->user_type_id == 2)
				{
					$count = StudentDetail::where('user_id',$user->id)->count();
					if($count > 0)
					{
						$user['student_detail'] = true;
					}
					else
					{
						$user['student_detail'] = false;
					}
				}
				elseif($user->user_type_id == 3)
				{
					$count = ServiceProviderDetail::where('user_id',$user->id)->count();
					if($count > 0)
					{
						$user['service_provider_detail'] = true;
					}
					else
					{
						$user['service_provider_detail'] = false;
					}
				}

				$accessToken = $user->createToken('authToken')->accessToken;
				$user['access_token'] = $accessToken;
				if($spDetail = ServiceProviderDetail::where('user_id',$user->id)->first())
				{
					$user['company_logo'] = $spDetail->company_logo_path;
					$user['company_logo_thumb_path'] = $spDetail->company_logo_thumb_path;
				}

				

				return response()->json(prepareResult(false, $user, getLangByLabelGroups('messages','message_user_login_success')),config('http_response.success'));
			} else {
				return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_user_login_credencial_error')),config('http_response.not_found'));
			}
		}
		catch (\Throwable $exception)
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function forgotPassword(Request $request)
	{
		$validation = \Validator::make($request->all(),[ 
			// 'email'=> 'email',
			// 'contact_number'=> 'numeric',
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}

		//$otp = rand(1000,9999);
		$otp = 1234;

		if($request->email != null)
		{
			try
			{
				$email = $request->email;
				$user = User::where('email',$email)->orWhere('guardian_email',$email)->first();
				if(!$user)
				{
					return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_user_not_exists')), config('http_response.internal_server_error'));
				}

				


				//------------------------Mail start-------------------------//

				$emailTemplate = EmailTemplate::where('template_for','forgot_password')->first();

				$body = $emailTemplate->body;

				$arrayVal = [
					'{{user_name}}' => AES256::decrypt($user->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($user->last_name, env('ENCRYPTION_KEY')),
					'{{otp}}'		=> $otp,
				];
				$body = $this->strReplaceAssoc($arrayVal, $body);
				
				$details = [
					'title' => $emailTemplate->subject,
					'body' => $body
				];
				
				$mail = Mail::to(AES256::decrypt($email, env('ENCRYPTION_KEY')))->send(new ForgotPasswordMail($details));

				//------------------------Mail End-------------------------//

				return response()->json(prepareResult(false, ['otp'=>$otp,'user_id'=>$user->id], getLangByLabelGroups('messages','message_otp_sent')), config('http_response.success'));
			}
			catch (\Throwable $exception)
			{
				return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
			}
		}
		elseif($request->contact_number != null)
		{
			try
			{
				$user = User::where('contact_number',$request->contact_number)->orWhere('guardian_contact_number',$request->contact_number)->first();
				if(!$user)
				{
					return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_user_not_exists')), config('http_response.internal_server_error'));
				}
				//$otp = rand(1000,9999);
				$otp = 1234;

				$this->sendOtp($request->contact_number,$otp);

				if(OtpVerification::where('mobile_number', $request->contact_number)->where('otp_for', $request->otp_for)->count()>0)
				{
					OtpVerification::where('mobile_number', $request->contact_number)->where('otp_for', $request->otp_for)->delete();
				}
				$otpStore = new OtpVerification;
				$otpStore->mobile_number 	= $request->contact_number;
				$otpStore->otp 				= $otp;
				$otpStore->otp_for 			= $request->otp_for;
				$otpStore->save();

				return response()->json(prepareResult(false, ['otp'=>$otp,'user_id'=>$user->id], getLangByLabelGroups('messages','message_otp_sent')), config('http_response.success'));
			}
			catch (\Throwable $exception)
			{
				return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
			}

		}
	}

	public function saveNewPassword(Request $request)
	{
		$validation = \Validator::make($request->all(), [
			'user_id'  => 'required',
			'new_password' => 'required',
			'contact_number' => 'required'
		]);

		if ($validation->fails()) {
			return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}

		DB::beginTransaction();
		try
		{
			$user = User::find($request->user_id);
			if(($request->contact_number == $user->guardian_contact_number) || ($request->contact_number == $user->guardian_email))
			{
				$user->guardian_password   = bcrypt($request->new_password);
			}
			else
			{
				$user->password   = bcrypt($request->new_password);
			}
			$user->save();
			DB::commit();
			return response()->json(prepareResult(false, new userResource($user), getLangByLabelGroups('messages','message_new_password_updated')), config('http_response.created'));
		}
		catch (\Throwable $exception)
		{
			DB::rollback();
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}

	}

	public function logout()
	{
		if (Auth::check()) 
		{
			try
			{
				$token = Auth::user()->token();
				$token->revoke();
		    	// UserDeviceInfo::where('user_id',Auth::id())->delete();
		     //   	Auth::user()->AauthAcessToken()->delete();
				return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_logout_success')), config('http_response.created'));
			}
			catch (\Throwable $exception)
			{
				DB::rollback();
				return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
			}
		}
	}


	public function emailVerification($email,$otp)
	{
		$email = base64_decode($email);
		$otp = base64_decode($otp);
		try
		{
			if(OtpVerification::where('mobile_number',$email)->where('otp',$otp)->where('otp_for','email_verification')->count() > 0)
			{
				$user = User::where('email',$email)->first();
				if($user) {
					$user->is_email_verified = 1;
					$user->save();
				} else {
					$user = User::where('guardian_email',$email)->first();
					if($user) {
						$user->is_guardian_email_verified = 1;
						$user->save();
					}
				}
				return response()->json(prepareResult(false, $user, getLangByLabelGroups('messages','message_email_verification_success')), config('http_response.success'));
			}
			else
			{
				return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_email_verification_failed')), config('http_response.bad_request'));
			}
		}
		catch (\Throwable $exception)
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}
}