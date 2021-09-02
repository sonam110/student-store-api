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
use App\Models\Faq;
use App\Models\LangForDDL;
use App\Models\ProductsServicesBook;
use DB;
use Edujugon\PushNotification\PushNotification;
use App\Models\StudentDetail;
use App\Models\JobTag;
use App\Models\Label;
use Stripe;


class FrontController extends Controller
{

	public function getUserType()
	{
		// $push = new PushNotification('apn');
		// $message = [
		// 	'aps' => [
		// 		'alert' => [
		// 			'title' => 'test push notification',
		// 			'body' 	=> 'testing'
		// 		],
		// 		'sound' => 'default'
		// 	]
		// ];
		// $push->setMessage($message)
		// ->setDevicesToken('2DBC3B374FA534FE5D0C503835B390A4D064B39D89E31771EFA5BF712EFA88D4');
		// $push = $push->send(); 

		// return $push->getFeedback();
		
		// $stripe = new \Stripe\StripeClient("sk_test_519T7jfD6j8NkE89Ko1JaxF7qs6zURAqj4qmr2a2v33LE8MDxYq2CAhMwaZOQKkV5EA7ZZfApqYSPRZRVb1piukao001xGmsBCv");
		// $charge = $stripe->charges->create(
		// 	[
		// 	  'amount' => 1 * 100,
		// 	  'currency' => 'usd',
		// 	  'source' => 'tok_mastercard', // obtained with Stripe.js
		// 	  'description' => 'My First Test Charge (created for API docs)'
		// 	], 
		// 	[
		// 	  'idempotency_key' => 'Y2uAhIh4FXPftzj7'
		// 	]
		// );
		// return $charge;

		// $stripe = new \Stripe\StripeClient(
		//   env('STRIPE_SECRET')
		// );
		// $data = $stripe->refunds->create([
		//   'charge' => 'ch_1JD4Z6D6j8NkE89KF3o9fIMa',
		//   'amount' => 1 * 100,
		// ]);

		
		// return $data;

		

    // foreach ($labels as $key => $label) {
    //         if(LabelGroup::where('name',$key)->count() > 0)
    //         {
    //             $labelGroup = LabelGroup::where('name',$key)->first();
    //         }
    //         else
    //         {
    //             $labelGroup = new LabelGroup;
    //             $labelGroup->name                = $key;
    //             $labelGroup->status              = 1;
    //             $labelGroup->save();
    //         }

    //         foreach ($label as $key1 => $value) {
    //             if(Label::where('label_group_id',$labelGroup->id)->where('label_name',$key1)->where('language_id',1)->count() == 0)
    //             {
    //                 $label = new Label;
    //                 $label->label_group_id         = $labelGroup->id;
    //                 $label->language_id            = 1;
    //                 $label->label_name             = $key1;
    //                 $label->label_value            = $value;
    //                 $label->status                 = 1;
    //                 $label->save(); 
    //             }
    //         }
    //     }

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

    public function page($slug)
    {
        try
        {
            $page = Page::where('slug',$slug)->first();
            return response(prepareResult(false, $page, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function getFaqs()
    {
        try
        {
            $faq = Faq::get();
            return response(prepareResult(false, $faq, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
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
}