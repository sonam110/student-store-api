<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contest;
use DateTime;

class ContestStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contests:start';

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
        $contests = Contest::whereBetween('start_date',[date('Y-m-d'),date('Y-m-d',strtotime('+3 days'))])->get();
          foreach($contests as $contest) {
            if($contest->contestApplications->count() > 0){
                $start_date = new DateTime($contest->start_date);
                $diffInDays = ($start_date->diff(\Carbon\Carbon::now())->days) + 1;
                $title = $diffInDays.' days left for contest';
                $body =  $contest->title.' will start in '.$diffInDays.' days.';
                $type = 'Contest Start Date';
                if($contest->start_date == date('Y-m-d'))
                {
                    $title = '0 days left for contest';
                    $body =  $contest->title.' will start today at '.\Carbon\Carbon::parse($contest->start_time)->format('h:i A').'.';
                }
                $contestApplications = $contest->contestApplications()->where('payment_status', 'paid')->get();
                foreach ($contestApplications as $key => $applications) {
                    
                    $user = $applications->user;
                    pushNotification($title,$body,$user,$type,true,'buyer','contest',$contest->id,'joined');
                }
            }
          }
        return 0;
    }
}
