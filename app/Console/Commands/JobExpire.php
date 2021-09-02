<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Job;

class JobExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:expire';

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
        $jobs = Job::where('application_end_date','<=',date('Y-m-d'))->get();
          foreach($jobs as $job) {
            $job->update(['job_status' => '3']);
            // Notification Start

            $title = 'Job Status update';
            $body =  'Status for Job '.$job->title.' is updated to expired.';
            $user = $job->user;
            $type = 'Job Expired';
            pushNotification($title,$body,$user,$type,true,'seller','job',$job->id,'created');
          }
    }
}
