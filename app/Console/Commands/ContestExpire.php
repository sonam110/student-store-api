<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contest;

class ContestExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contest:expire';

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
        $contests = Contest::where('application_end_date','<=',date('Y-m-d'))->get();
          foreach($contests as $contest) {
            $contest->update(['status' => 'expired']);
            // Notification Start

            $title = 'Contest Status update';
            $body =  'Status for Contest '.$contest->title.' is updated to expired.';
            $user = $contest->user;
            $type = 'Contest Expired';
            pushNotification($title,$body,$user,$type,true,'seller','contest',$contest->id,'created');
          }
    }
}
