<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contest;

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
        $contests = Contest::where('application_end_date',date('Y-m-d'))->get();
          foreach($contests as $contest) {
            if($contest->contestApplications->count() < $contest->min_participants){

                $contest->update(['status' => 'hold']);
                // Notification Start

                $title = 'Contest Status update';
                $body =  'Status for Contest '.$contest->title.' is updated to on hold because minimum participants didnt came. Edit your contest or refund the applicants.';
                $user = $contest->user;
                $type = 'Contest On Hold';
                pushNotification($title,$body,$user,$type,true,'seller','contest',$contest->id,'created');

                // Notification End
            }
          }
        return 0;
    }
}
