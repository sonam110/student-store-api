<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Stripe;
use App\Models\Package;
use App\Models\AppSetting;
use App\Models\PaymentGatewaySetting;

class AddPackagesOnStripeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $appsetting = AppSetting::select('logo_path')->first();
        $paymentInfo = PaymentGatewaySetting::first();

        $packages = Package::orderBy('module','ASC')->get();
            foreach($packages as $package)
            {
                if($package->subscription>0 || $package->price>0)
                {
                    $stripe = new \Stripe\StripeClient($paymentInfo->payment_gateway_secret);

                    $createProduct = $stripe->products->create([
                        'images'    => [$appsetting->logo_path],
                        'name'      => ucfirst(str_replace('_', ' ', $package->type_of_package)).':'.$package->module,
                        'type'      => 'service',
                        'active'    => ($package->is_published==1) ? true : false
                    ]);

                    if($package->subscription>0) {
                        $amount = $package->subscription;
                    } else {
                        $amount = $package->price;
                    }
                    return [
                        'amount'          => $amount * 100,
                        'currency'        => $paymentInfo->stripe_currency,
                        'interval'        => 'day',
                        'interval_count'  => $package->duration,
                        'product'         => $createProduct->id,
                    ];
                    $plan = $stripe->plans->create([
                        'amount'          => $amount * 100,
                        'currency'        => $paymentInfo->stripe_currency,
                        'interval'        => 'day',
                        'interval_count'  => $package->duration,
                        'product'         => $createProduct->id,
                    ]);
                    \Log::info($plan);
                    $package->stripe_plan_id = $plan->id;
                    $package->save();
                }
            }
    }
}
