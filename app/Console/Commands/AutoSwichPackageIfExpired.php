<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserPackageSubscription;
use App\Models\Package;
use App\Mail\OrderMail;
use mervick\aesEverywhere\AES256;
use App\Models\EmailTemplate;
use Mail;
use Log;

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
                $getFreePackage = Package::where('module', $packageModuleType)
                    ->where('package_for', $package_for)
                    ->where('type_of_package','packages_free')
                    ->first();
                if($getFreePackage)
                {
                    $userPackageSubscription = new UserPackageSubscription;
                    $userPackageSubscription->user_id = $package->user_id;
                    $userPackageSubscription->subscription_id = null;
                    $userPackageSubscription->payby = null;
                    $userPackageSubscription->package_id = $getFreePackage->id;
                    $userPackageSubscription->package_valid_till = date('Y-m-d',strtotime('+'.$getFreePackage->duration .'days'));
                    $userPackageSubscription->subscription_status = 1;
                    $userPackageSubscription->module = $getFreePackage->module;
                    $userPackageSubscription->type_of_package = $getFreePackage->type_of_package;
                    $userPackageSubscription->job_ads = $getFreePackage->job_ads;
                    $userPackageSubscription->publications_day = $getFreePackage->publications_day;
                    $userPackageSubscription->duration = $getFreePackage->duration;
                    $userPackageSubscription->cvs_view = $getFreePackage->cvs_view;
                    $userPackageSubscription->employees_per_job_ad = $getFreePackage->employees_per_job_ad;
                    $userPackageSubscription->no_of_boost = $getFreePackage->no_of_boost;
                    $userPackageSubscription->boost_no_of_days = $getFreePackage->boost_no_of_days;
                    $userPackageSubscription->most_popular = $getFreePackage->most_popular;
                    $userPackageSubscription->most_popular_no_of_days = $getFreePackage->most_popular_no_of_days;
                    $userPackageSubscription->top_selling = $getFreePackage->top_selling;
                    $userPackageSubscription->top_selling_no_of_days = $getFreePackage->top_selling_no_of_days;
                    $userPackageSubscription->price = $getFreePackage->price;
                    $userPackageSubscription->start_up_fee = $getFreePackage->start_up_fee;
                    $userPackageSubscription->subscription = $getFreePackage->subscription;
                    $userPackageSubscription->commission_per_sale = $getFreePackage->commission_per_sale;
                    $userPackageSubscription->number_of_product = $getFreePackage->number_of_product;
                    $userPackageSubscription->number_of_service = $getFreePackage->number_of_service;
                    $userPackageSubscription->number_of_book = $getFreePackage->number_of_book;
                    $userPackageSubscription->number_of_contest = $getFreePackage->number_of_contest;
                    $userPackageSubscription->number_of_event = $getFreePackage->number_of_event;
                    $userPackageSubscription->notice_month = $getFreePackage->notice_month;
                    $userPackageSubscription->locations = $getFreePackage->locations;
                    $userPackageSubscription->organization = $getFreePackage->organization;
                    $userPackageSubscription->attendees = $getFreePackage->attendees;
                    $userPackageSubscription->range_of_age = $getFreePackage->range_of_age;
                    $userPackageSubscription->cost_for_each_attendee = $getFreePackage->cost_for_each_attendee;
                    $userPackageSubscription->top_up_fee = $getFreePackage->top_up_fee;
                    $userPackageSubscription->save();

                    $user = $package->user;

                    //Mail Start
                    $body = "Package";
                    $emailTemplate = EmailTemplate::where('template_for','package_upgrade')->where('language_id',$user->language_id)->first();
                    if(!$emailTemplate)
                    {
                        $emailTemplate = EmailTemplate::where('template_for','package_upgrade')->first();
                    }
                    $body = $emailTemplate->body;

                    $arrayVal = [
                        '{{user_name}}' => AES256::decrypt($user->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($user->last_name, env('ENCRYPTION_KEY')),
                        '{{module}}' =>     $userPackageSubscription->module,
                        '{{valid_till}}' => $userPackageSubscription->package_valid_till,
                        '{{package_type}}' => $userPackageSubscription->type_of_package,
                    ];
                    $body = strReplaceAssoc($arrayVal, $body);
                    
                    $details = [
                        'title' => $emailTemplate->subject,
                        'body' => $body
                    ];
                    
                    Mail::to(AES256::decrypt($user->email, env('ENCRYPTION_KEY')))->send(new OrderMail($details));
                }
            }
        }
        return 0;
    }
}
