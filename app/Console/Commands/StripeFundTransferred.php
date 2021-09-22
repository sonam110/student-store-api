<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe;

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
        $payout = \Stripe\Transfer::create([
          "amount" => 999,
          "currency" => "USD",
          "destination" => "acct_1Jc7iyRgancAKpJI",
          "transfer_group" => "ORDER_95"
        ]);
    }
}
