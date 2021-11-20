<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserPackageSubscription;
use App\Models\Package;

class AutoSwichPackageIfExpired extends Command
{
    protected $signature = 'expiredpackage:switchtofree';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $subscribedPackages = UserPackageSubscription::select('id','package_valid_till','module','user_id','package_id')
            ->where('subscription_status', 1)
            ->whereDate('package_valid_till', '<', date('Y-m-d'))
            ->get();
        foreach($subscribedPackages as $key => $package)
        {
            $package->subscription_status = 0;
            $package->save();
            if($package)
            {
                $checkUserType = $package->user->user_type_id;
                $package_for = 'other';
                if($checkUserType==2) {
                    $package_for = 'student';
                }
                $packageModuleType = $package->package->module;
                if($packageModuleType=='Job')
                {
                    $getFreePackage = Pakage::where('module', $packageModuleType)
                        ->where('package_for','packages_free')
                        ->where('type_of_package','packages_free')
                        ->first();
                }
                elseif($packageModuleType=='Product')
                {

                }
                $userPackageSubscription                        = new UserPackageSubscription;
                $userPackageSubscription->user_id               = Auth::id();
                $userPackageSubscription->subscription_id       = $request->subscription_id;
                $userPackageSubscription->payby                 = $request->payby;
                $userPackageSubscription->package_id            = $user_package;
                $userPackageSubscription->package_valid_till    = date('Y-m-d',strtotime('+'.$package->duration .'days'));
                $userPackageSubscription->subscription_status   = 1;
                $userPackageSubscription->module                = $package->module;
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
                $userPackageSubscription->top_selling           = $package->top_selling;
                $userPackageSubscription->top_selling_no_of_days= $package->top_selling_no_of_days;
                $userPackageSubscription->price                 = $package->price;
                $userPackageSubscription->start_up_fee          = $package->start_up_fee;
                $userPackageSubscription->subscription          = $package->subscription;
                $userPackageSubscription->commission_per_sale   = $package->commission_per_sale;
                $userPackageSubscription->number_of_product     = $package->number_of_product;
                $userPackageSubscription->number_of_service     = $package->number_of_service;
                $userPackageSubscription->number_of_book        = $package->number_of_book;
                $userPackageSubscription->number_of_contest     = $package->number_of_contest;
                $userPackageSubscription->number_of_event       = $package->number_of_event;
                $userPackageSubscription->notice_month          = $package->notice_month;
                $userPackageSubscription->locations             = $package->locations;
                $userPackageSubscription->organization          = $package->organization;
                $userPackageSubscription->attendees             = $package->attendees;
                $userPackageSubscription->range_of_age          = $package->range_of_age;
                $userPackageSubscription->cost_for_each_attendee= $package->cost_for_each_attendee;
                $userPackageSubscription->top_up_fee            = $package->top_up_fee;
                $userPackageSubscription->save();
            }
        }
        return 0;
    }
}
