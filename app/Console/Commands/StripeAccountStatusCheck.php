<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\PaymentGatewaySetting;
use Stripe;
use Log;

class StripeAccountStatusCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:account';

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
        $paymentInfo = PaymentGatewaySetting::select('id','payment_gateway_secret')->first();
        $timeCheck = date("Y-m-d H:i:s", strtotime('-3600 sec', time()));
        $users = User::select('id','stripe_account_id','stripe_status','stripe_create_timestamp')
                ->whereNotNull('stripe_account_id')
                ->where('stripe_create_timestamp', '>=', $timeCheck)
                ->where('stripe_status', '2')
                ->get();
        foreach ($users as $key => $user) 
        {
            $stripe = new \Stripe\StripeClient($paymentInfo->payment_gateway_secret);
            $accountStatus = $stripe->accounts->retrieve(
              $user->stripe_account_id,
              []
            );
            if(is_null($accountStatus->verification->disabled_reason))
            {
                $user->stripe_status = '3';
            }
            else
            {
                $user->stripe_status = '4';
            }
            $user->stripe_create_timestamp = date('Y-m-d H:i:s');
            $user->save();
        }
        \Log::channel('cron')->info('stripe:account command executed successfully.');
    }
}
