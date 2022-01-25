<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contest;
use App\Models\ContestApplication;
use App\Models\OrderItem;


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
        $contests = Contest::where('application_end_date','<',date('Y-m-d'))->where('status','verified')->get();
          foreach($contests as $contest) {
            $contest->update(['status' => 'expired']);
            // Notification Start

            $title = 'Contest expired';
            $body =  'Status for Contest '.$contest->title.' is updated to expired.';
            $user = $contest->user;
            $type = 'Contest Expired';
            pushNotification($title,$body,$user,$type,true,'seller','contest',$contest->id,'created');
        }

        $contests = Contest::where('start_date','<',date('Y-m-d'))->whereIn('status',['verified','expired'])->get();
          foreach($contests as $contest) 
          {
            $contest->update(['status' => 'completed']);
            ContestApplication::where('contest_id',$contest->id)->where('application_status','joined')->where('payment_status','paid')->update(['application_status'=>'completed']);

            //OrderStatus Update
            $getAllContApplications = ContestApplication::select('id')->where('contest_id',$contest->id)->where('payment_status','paid')->where('application_status','completed')->get();
            foreach($getAllContApplications as $key => $apllications)
            {
                $changeOrderStatus = OrderItem::where('contest_application_id', $apllications->id)->first();
                if($changeOrderStatus)
                {
                    $changeOrderStatus->item_status = 'completed';
                    $changeOrderStatus->delivery_completed_date = date('Y-m-d H:i:s');
                    if(!empty($changeOrderStatus->user) && $changeOrderStatus->user->user_type_id==2)
                    {
                        $changeOrderStatus->return_applicable_date = date('Y-m-d');
                    }
                    $changeOrderStatus->save();
                }
            }
            
            // Notification Start

            $title = 'Contest completed';
            $body =  'Status for Contest '.$contest->title.' is updated to completed.';
            $user = $contest->user;
            $type = 'Contest completed';
            pushNotification($title,$body,$user,$type,true,'seller','contest',$contest->id,'created');
        }
    }
}
