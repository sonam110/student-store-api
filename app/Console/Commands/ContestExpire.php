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
        // $contests = Contest::whereDate('application_end_date','<',date('Y-m-d'))
        //     ->where('status','verified')
        //     ->get();
        // foreach($contests as $contest) {
        //     $contest->update(['status' => 'expired']);
        //     // Notification Start

        //     $title = 'Contest expired';
        //     $body =  'Status for Contest '.$contest->title.' is updated to expired.';
        //     $user = $contest->user;
        //     $type = 'Contest Expired';
        //     pushNotification($title,$body,$user,$type,true,'seller','contest',$contest->id,'created');
        // }

        // $contests = Contest::whereDate('start_date','<',date('Y-m-d'))
        //     ->whereIn('status',['verified','expired'])
        //     ->get();
        //   foreach($contests as $contest) 
        //   {
        //     $contest->update(['status' => 'completed']);

        //     ContestApplication::where('contest_id',$contest->id)
        //         ->whereIn('application_status',['joined','approved'])
        //         ->where('payment_status','paid')
        //         ->update(['application_status'=>'completed']);

        //     //OrderStatus Update
        //     $getAllContApplications = ContestApplication::select('id')
        //         ->where('contest_id',$contest->id)
        //         ->where('payment_status','paid')
        //         ->where('application_status','completed')
        //         ->get();
        //     foreach($getAllContApplications as $key => $apllications)
        //     {
        //         $changeOrderStatus = OrderItem::where('contest_application_id', $apllications->id)
        //         ->first();
        //         if($changeOrderStatus)
        //         {
        //             $changeOrderStatus->item_status = 'completed';
        //             $changeOrderStatus->delivery_completed_date = date('Y-m-d H:i:s');
        //             if(!empty($changeOrderStatus->user) && $changeOrderStatus->user->user_type_id==2)
        //             {
        //                 $changeOrderStatus->return_applicable_date = date('Y-m-d');
        //             }
        //             $changeOrderStatus->save();
        //         }
        //     }
            
        //     // Notification Start

        //     $title = 'Contest completed';
        //     $body =  'Status for Contest '.$contest->title.' is updated to completed.';
        //     $user = $contest->user;
        //     $type = 'Contest completed';
        //     pushNotification($title,$body,$user,$type,true,'seller','contest',$contest->id,'created');
        // }

        //refund if contest is completed and applicants application status is pending or document_updated or rejected
        $contestApplications = ContestApplication::select('contest_applications.*')
            ->join('contests', function ($join) {
                $join->on('contest_applications.contest_id', '=', 'contests.id');
            })
            ->where('contest_applications.payment_status', 'paid')
            ->where('contests.status', 'completed')
            ->whereDate('contests.start_date','<',date('Y-m-d'))
            ->whereIn('contest_applications.application_status',['pending','document_updated', 'rejected'])
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
        }

        \Log::channel('cron')->info('contest:expire command executed successfully.');
    }
}
