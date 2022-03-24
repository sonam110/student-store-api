<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contest;
use App\Models\ContestApplication;
use App\Models\OrderItem;
use Log;

class ContestHoldToCanceled extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holdto:canceled';

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
        $contests = Contest::where('status', 'hold')->get();
        foreach ($contests as $key => $contest) 
        {
            if(empty($contest->contest_hold_date))
            {
                $contest->contest_hold_date = $contest->start_date;
                $contest->save();
            }
            else
            {
                $autoCanceledDate = date("Y-m-d", strtotime("7 days", strtotime($contest->contest_hold_date)));
                if(strtotime($autoCanceledDate) <= strtotime(date('Y-m-d')))
                {
                    //refund if contest is on hold and not taking any action within 7 days from contest_hold_date
                    $contestApplications = $contest->contestApplications()
                        ->where('application_status', '!=', 'canceled')
                        ->where('payment_status', 'paid')
                        ->get();

                    foreach($contestApplications as $contestApplication) 
                    {
                        //full refund
                        $orderedItem = OrderItem::where('contest_id', $contestApplication->contest_id)
                            ->where('user_id', $contestApplication->user_id)
                            ->where('contest_application_id', $contestApplication->id)
                            ->first();
                        if($orderedItem)
                        {
                            $refundOrderItemId = $orderedItem->id;
                            $refundOrderItemPrice = $orderedItem->price_after_apply_reward_points;
                            $refundOrderItemQuantity = $orderedItem->quantity;
                            $refundOrderItemReason = 'automatically canceled';

                            $isRefunded = refund($refundOrderItemId,$refundOrderItemPrice,$refundOrderItemQuantity,$refundOrderItemReason);

                            if($isRefunded=='failed')
                            {
                                Log::error('payment not refunded. please check Log');
                                Log::info($orderedItem);
                            }
                            else
                            {
                                //update contest application status
                                $contestApplication->application_status = 'canceled';
                                $contestApplication->save();

                                //update order item information
                                $orderedItem->canceled_refunded_amount = $refundOrderItemPrice * $refundOrderItemQuantity;
                                $orderedItem->returned_rewards = ceil($orderedItem->used_item_reward_points / $refundOrderItemQuantity);
                                $orderedItem->earned_reward_points = 0;
                                $orderedItem->reward_point_status = 'completed';
                                $orderedItem->item_status = 'canceled';

                                //update status of payment
                                $orderedItem->amount_transferred_to_vendor = '0';
                                $orderedItem->student_store_commission = '0';
                                $orderedItem->cool_company_commission = '0';
                                $orderedItem->student_store_commission_percent = '0';
                                $orderedItem->cool_company_commission_percent = '0';
                                $orderedItem->vat_amount = '0';
                                $orderedItem->save();
                            }
                        }
                        $contestApplication->application_status = 'canceled';
                        $contestApplication->save();
                    }

                    //change status hold to canceled
                    $contest->status = 'canceled';
                    $contest->save();

                    // Notification Start
                    $title = 'Contest is canceled';
                    $body =  'Contest/Event: '.$contest->title.' Status is canceled because you have not taken any action within 7 days. And the full amount has been refunded to all the participants.';

                    $user = $contest->user;
                    $type = 'Contest canceled';
                    pushNotification($title,$body,$user,$type,true,'seller','contest',$contest->id,'created');
                    // Notification End
                }
            }
            \Log::channel('cron')->info('holdto:canceled command executed successfully.');
        }
    }
}
