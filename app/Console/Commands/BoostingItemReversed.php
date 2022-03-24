<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Job;
use App\Models\ProductsServicesBook;
use Log;

class BoostingItemReversed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boosting:update';

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
        //Jobs boosting update
        $jobs = Job::where('is_promoted', 1)
            ->whereDate('promotion_end_date', '<', date('Y-m-d'))
            ->update([
                'is_promoted' => 0,
                'promotion_start_date' => null,
                'promotion_end_date' => null,
            ]);

        //Product boosting update
        //Promotions
        $products = ProductsServicesBook::where('is_promoted', 1)
            ->whereDate('promotion_end_at', '<', date('Y-m-d'))
            ->update([
                'is_promoted' => 0,
                'promotion_start_at' => null,
                'promotion_end_at' => null,
            ]);

        //most_popular
        $products = ProductsServicesBook::where('most_popular', 1)
            ->whereDate('most_popular_end_at', '<', date('Y-m-d'))
            ->update([
                'most_popular' => 0,
                'most_popular_start_at' => null,
                'most_popular_end_at' => null,
            ]);

        //top_selling
        $products = ProductsServicesBook::where('top_selling', 1)
            ->whereDate('top_selling_end_at', '<', date('Y-m-d'))
            ->update([
                'top_selling' => 0,
                'top_selling_start_at' => null,
                'top_selling_end_at' => null,
            ]);

        \Log::channel('cron')->info('boosting:update command executed successfully.');
    }
}
