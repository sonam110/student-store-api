<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderItem;
use App\Models\User;

class RewardPointsCredit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rewardPoints:credit';

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
        $date = date('Y-m-d',strtotime('-14 days'));
        $orderItems = OrderItem::whereDate('return_applicable_date','<=',$date)->whereNotNull('return_applicable_date')->where('reward_point_status','pending')->where('is_replaced','0')->where('is_returned','0')->where('is_disputed','0')->get();
          foreach($orderItems as $orderItem) {
            $orderItem->update(['reward_point_status' => 'credited']);

            $user = User::find($orderItem->user_id);

            User::find($orderItem->user_id)->update(['reward_points' => $user->reward_points + $orderItem->earned_reward_points]);


            // Notification Start

            $title = 'Reward Points Credited';
            $body =  $orderItem->earned_reward_points.' Reward Points Credited for your order of product '.$orderItem->title.'.';
            $user = $user;
            $type = 'Reward Points';
            pushNotification($title,$body,$user,$type,true,'buyer','orderItem',$orderItem->id,'my-orders');
          }
        return 0;
    }
}
