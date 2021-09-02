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

class PackageController extends Controller
{
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
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function destroy(Package $package)
    {
        if(UserPackageSubscription::where('package_id',$package->id)->count() > 0)
        {
            return response()->json(prepareResult(true, ['package subscribed'], getLangByLabelGroups('messages','messages_can\'t_delete')), config('http_response.success'));
        }
        $package->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_package_deleted')), config('http_response.success'));
    }
}
