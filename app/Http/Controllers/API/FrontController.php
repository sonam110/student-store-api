<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserType;
use App\Models\User;
use App\Models\ServiceProviderTypeDetail;
use App\Models\RegistrationTypeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\LabelGroup;
use App\Models\Language;
use Auth;
use App\Models\Job;
use App\Models\AppSetting;
use App\Models\Page;
use App\Models\FAQ;
use App\Models\LangForDDL;
use App\Models\ProductsServicesBook;
use DB;
use Edujugon\PushNotification\PushNotification;
use App\Models\StudentDetail;
use App\Models\JobTag;
use App\Models\Label;
use App\Models\Slider;
use App\Models\EmailTemplate;
use App\Models\ProductImage;
use App\Models\ServiceProviderDetail;
use App\Models\PaymentGatewaySetting;
use App\Models\Contest;
use Stripe;
use App\Mail\ForgotPasswordMail;
use Mail;
use Image;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use mervick\aesEverywhere\AES256;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FrontController extends Controller
{
	function __construct()
    {
        $this->paymentInfo = PaymentGatewaySetting::first();
        $this->appsetting = AppSetting::first();
    }

	public function getUserType()
	{
    	// foreach ($labels as $key => $label) {
     //        if(LabelGroup::where('name',$key)->count() > 0)
     //        {
     //            $labelGroup = LabelGroup::where('name',$key)->first();
     //        }
     //        else
     //        {
     //            $labelGroup = new LabelGroup;
     //            $labelGroup->name                = $key;
     //            $labelGroup->status              = 1;
     //            $labelGroup->save();
     //        }

     //        foreach ($label as $key1 => $value) {
     //            if(Label::where('label_group_id',$labelGroup->id)->where('label_name',$key1)->where('language_id',3)->count() > 0)
     //            {
     //            	$label = Label::where('label_group_id',$labelGroup->id)->where('label_name',$key1)->where('language_id',3)->first();
     //            }
     //        	else
     //        	{
     //                $label = new Label;
     //            }
     //            $label->label_group_id         = $labelGroup->id;
     //            $label->language_id            = 3;
     //            $label->label_name             = $key1;
     //            $label->label_value            = $value;
     //            $label->status                 = 1;
     //            $label->save(); 
     //        }
     //    }

		$userTypes = UserType::where('id','!=', 1)->get();
		return response()->json(prepareResult(false, $userTypes, getLangByLabelGroups('messages','message_user_type_list')), config('http_response.success'));		
	}

	public function getServiceProviderType(Request $request)
	{
		$serviceProviderTypes = ServiceProviderTypeDetail::select('service_provider_type_details.*')
		->join('service_provider_types', function ($join) {
			$join->on('service_provider_type_details.service_provider_type_id', '=', 'service_provider_types.id');
		})
		->where('service_provider_types.registration_type_id',$request->registration_type_id)
		->where('language_id',$request->language_id)
		->get();
		return response()->json(prepareResult(false, $serviceProviderTypes, "Service-Provider-Types retrieved successfully! "), config('http_response.success'));
	}

	public function getRegistrationType(Request $request)
	{
		$registrationTypes = RegistrationTypeDetail::select('registration_type_details.*')
		->join('registration_types', function ($join) {
			$join->on('registration_type_details.registration_type_id', '=', 'registration_types.id');
		})
		->where('language_id',$request->language_id)
		->get();
		return response()->json(prepareResult(false, $registrationTypes, "Registration-Types retrieved successfully! "), config('http_response.success'));
	}

	public function userQr($qr_code)
	{
		$userInfo = User::where('qr_code_number', $qr_code)->with('userType','studentDetail')->first();
		if($userInfo)
		{
			return response()->json(prepareResult(false, $userInfo, "Registration-Types retrieved successfully! "), config('http_response.success'));
		}
		return response()->json(prepareResult(true, [], getLangByLabelGroups('messages', 'message_error')), config('http_response.internal_server_error'));
	}

	public function labelByGroupName(Request $request)
	{
		$getLang = $request->language_id;
		$labelGroups = LabelGroup::select('id','name')
		->where('name', $request->group_name)
		->with(['labels' => function($q) use ($getLang) {
			$q->select('id','label_group_id','language_id','label_name','label_value')
			->where('language_id', $getLang);
		}])
		->first();
		if($labelGroups)
		{
			return response()->json(prepareResult(false, $labelGroups, getLangByLabelGroups('messages', 'messages_label_group_info')), config('http_response.success'));
		}
		return response()->json(prepareResult(true, [], getLangByLabelGroups('messages', 'message_error')), config('http_response.internal_server_error'));
	}

	public function getInfoByGroupAndLabelName(Request $request)
	{
		$group_name = $request->group_name;
		$getLang 	= $request->language_id;
		$label_name = $request->label_name;
		$getLabelGroup = LabelGroup::select('id')
		->with(['returnLabelNames' => function($q) use ($getLang, $label_name) {
			$q->select('id','label_group_id', 'label_name','label_value')
			->where('language_id', $getLang)
			->where('label_name', $label_name);
		}])
		->where('name', $group_name)
		->first();

		if($getLabelGroup)
		{
			return response()->json(prepareResult(false, $getLabelGroup, getLangByLabelGroups('messages', 'messages_label_group_info')), config('http_response.success'));
		}
		return response()->json(prepareResult(true, [], getLangByLabelGroups('messages', 'message_error')), config('http_response.internal_server_error'));
	}

	public function updateUserLanguage(Request $request)
	{
		$getLang = env('APP_DEFAULT_LANGUAGE', '1');
		if(Auth::check())
		{
			$getLang = Auth::user()->language_id;
			if(empty($getLang))
			{
				$getLang = env('APP_DEFAULT_LANGUAGE', '1');
			}
			$userLangUpdate = User::find(Auth::id());
			$userLangUpdate->language_id = $request->language_id;
			$userLangUpdate->save();
		}

		if($getLang)
		{
			return response()->json(prepareResult(false, $getLang, getLangByLabelGroups('messages', 'messages_language_changed')), config('http_response.success'));
		}
		return response()->json(prepareResult(true, [], getLangByLabelGroups('messages', 'message_error')), config('http_response.internal_server_error'));
	}

	public function getLanguages()
	{
		$languages = Language::where('status', 1)->get();
		if($languages)
		{
			return response()->json(prepareResult(false, $languages, getLangByLabelGroups('messages', 'messages_language_list')), config('http_response.success'));
		}
		return response()->json(prepareResult(true, [], getLangByLabelGroups('messages', 'message_error')), config('http_response.internal_server_error'));
	}


	

	public function appSettings()
	{
		try
		{
			$stripeSetting = PaymentGatewaySetting::first();
			$appSetting = AppSetting::first();
			$appSetting['stripe_setting'] = [
				'payment_gateway_name' 		=> AES256::encrypt($stripeSetting->payment_gateway_name, env('ENCRYPTION_KEY')),
				'payment_gateway_key' 		=> AES256::encrypt($stripeSetting->payment_gateway_key, env('ENCRYPTION_KEY')),
				'payment_gateway_secret' 	=> AES256::encrypt($stripeSetting->payment_gateway_secret, env('ENCRYPTION_KEY')),
				'stripe_currency' 			=> AES256::encrypt($stripeSetting->stripe_currency, env('ENCRYPTION_KEY')),
				'klarna_username' 			=> AES256::encrypt($stripeSetting->klarna_username, env('ENCRYPTION_KEY')),
				'klarna_password' 			=> AES256::encrypt($stripeSetting->klarna_password, env('ENCRYPTION_KEY')),
				'swish_access_token' 		=> AES256::encrypt($stripeSetting->swish_access_token, env('ENCRYPTION_KEY')),
			];
			return response(prepareResult(false, $appSetting, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function getRewardPointCurrencyValue()
	{
		try
		{
			$customer_rewards_pt_value = AppSetting::first(['single_rewards_pt_value','customer_rewards_pt_value','vat']);
			return response(prepareResult(false, $customer_rewards_pt_value, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function getPages(Request $request)
	{
		try
		{
			if(!empty($request->language_id))
			{
				$language_id = $request->language_id;
			}
			else
			{
				$language_id = 1;
			}

			$data = [];
			$data['header_pages'] = Page::where('is_header_menu',true)->where('language_id',$language_id)->orderBy('auto_id', 'ASC')->get();

			$data['footer']['section_1'] = Page::where('is_footer_menu',true)->where('footer_section',1)->where('language_id',$language_id)->orderBy('auto_id', 'ASC')->get(); 
			$data['footer']['section_2'] = Page::where('is_footer_menu',true)->where('footer_section',2)->where('language_id',$language_id)->orderBy('auto_id', 'ASC')->get(); 
			return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function page( Request $request,$slug)
	{
		try
		{
			if(!empty($request->language_id))
			{
				$language_id = $request->language_id;
			}
			else
			{
				$language_id = 1;
			}
			$page = Page::where('slug',$slug)->where('language_id',$language_id)->first();
			return response(prepareResult(false, $page, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function getFaqs(Request $request)
	{
		try
		{
			if(!empty($request->language_id))
			{
				$language_id = $request->language_id;
			}
			else
			{
				$language_id = 1;
			}

			$faq = FAQ::where('language_id',$language_id)->get();
			return response(prepareResult(false, $faq, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function getSliders(Request $request)
	{
		try
		{
			if(!empty($request->language_id))
			{
				$language_id = $request->language_id;
			}
			else
			{
				$language_id = 1;
			}

			$sliders = Slider::where('language_id',$language_id)->get();
			return response(prepareResult(false, $sliders, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	

	public function getLabels(Request $request)
	{
		try
		{
			$getLang = $request->language_id;
			$labelGroups = LabelGroup::select('id','name')
			->with(['labels' => function($q) use ($getLang) {
				$q->select('id','label_group_id','language_id','label_name','label_value')
				->where('language_id', $getLang);
			}])->orderBy('auto_id','ASC')->get();
			return response(prepareResult(false, $labelGroups, getLangByLabelGroups('messages','message_label_group_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function getLanguageListForDDL()
	{
		$languages = LangForDDL::orderBy('name', 'ASC')->get();
		return response()->json(prepareResult(false, $languages, getLangByLabelGroups('messages','message_user_type_list')), config('http_response.success'));
	}

	public function gtinIsbnSearch(Request $request)
	{
		$products = ProductsServicesBook::where('gtin_isbn','like', '%'.$request->gtin_isbn.'%')->orderBy('created_at','desc')->get();
		return response()->json(prepareResult(false, $products, getLangByLabelGroups('messages','message_products_services_book_list')), config('http_response.success'));
	}

	public function getEducationInstitutes(Request $request) {
		$educationInstitutes = StudentDetail::groupBy('institute_name')->get(['institute_name']);
		return response()->json(prepareResult(false, $educationInstitutes, getLangByLabelGroups('messages','message_products_services_book_list')), config('http_response.success'));
	}


	public function getJobTags()
	{
		try
		{
			$jobTags = JobTag::where('title', '!=', null)->select('title')->groupBy('title')->get();
			return response(prepareResult(false, $jobTags, getLangByLabelGroups('messages','messages_job_tags_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function payout()
	{ 	
		/*$users = User::get();
		foreach($users as $user)
		{
			$qrCodeNumber = User::QR_NUMBER_PREFIX.User::QR_NUMBER_SEPRATOR.$user->qr_code_number;
			if (extension_loaded('imagick'))
			{
				QrCode::size(500)
				->format('png')
				->errorCorrection('H')
				->gradient(34, 195, 80, 100, 3, 48,'diagonal')
				->merge(env('APP_LOGO'), .2, true)
				->generate(route('user-qr', [$qrCodeNumber]), public_path('uploads/qr/'.$qrCodeNumber.'.png'));

				echo 'QR code: '.$user->qr_code_number.' image generated.<br>';
			}
			else
			{
				echo 'imagick extension not enabaled..<br>';
			}
			
		}
		
		die;*/
		//acct_1F0knGLBmnAF4Rxg -aman
		\Stripe\Stripe::setApiKey($this->paymentInfo->payment_gateway_secret);

		/*
		$account = \Stripe\Customer::create(
		  ["email" => "aman.h@nrt.co.in"],
		  ["stripe_account" => "acct_1F0knGLBmnAF4Rxg"]
		);

		dd($account);
		*/

		
		/*$account = \Stripe\Account::create([
		  'country' => 'US',
		  'type' => 'custom',
		  'capabilities' => [
		    'card_payments' => [
		      'requested' => true,
		    ],
		    'transfers' => [
		      'requested' => true,
		    ],
		  ],
		]);

		dd($account);*/

		/*
		$account = \Stripe\Account::create([
		  'type' => 'standard',
		]);

		dd($account);
		*/
		
		/*
		$account_links = \Stripe\AccountLink::create([
		  'account' => 'acct_1Jc8zuRnhaMphhEV',
		  'refresh_url' => 'https://example.com/reauth',
		  'return_url' => 'https://example.com/return',
		  'type' => 'account_onboarding',
		]);

		dd($account_links);
		*/

		
		/*$payout = \Stripe\Transfer::create([
		  "amount" => 999,
		  "currency" => "USD",
		  "destination" => "acct_1Jc7iyRgancAKpJI",
		  "transfer_group" => "ORDER_95"
		]);

		dd($payout);*/
		

		/*
		$payout = \Stripe\PaymentIntent::create([
		  'amount' => 1000,
		  'currency' => 'usd',
		  'payment_method_types' => ['card_present'],
		  'capture_method' => 'manual',
		], [
		  'stripe_account' => 'acct_1Ja0oURm6mY5ns6G',
		]);

		dd($payout);
		*/

		/*
		$payment_intent = \Stripe\PaymentIntent::create([
		  'payment_method_types' => ['card'],
		  'amount' => 1000,
		  'currency' => 'usd',
		  'transfer_data' => [
		    'amount' => 877,
		    'destination' => 'acct_1Ja0oURm6mY5ns6G',
		  ],
		]);

		dd($payment_intent);

		*/

		/*
		$payout = \Stripe\Payout::create([
		  'amount' => 999,
		  'currency' => 'USD',
		  'method' => 'standard',
		], [
		  'stripe_account' => 'acct_1Ja0oURm6mY5ns6G',
		]);

		dd($payout);
		*/

		/*
		$payout = \Stripe\Payout::create([
		  'amount' => 889,
		  'currency' => 'usd',
		], [
		  'stripe_account' => 'acct_1Ja0oURm6mY5ns6G',
		]);

		dd($payout);
		*/

		
		$stripe = new \Stripe\StripeClient($this->paymentInfo->payment_gateway_secret);

		/*
		$users = $stripe->accounts->all(['limit' => 3]);
		dd($users);
		*/

		/*$updateCapability = $stripe->accounts->updateCapability(
		  'acct_1Ja0Q7RmXczxfXRt',
		  'card_payments',
		  ['requested' => true]
		);

		dd($updateCapability);*/

		/*$account = $stripe->accounts->delete(
		  'acct_1Jc7fdDH2Q0vOxnt',
		  []
		);
		dd($account);*/

		
		/*//Create customer
		$account = $stripe->customers->create([
			'name' 				=> 'Ashok Sahu',
			'phone'				=> '9713753131',
			'email'				=> 'ashok@nrt.co.in',
		  	'description' 		=> 'My First Test Customer (created for API docs)',
		]);
		dd($account->id);  //customer id cus_KLWfeafgS59wL4 */


		/*//Create card
		$cardinfo = $stripe->customers->createSource(
		  	'cus_KLWfeafgS59wL4',
		  	[
		  		'source' 	=> [
		  			'object'	=> 'card',
		  			'number' 	=> 5555555555554444,
			  		'exp_month' => 12,
			  		'exp_year' 	=> 2023,
			  		'cvc' 		=> 234,
			  		'name'      => 'Jhon Paul'
		  		],
		  	]
		);
		dd($cardinfo);  // card id : card_1JhVWUD6j8NkE89KojLGbmyM*/

		/*$paymentMethods = $stripe->paymentMethods->create([
		  'type' => 'card',
		  'card' => [
		    'number' => '4000002760003184',
		    'exp_month' => 10,
		    'exp_year' => 2022,
		    'cvc' => '314',
		  ],
		]);*/

		//dd($paymentMethods);  //pm_1JhZRRD6j8NkE89KQsjaoesm

		/*$paymentMethodAttach = $stripe->paymentMethods->attach(
		  'pm_1JhZRRD6j8NkE89KQsjaoesm',
		  ['customer' => 'cus_KLWfeafgS59wL4']
		);

		dd($paymentMethodAttach);*/

		/*$customerInfo = $stripe->customers->retrieve(
		  'cus_KLWfeafgS59wL4',
		  []
		);

		dd($customerInfo);*/


		/*$allPaymentMethods = \Stripe\PaymentMethod::all([
		  'customer' => 'cus_KLWfeafgS59wL4',
		  'type' => 'card',
		]);

		dd($allPaymentMethods);*/

		/*$subscription = $stripe->subscriptions->create([
		  'customer' => 'cus_KLWfeafgS59wL4',
		  'items' => [
		    ['price' => 'price_1JhY1FD6j8NkE89KlREv79q6'],
		  ],
		  'payment_behavior' => 'default_incomplete',
		  'expand' => ['latest_invoice.payment_intent'],
		]);

		dd($subscription);*/

		/*$refund = \Stripe\Refund::create([
			'amount' => '5000',
		  	'payment_intent' => 'pi_3Jha3RD6j8NkE89K0iYHSoa4',
		]);

		dd($refund);*/

        /*$createProduct = $stripe->products->create([
        	'images'	=> [$this->appsetting->logo_path],
            'name'      => 'testing plan 2',
            'type'		=> 'service',
            'active'    => true
        ]);

        dd($createProduct);

        $plan = $stripe->plans->create([
            'amount'          => 1000,
            'currency'        => $this->paymentInfo->stripe_currency,
            'interval'        => 'day',
            'interval_count'  => 30,
            'product'         => $createProduct->id,
        ]);
        dd($createProduct, $plan);*/
        /*$is_published = 1;
        $planInfo = $stripe->plans->retrieve(
            'plan_KMeJrQBXCET999',
            []
        );
        $productInfo = $planInfo->product;
        $createProduct = $stripe->products->update([
            'prod_KMeJ65ZAEVbUis',
            ['active' => 1]
        ]);

        dd($createProduct);*/

        \Stripe\Stripe::setApiKey($this->paymentInfo->payment_gateway_secret);
		/*$cancelSubscription = $stripe->subscriptions->cancel(
		  	'sub_1JhYEPD6j8NkE89KA5XVE8jO',
		  	[]
		);

		return str_replace('Stripe\Subscription JSON: ', '', $cancelSubscription);*/

		/*$klarna = \Stripe\PaymentIntent::create([
		  'payment_method_types' => ['klarna'],
		  'amount' => 1089,
		  'currency' => 'USD',
		]);
		dd($klarna);*/

		/*$stripe = new \Stripe\StripeClient($this->paymentInfo->payment_gateway_secret);
		$accountStatus = $stripe->accounts->retrieve(
		  'acct_1JnRWxRYOavc3Px5',
		  []
		);
		dd($accountStatus->verification->disabled_reason);*/
	}

	public function strReplaceAssoc(array $replace, $subject) { 
		return str_replace(array_keys($replace), array_values($replace), $subject);
	}

	public function checkSendMail()
	{
		$otp = 1234;
		$emailTemplate = EmailTemplate::where('template_for','forgot_password')->first();

		$body = $emailTemplate->body;

		$arrayVal = [
			'{{user_name}}' => 'Ashok Sahu',
			'{{otp}}'		=> $otp,
		];

		$body = $this->strReplaceAssoc($arrayVal, $body);
		
		$details = [
			'title' => $emailTemplate->subject,
			'body' => $body
		];
		
		$mail = Mail::to('ashok@nrt.co.in')->send(new ForgotPasswordMail($details));
		dd($mail);
	}

	public function getAllFiles()
	{
		$thumbDestinationPath = 'uploads/thumbs/';
		$path = public_path('uploads');
      	$files = \File::allFiles($path);
      	foreach ($files as $key => $file) 
      	{
      		if($key>=0 && $key<=50)
      		{
      			if(basename(pathinfo($file, PATHINFO_EXTENSION))=='jpg' || basename(pathinfo($file, PATHINFO_EXTENSION))=='png' || basename(pathinfo($file, PATHINFO_EXTENSION))=='jpeg')
	      		{
	      			$fileName = pathinfo($file);
	      			echo '<pre>';
	      			print_r($fileName);
	      			if(basename($fileName['dirname'])!='qr')
	      			{
	      				$imgthumb = Image::make($path.'/'.$fileName['basename']);
		                $imgthumb->resize(260, null, function ($constraint) {
		                    $constraint->aspectRatio();
		                });
		                $imgthumb->save($thumbDestinationPath.'/'.$fileName['basename']);
	      			}
	      		}
	      	}
      	}
      	dd('Done');
	}

	public function addThumbFileName()
    {
        
        $allimages = ProductImage::get();
        foreach ($allimages as $key => $image) {
        	if(!empty($image->image_path))
        	{
        		$image->image_path = env('CDN_DOC_URL').'uploads/'.basename($image->image_path);
        		$image->thumb_image_path = env('CDN_DOC_THUMB_URL').basename($image->image_path);
        	}
        	else
        	{
        		$image->thumb_image_path  = 'https://www.nrtsms.com/images/no-image.png';
        	}
        	$image->save();
        }

        $companyLogos = ServiceProviderDetail::get();
        foreach ($companyLogos as $key => $image) {
        	if(!empty($image->company_logo_path))
        	{
        		$image->company_logo_path = env('CDN_DOC_URL').'uploads/'.basename($image->company_logo_path);
        		$image->company_logo_thumb_path = env('CDN_DOC_THUMB_URL').basename($image->company_logo_path);
        	}
        	else
        	{
        		$image->company_logo_thumb_path  = 'https://www.nrtsms.com/images/no-image.png';
        	}
        	$image->save();
        }

        $userImages = User::get();
        foreach ($userImages as $key => $image) {
        	if(!empty($image->profile_pic_path))
        	{
        		$image->profile_pic_path = env('CDN_DOC_URL').'uploads/'.basename($image->profile_pic_path);
        		$image->profile_pic_thumb_path = env('CDN_DOC_THUMB_URL').basename($image->profile_pic_path);
        	}
        	else
        	{
        		$image->profile_pic_thumb_path  = 'https://www.nrtsms.com/images/no-image.png';
        	}
        	$image->save();
        }
        

        $contests = Contest::get();
        foreach ($contests as $key => $image) {
        	if(!empty($image->cover_image_path))
        	{
        		$image->cover_image_path = env('CDN_DOC_URL').'uploads/'.basename($image->cover_image_path);
        		$image->cover_image_thumb_path = env('CDN_DOC_THUMB_URL').basename($image->cover_image_path);
        	}
        	else
        	{
        		$image->cover_image_thumb_path  = 'https://www.nrtsms.com/images/no-image.png';
        	}
        	$image->save();
        }
        dd('Done');
    }


  //   public function countryStateCitySeeder()
  //   {
  //   	$country = new Country;
		// $country->name = 'Sweden';
		// $country->sortname = 'SE';
		// $country->phonecode = '46';
		// $country->status = 1;
		// $country->save();


		// foreach ($cities as $key => $value) {
		// 	if(State::where('name',$value['admin_name'])->count() > 0)
		// 	{
		// 		$state = State::where('name',$value['admin_name'])->first();
		// 	}
		// 	else
		// 	{
		// 		$state = new State;
		// 		$state->country_id = $country->id;
		// 		$state->name = $value['admin_name'];
		// 		$state->status = 1;
		// 		$state->save();
		// 	}

		// 	$city = new City;
		// 	$city->state_id = $state->id;
		// 	$city->name = $value['city'];
		// 	$city->status = 1;
		// 	$city->save();
			
		// }
  //   }
}