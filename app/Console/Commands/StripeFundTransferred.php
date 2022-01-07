<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VendorFundTransfer;
use App\Models\OrderItem;
use App\Models\User;
use Stripe;
use Log;
use App\Models\PaymentGatewaySetting;

class StripeFundTransferred extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripevendor:findtransfer';

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
        $paymentInfo = PaymentGatewaySetting::first();
        \Stripe\Stripe::setApiKey($paymentInfo->payment_gateway_secret);

        $today          = new \DateTime();
        $before15Days   = $today->sub(new \DateInterval('P15D'))->format('Y-m-d');

        $getUserIds = OrderItem::select('users.id as user_id')
            ->join('users', 'users.id','=','order_items.vendor_user_id')
            ->whereNotNull('order_items.vendor_user_id')
            ->whereIn('order_items.item_status',['completed', 'replaced', 'returned'])
            ->whereRaw("(CASE WHEN order_items.is_disputed = 1 THEN order_items.disputes_resolved_in_favour = 1 ELSE order_items.is_disputed=0 END)")
            ->where('order_items.is_transferred_to_vendor', 0)
            ->where('order_items.amount_transferred_to_vendor', '>', 0)
            ->whereDate('order_items.delivery_completed_date', '<=', $before15Days)
            ->orderBy('order_items.auto_id', 'ASC')
            ->groupBy('order_items.vendor_user_id')
            ->get();
        foreach($getUserIds as $user)
        {
            //Get user Stripe Accont
            $userInfo = User::select('stripe_account_id')
                ->where('id', $user->user_id)
                ->where('users.stripe_account_id', '!=', null)
                ->where('users.stripe_status', '3')
                ->first();
            if($userInfo)
            {
                $totalAmountForPaid = 0;
                $orderItemId = [];
                $createOrderUserWise = OrderItem::select('users.id as user_id','order_items.id','order_items.order_id','order_items.products_services_book_id','order_items.amount_transferred_to_vendor','order_items.is_transferred_to_vendor','order_items.fund_transferred_date')
                ->join('users', 'users.id','=','order_items.vendor_user_id')
                ->whereNotNull('order_items.vendor_user_id')
                ->whereIn('order_items.item_status',['completed', 'replaced', 'returned'])
                ->whereRaw("(CASE WHEN order_items.is_disputed = 1 THEN order_items.disputes_resolved_in_favour = 1 ELSE order_items.is_disputed=0 END)")
                ->where('order_items.is_transferred_to_vendor', 0)
                ->where('order_items.amount_transferred_to_vendor', '>', 0)
                ->whereDate('order_items.delivery_completed_date', '<=', $before15Days)
                ->where('order_items.item_status', 'completed')
                ->where('order_items.vendor_user_id', $user->user_id)
                ->get();
                foreach ($createOrderUserWise as $key => $order) {
                    $orderItemId[] = $order->id;
                    $totalAmountForPaid += $order->amount_transferred_to_vendor;
                }
                if($totalAmountForPaid>0)
                {
                    try {
                        $payout = \Stripe\Transfer::create([
                          "amount"          => $totalAmountForPaid * 100,
                          "currency"        => $paymentInfo->stripe_currency,
                          "destination"     => $userInfo->stripe_account_id,
                          "transfer_group"  => "ORDER_PAYMENT_TILL_".$before15Days
                        ]);
                    } catch (\Exception $e) {
                        pushNotification('Insufficient funds',$e, User::orderBy('auto_id','ASC')->first(),'',true,'Admin','Payment','no-data','Admin');
                        Log::info($e);
                        break;
                    }

                    $createLog = new VendorFundTransfer;
                    $createLog->user_id = $user->user_id;
                    $createLog->transfer_group = $payout['transfer_group'];
                    $createLog->transection_id = $payout['id'];
                    $createLog->object = $payout['object'];
                    $createLog->amount = $payout['amount'];
                    $createLog->amount_reversed = $payout['amount_reversed'];
                    $createLog->balance_transaction = $payout['balance_transaction'];
                    $createLog->created = $payout['created'];
                    $createLog->currency = $payout['currency'];
                    $createLog->description = $payout['description'];
                    $createLog->destination = $payout['destination'];
                    $createLog->destination_payment = $payout['destination_payment'];
                    $createLog->livemode = $payout['livemode'];
                    $createLog->reversed = $payout['reversed'];
                    $createLog->source_type = $payout['source_type'];
                    $createLog->complete_response = $payout;
                    $createLog->save();

                    Log::channel('paymentTransferred')->info('User Id:'. $user->user_id);
                    Log::channel('paymentTransferred')->info($payout);

                    if($createLog)
                    {
                        $updateOrderInfo = OrderItem::select('id','is_transferred_to_vendor','fund_transferred_date')->whereIn('id', $orderItemId)->update([
                                'is_transferred_to_vendor'  => '1',
                                'fund_transferred_date'     => date('Y-m-d H:i:s')
                            ]);
                        Log::channel('paymentTransferred')->info('Fund transferred.');
                    }
                }
            }
        }
        return 0;
    }
}
