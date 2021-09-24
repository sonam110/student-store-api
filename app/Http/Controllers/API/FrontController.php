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
use Stripe;
use App\Mail\ForgotPasswordMail;
use Mail;

class FrontController extends Controller
{

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
     //            if(Label::where('label_group_id',$labelGroup->id)->where('label_name',$key1)->where('language_id',1)->count() > 0)
     //            {
     //            	$label = Label::where('label_group_id',$labelGroup->id)->where('label_name',$key1)->where('language_id',1)->first();
     //            }
     //        	else
     //        	{
     //                $label = new Label;
     //            }
     //            $label->label_group_id         = $labelGroup->id;
     //            $label->language_id            = 1;
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
			$appSetting = AppSetting::first();
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
	{ 	//acct_1F0knGLBmnAF4Rxg -aman
		\Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

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

		
		$payout = \Stripe\Transfer::create([
		  "amount" => 999,
		  "currency" => "USD",
		  "destination" => "acct_1Jc7iyRgancAKpJI",
		  "transfer_group" => "ORDER_95"
		]);

		dd($payout);
		

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

		
		$stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

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

		$account = $stripe->accounts->delete(
		  'acct_1Jc7fdDH2Q0vOxnt',
		  []
		);
		dd($account);
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
}