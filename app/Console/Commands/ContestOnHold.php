<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contest;
use Log;

class ContestOnHold extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contests:onHold';

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
        $contests = Contest::where('application_end_date', date('Y-m-d'))->get();
        foreach($contests as $contest) {
            if(empty($contest->min_participants)) {
                $min_participants = 0;
            } else {
                $min_participants = $contest->min_participants;
            }
            if($contest->contestApplications()->where('payment_status', 'paid')->count() < $min_participants) {

                $contest->update([
                    'status' => 'hold',
                    'contest_hold_date' => date('Y-m-d')
                ]);

                // Notification Start
                $title = 'Contest on hold';
                $body =  'Status for Contest/event '.$contest->title.' is updated to on hold because minimum participants didn\'t came. Edit your contest or refund the applicants. if you do not take any action within 7 days the contest/event will be automatically canceled and the full amount will be refunded to all the participants.';
                $user = $contest->user;
                $type = 'Contest On Hold';
                pushNotification($title,$body,$user,$type,true,'seller','contest',$contest->id,'created');
                // Notification End
            }
        }

        \Log::channel('cron')->info('contests:onHold command executed successfully.');
        return 0;
    }
}
