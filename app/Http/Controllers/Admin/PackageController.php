<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Package;
use Str;
use DB;
use Auth;
use App\Models\UserPackageSubscription;
use Stripe;
use App\Models\AppSetting;
use App\Models\PaymentGatewaySetting;

class PackageController extends Controller
{
    function __construct()
    {
        $this->appsetting = AppSetting::select('logo_path')->first();
        $this->paymentInfo = PaymentGatewaySetting::first();
    }

    public function index(Request $request)
    {
        try
        {
            $packages = Package::orderBy('created_at','DESC');
            if(!empty($request->module))
            {
                $packages = $packages->where('module',$request->module);
            }
            if(!empty($request->package_for))
            {
                $packages = $packages->where('package_for',$request->package_for);
            }
            if(!empty($request->type_of_package))
            {
                $packages = $packages->where('type_of_package',$request->type_of_package);
            }

            if(!empty($request->per_page_record))
            {
                $packages = $packages->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $packages = $packages->get();
            }
            return response(prepareResult(false, $packages, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
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
            'module'            => 'required',
            'package_for'       => 'required',
            'type_of_package'   => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }
        DB::beginTransaction();
        try
        {
            $stripe = new \Stripe\StripeClient($this->paymentInfo->payment_gateway_secret);


            $duration = $request->duration;
            if($request->duration==999)
            {
                $duration = 0;
            }
            
            $createProduct = $stripe->products->create([
                'images'    => [$this->appsetting->logo_path],
                'name'      => ucfirst(str_replace('_', ' ', $request->type_of_package)),
                'type'      => 'service',
                'active'    => ($request->is_published==1) ? true : false
            ]);

            if($request->subscription>0) {
                $amount = $request->subscription;
            } else {
                $amount = $request->price;
            }

            $plan = $stripe->plans->create([
                'amount'          => $amount * 100,
                'currency'        => $this->paymentInfo->stripe_currency,
                'interval'        => 'day',
                'interval_count'  => $duration,
                'product'         => $createProduct->id,
            ]);

            $package  = new Package;
            $package->module                    = $request->module;
            $package->package_for               = $request->package_for;
            $package->type_of_package           = $request->type_of_package;
            $package->slug                      = Str::slug($request->type_of_package);
            $package->job_ads                   = $request->job_ads;
            $package->publications_day          = $request->publications_day;
            $package->duration                  = $request->duration;
            $package->cvs_view                  = $request->cvs_view;
            $package->employees_per_job_ad      = $request->employees_per_job_ad;
            $package->no_of_boost               = $request->no_of_boost;
            $package->boost_no_of_days          = $request->boost_no_of_days;
            $package->most_popular              = $request->most_popular;
            $package->most_popular_no_of_days   = $request->most_popular_no_of_days;
            $package->top_selling               = $request->top_selling;
            $package->top_selling_no_of_days    = $request->top_selling_no_of_days;
            $package->price                     = $request->price;
            $package->start_up_fee              = $request->start_up_fee;
            $package->subscription              = $request->subscription;
            $package->commission_per_sale       = $request->commission_per_sale;
            $package->number_of_contest         = $request->number_of_contest;
            $package->number_of_event           = $request->number_of_event;
            $package->number_of_product         = $request->number_of_product;
            $package->number_of_service         = $request->number_of_service;
            $package->number_of_book            = $request->number_of_book;
            $package->notice_month              = $request->notice_month;
            $package->locations                 = $request->locations;
            $package->organization              = $request->organization;
            $package->attendees                 = $request->attendees;
            $package->range_of_age              = $request->range_of_age;
            $package->cost_for_each_attendee    = $request->cost_for_each_attendee;
            $package->top_up_fee                = $request->top_up_fee;
            $package->is_published              = $request->is_published;
            $package->stripe_plan_id            = $plan->id;
            if($request->is_published)
            {
                $package->published_at = date('Y-m-d');
            }
            $package->save();
            DB::commit();
            return response()->json(prepareResult(false, $package, getLangByLabelGroups('messages','messages_package_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function show($id)
    {
        try
        {
            $package = Package::find($id);
            return response(prepareResult(false, $package, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function update(Request $request, Package $package)
    {
        $validation = Validator::make($request->all(), [
            'module'            => 'required',
            'package_for'       => 'required',
            'type_of_package'   => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }
        DB::beginTransaction();
        try
        {
            $stripe = new \Stripe\StripeClient($this->paymentInfo->payment_gateway_secret);

            $duration = $request->duration;
            if($request->duration==999)
            {
                $duration = 0;
            }

            if($duration!=0)
            {
                if(empty($package->stripe_plan_id))
                {
                    $createProduct = $stripe->products->create([
                        'images'    => [$this->appsetting->logo_path],
                        'name'      => ucfirst(str_replace('_', ' ', $request->type_of_package)),
                        'type'      => 'service',
                        'active'    => ($request->is_published==1) ? true : false
                    ]);

                    if($request->subscription>0) {
                        $amount = $request->subscription;
                    } else {
                        $amount = $request->price;
                    }

                    $plan = $stripe->plans->create([
                        'amount'          => $amount * 100,
                        'currency'        => $this->paymentInfo->stripe_currency,
                        'interval'        => 'day',
                        'interval_count'  => $duration,
                        'product'         => $createProduct->id,
                    ]);
                    $package->stripe_plan_id = $plan->id;
                }
                else
                {
                    $planInfo = $stripe->plans->retrieve(
                        $package->stripe_plan_id,
                        []
                    );
                    $productInfo = $planInfo->product;
                    $createProduct = $stripe->products->update(
                        $productInfo,
                        ['active'    => ($request->is_published==1) ? true : false]
                    );

                    if($request->subscription>0) {
                        $amount = $request->subscription;
                    } else {
                        $amount = $request->price;
                    }

                    if($package->subscription>0) {
                        $packageAmount = $package->subscription;
                    } else {
                        $packageAmount = $package->price;
                    }

                    if($package->duration != $request->duration || $packageAmount != ($amount * 100))
                    {
                        if($duration!=0)
                        {
                            $stripe->plans->update(
                                $package->stripe_plan_id,
                                ['active' => false]
                            );

                            $plan = $stripe->plans->create([
                                'amount'          => $request->subscription * 100,
                                'currency'        => $this->paymentInfo->stripe_currency,
                                'interval'        => 'day',
                                'interval_count'  => $duration,
                                'product'         => $createProduct->id,
                            ]);
                            $package->stripe_plan_id = $plan->id;
                        }
                    }
                }
            }

            $package->job_ads                   = $request->job_ads;
            $package->publications_day          = $request->publications_day;
            $package->duration                  = $request->duration;
            $package->cvs_view                  = $request->cvs_view;
            $package->employees_per_job_ad      = $request->employees_per_job_ad;
            $package->no_of_boost               = $request->no_of_boost;
            $package->boost_no_of_days          = $request->boost_no_of_days;
            $package->most_popular              = $request->most_popular;
            $package->most_popular_no_of_days   = $request->most_popular_no_of_days;
            $package->top_selling               = $request->top_selling;
            $package->top_selling_no_of_days    = $request->top_selling_no_of_days;
            $package->price                     = $request->price;
            $package->start_up_fee              = $request->start_up_fee;
            $package->subscription              = $request->subscription;
            $package->commission_per_sale       = $request->commission_per_sale;
            $package->number_of_contest         = $request->number_of_contest;
            $package->number_of_event           = $request->number_of_event;
            $package->number_of_product         = $request->number_of_product;
            $package->number_of_service         = $request->number_of_service;
            $package->number_of_book            = $request->number_of_book;
            $package->notice_month              = $request->notice_month;
            $package->locations                 = $request->locations;
            $package->organization              = $request->organization;
            $package->attendees                 = $request->attendees;
            $package->range_of_age              = $request->range_of_age;
            $package->cost_for_each_attendee    = $request->cost_for_each_attendee;
            $package->top_up_fee                = $request->top_up_fee;
            $package->is_published              = $request->is_published;

            if($request->is_published)
            {
                $package->published_at = date('Y-m-d');
            }
            $package->save();
            DB::commit();
            return response()->json(prepareResult(false, $package, getLangByLabelGroups('messages','messages_package_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function destroy(Package $package)
    {
        if(UserPackageSubscription::where('package_id',$package->id)->count() > 0)
        {
            return response()->json(prepareResult(true, ['package subscribed'], getLangByLabelGroups('messages','messages_can\'t_delete')), config('http_response.success'));
        }
        if(!empty($package->stripe_plan_id)) {
            $stripe = new \Stripe\StripeClient($this->paymentInfo->payment_gateway_secret);
            $stripe->plans->delete(
                $package->stripe_plan_id,
                []
            );
        }
        $package->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_package_deleted')), config('http_response.success'));
    }

    public function purchasedPackage(Request $request,$id)
    {
        try
        {

            if(!empty('per_page_record'))
            {
                $package = UserPackageSubscription::select('auto_id','id','user_id','package_id','subscription_id','payby','package_valid_till','subscription_status','module','type_of_package','job_ads','publications_day','duration','cvs_view','employees_per_job_ad','no_of_boost','boost_no_of_days','most_popular','most_popular_no_of_days','top_selling','top_selling_no_of_days','price','start_up_fee','subscription','commission_per_sale','number_of_product','number_of_service','top_up_fee','used_no_of_boost','used_most_popular','used_top_selling','used_job_ads','used_cvs_view','number_of_contest','number_of_event','used_number_of_contest','used_number_of_event','number_of_book','used_number_of_product','used_number_of_service','used_number_of_book')->where('package_id',$id)->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $package = UserPackageSubscription::select('auto_id','id','user_id','package_id','subscription_id','payby','package_valid_till','subscription_status','module','type_of_package','job_ads','publications_day','duration','cvs_view','employees_per_job_ad','no_of_boost','boost_no_of_days','most_popular','most_popular_no_of_days','top_selling','top_selling_no_of_days','price','start_up_fee','subscription','commission_per_sale','number_of_product','number_of_service','top_up_fee','used_no_of_boost','used_most_popular','used_top_selling','used_job_ads','used_cvs_view','number_of_contest','number_of_event','used_number_of_contest','used_number_of_event','number_of_book','used_number_of_product','used_number_of_service','used_number_of_book')->where('package_id',$id)->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path')->get();
            }
            
            return response(prepareResult(false, $package, getLangByLabelGroups('messages','message_package_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}
