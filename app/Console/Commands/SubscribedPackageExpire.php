<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserPackageSubscription;

class SubscribedPackageExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscribedPackage:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

    function dateDiffInDays($date1, $date2) 
    {
        // Calculating the difference in timestamps
        $diff = strtotime($date2) - strtotime($date1);
          
        // 1 day = 24 hours
        // 24 * 60 * 60 = 86400 seconds
        return abs(round($diff / 86400));
    }

    public function handle()
    {
        $sixDays = date('Y-m-d',strtotime('+6days'));
        $fourDays = date('Y-m-d',strtotime('+4days'));
        $twoDays = date('Y-m-d',strtotime('+2days'));
        $toDays = date('Y-m-d');
        $subscribedPackages = UserPackageSubscription::select('id','package_valid_till','module','user_id','package_id')->whereIn(\DB::raw("DATE(package_valid_till)"),[$sixDays,$fourDays,$twoDays,$toDays])->with('user:id,first_name,last_name')->get();
        foreach($subscribedPackages as $subscribedPackage) {
            $days = $this->dateDiffInDays($toDays, $subscribedPackage->package_valid_till);
            $title = 'Subscribed Package Expiring';
            $body =  'Subscribed Package for '.$subscribedPackage->module.' is expiring in '.$days.' days.';
            $user = $subscribedPackage->user;
            $type = 'Package Expire';
            if($subscribedPackage->module == 'product' || $subscribedPackage->module == 'service')
            {
                $module = 'product_service';
            }
            else
            {
                $module = $subscribedPackage->module;
            }
            pushNotification($title,$body,$user,$type,true,'seller',$module,$subscribedPackage->id,'landing-page');
        }
        return 0;
    }
}
