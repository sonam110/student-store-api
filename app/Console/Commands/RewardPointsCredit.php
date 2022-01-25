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
        $orderItems = OrderItem::select('order_items.*')
            ->join('orders', 'orders.id','=','order_items.order_id')
            ->whereDate('return_applicable_date','<=',$date)
            ->whereNotNull('return_applicable_date')
            ->where('orders.payment_status', 'paid')
            ->where('earned_reward_points', '>', 0)
            ->where('reward_point_status','pending')
            ->whereIn('item_status',['completed', 'replaced', 'returned'])
            ->whereRaw("(CASE WHEN is_disputed = 1 THEN disputes_resolved_in_favour = 1 ELSE is_disputed=0 END)")
            ->get();
          foreach($orderItems as $orderItem) 
          {
            /*
            $creditRewardPoint = 0;
            $checkReturnQty = 0;
            $checkReplacedQty = 0;
            $creditReturnPoint = 0;

            $oneItemRewardValue = ceil($orderItem->earned_reward_points / $orderItem->quantity);

            // if returned
            if($orderItem->is_returned==1)
            {
                $checkReturnQty = $orderItem->return->quantity;
            }

            // if replaced
            if($orderItem->is_replaced==1)
            {
                $checkReplacedQty = $orderItem->replacement->quantity;
            }

            $remainingOrderActiveQty = ($orderItem->quantity - ($checkReturnQty + $checkReplacedQty));
            if($remainingOrderActiveQty>0)
            {
                $creditReturnPoint = $remainingOrderActiveQty * $oneItemRewardValue;
            }
            */


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
