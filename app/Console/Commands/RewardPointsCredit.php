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
        //select order_number, `order_items`.* from `order_items` inner join `orders` on `orders`.`id` = `order_items`.`order_id` where date(`return_applicable_date`) <= "2022-02-05" and `order_items`.`return_applicable_date` is not null and `orders`.`payment_status` = "paid" and `order_items`.`earned_reward_points` > 0 and `order_items`.`reward_point_status` = "pending" and `order_items`.`item_status` in ('completed', 'replaced', 'returned') and (CASE WHEN order_items.is_disputed = 1 THEN order_items.disputes_resolved_in_favour = 1 ELSE order_items.is_disputed=0 END)

        $date = date('Y-m-d',strtotime('-14 days'));
        $orderItems = OrderItem::select('order_items.*')
            ->join('orders', 'orders.id','=','order_items.order_id')
            ->whereDate('return_applicable_date','<=',$date)
            ->whereNotNull('order_items.return_applicable_date')
            ->where('orders.payment_status', 'paid')
            ->where('order_items.earned_reward_points', '>', 0)
            ->where('order_items.reward_point_status','pending')
            ->whereIn('order_items.item_status',['completed', 'replaced', 'returned'])
            ->whereRaw("(CASE WHEN order_items.is_disputed = 1 THEN order_items.disputes_resolved_in_favour = 1 ELSE order_items.is_disputed=0 END)")
            ->get();
          foreach($orderItems as $orderItem) 
          {
            $orderItem->update(['reward_point_status' => 'credited']);
            $user = User::find($orderItem->user_id);
            User::find($orderItem->user_id)->update(['reward_points' => $user->reward_points + $orderItem->earned_reward_points]);

            //reward credit log
            rewardCreditLog($orderItem->user_id,$orderItem->id,$orderItem->earned_reward_points);

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
