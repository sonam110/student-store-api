<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ContestOnHold;
use App\Console\Commands\ContestStart;
// use App\Console\Commands\ResumePdfGenerate;
use App\Console\Commands\SubscribedPackageExpire;
use App\Console\Commands\ContestExpire;
use App\Console\Commands\JobExpire;
use App\Console\Commands\OrderStatus;
use App\Console\Commands\RewardPointsCredit;
use App\Console\Commands\CoolCompanyCreateAssignment;
use App\Console\Commands\CoolCompanyRegFreelancer;
use App\Console\Commands\StripeFundTransferred;
use App\Console\Commands\StripeAccountStatusCheck;
use App\Console\Commands\AutoSwichPackageIfExpired;
use App\Console\Commands\ExportFileRemove;
use App\Console\Commands\CheckSwishPaymentStatus;


class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ContestOnHold::class,
        ContestStart::class,
        // ResumePdfGenerate::class,
        SubscribedPackageExpire::class,
        ContestExpire::class,
        JobExpire::class,
        OrderStatus::class,
        RewardPointsCredit::class,
        CoolCompanyCreateAssignment::class,
        CoolCompanyRegFreelancer::class,
        StripeFundTransferred::class,
        StripeAccountStatusCheck::class,
        AutoSwichPackageIfExpired::class,
        ExportFileRemove::class,
        CheckSwishPaymentStatus::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('contest:expire')->dailyAt('00:30');
        $schedule->command('contests:onHold')->dailyAt('23:59');
        $schedule->command('contests:start')->dailyAt('00:01');

        $schedule->command('job:expire')->dailyAt('00:01');
        //$schedule->command('order:status')->hourly();
        $schedule->command('rewardPoints:credit')->dailyAt('00:01');

        $schedule->command('create:freelancer')->hourly();
        $schedule->command('create:assignment')->dailyAt('02:00');
        $schedule->command('stripe:account')->hourly();
        $schedule->command('stripevendor:findtransfer')->dailyAt('03:00');
        
        $schedule->command('subscribedPackage:expire')->dailyAt('09:00');
        $schedule->command('expiredpackage:switchtofree')->dailyAt('00:01');
        $schedule->command('file:remove')->dailyAt('02:00');
        
        $schedule->command('checkswish:paymentstatus')->everyMinute();;
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
