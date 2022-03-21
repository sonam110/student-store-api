<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contest;

class ChangeContestHoldToVerified extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contest:autoverified';

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
    public function handle()
    {
        $oneDayBefore = date('Y-m-d', strtotime("1 days"));
        $contests = Contest::whereDate('start_date', $oneDayBefore)
            ->where('status','pending')
            ->withCount('contestApplications')
            ->get();
        foreach($contests as $contest) 
        {
            if($contest->contest_applications_count > 0)
            {
                $contest->update(['status' => 'verified']);

                $title = 'Contest verified';
                $body =  'Status for Contest '.$contest->title.' is updated to verified.';
                $user = $contest->user;
                $type = 'Contest verified';
                pushNotification($title,$body,$user,$type,true,'seller','contest',$contest->id,'created');
            } 
        }

        \Log::channel('cron')->info('contest:autoverified command executed successfully.');
        return true;
    }
}
