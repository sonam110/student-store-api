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
        StripeAccountStatusCheck::class,

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
        $schedule->command('contests:onHold')->dailyAt('23:59');
        $schedule->command('contests:start')->dailyAt('00:01');
        // $schedule->command('resumePdf:generate')->dailyAt('00:01');
        $schedule->command('subscribedPackage:expire')->dailyAt('00:01');
        $schedule->command('contest:expire')->dailyAt('00:01');
        $schedule->command('job:expire')->dailyAt('00:01');
        $schedule->command('order:status')->hourly();
        $schedule->command('rewardPoints:credit')->dailyAt('00:01');
        $schedule->command('create:freelancer')->hourly();
        $schedule->command('create:assignment')->dailyAt('02:00');
        $schedule->command('stripe:account')->hourly();
        
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
