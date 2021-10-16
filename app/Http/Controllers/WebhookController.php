<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPackageSubscription;
use App\Models\PaymentGatewaySetting;
use Stripe;
use Log;

class WebhookController extends Controller
{
    function __construct()
    {
        $this->paymentInfo = PaymentGatewaySetting::first();
    }

    public function stripeWebhook(Request $request)
    {
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try
        {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } 
        catch(\UnexpectedValueException $e)
        {
            Log::channel('webhook')->info('Invalid payload');
            Log::channel('webhook')->info($payload);
        }
        catch(\Stripe\Exception\SignatureVerificationException $e)
        {
            Log::channel('webhook')->info('Invalid signature');
            Log::channel('webhook')->info($e);
        }

        /*
        // Handle the event
        switch ($event->type) {
          case 'subscription_schedule.aborted':
            $subscriptionSchedule = $event->data->object;
          case 'subscription_schedule.canceled':
            $subscriptionSchedule = $event->data->object;
          case 'subscription_schedule.completed':
            $subscriptionSchedule = $event->data->object;
          case 'subscription_schedule.created':
            $subscriptionSchedule = $event->data->object;
          // ... handle other event types
          default:
            echo 'Received unknown event type ' . $event->type;
        */

        if ($event->type == "subscription_schedule.aborted" || $event->type == "subscription_schedule.canceled")
        {
            $subscriptionSchedule = $event->data->object;
            $subscription_id = $subscriptionSchedule->subscription;
            $this->abortedSubscription($subscription_id);
            //$this->sendMail();
            //Log::channel('webhook')->info('aborted');
            //Log::channel('webhook')->info($subscriptionSchedule);
        }
        elseif ($event->type == "subscription_schedule.completed")
        {            
            $subscriptionSchedule = $event->data->object;
            $this->completedSubscription($subscription_id);
            //Log::channel('webhook')->info('completed');
            //Log::channel('webhook')->info($subscriptionSchedule);
        }
        elseif ($event->type == "subscription_schedule.created")
        {            
            $subscriptionSchedule = $event->data->object;
            Log::channel('webhook')->info('created');
            Log::channel('webhook')->info($subscriptionSchedule);
        }
        Log::channel('webhook')->info('payload');
        Log::channel('webhook')->info($payload);
    }

    private function abortedSubscription($subscription_id) 
    {
        $subscribedPackage = UserPackageSubscription::where('subscription_id', $subscription_id)->orderBy('auto_id', 'DESC')->first();
        if($subscribedPackage)
        {
            $user_package->is_canceled = 1;
            $user_package->canceled_date = date('Y-m-d');
            $user_package->save();

            $title = 'Package Subscription Canceled';
            $body =  'Your '.$subscribedPackage->package->module.' module '.getLangByLabelGroups('packages', $subscribedPackage->package->type_of_package).' package is successfully canceled.';
            $user = $user_package->user;
            $type = 'Package';
            $user_type = 'buyer';
            $module = 'profile';
            pushNotification($title,$body,$user,$type,true,$user_type,$module,'no-data','package');
            Log::channel('webhook')->info('Subscription canceled. User Package Subscription Id: '. $subscribedPackage->id);
        }
    }

    private function abortedSubscription($subscription_id) 
    {
        $subscribedPackage = UserPackageSubscription::where('subscription_id', $subscription_id)->orderBy('auto_id', 'DESC')->first();
        if($subscribedPackage)
        {
            $user_package->is_canceled = 1;
            $user_package->canceled_date = date('Y-m-d');
            $user_package->save();

            $title = 'Package Subscription Canceled';
            $body =  'Your '.$subscribedPackage->package->module.' module '.getLangByLabelGroups('packages', $subscribedPackage->package->type_of_package).' package is successfully canceled.';
            $user = $user_package->user;
            $type = 'Package';
            $user_type = 'buyer';
            $module = 'profile';
            pushNotification($title,$body,$user,$type,true,$user_type,$module,'no-data','package');
            Log::channel('webhook')->info('Subscription canceled. User Package Subscription Id: '. $subscribedPackage->id);
        }
    }
}
