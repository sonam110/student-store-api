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

        if ($event->type == "subscription_schedule.aborted")
        {
            $subscriptionSchedule = $event->data->object;
            //$this->completeOrderInDatabase()
            //$this->sendMail();
            Log::channel('webhook')->info('success');
            Log::channel('webhook')->info($subscriptionSchedule);
        } 
        elseif ($event->type == "subscription_schedule.canceled")
        {            
            $subscriptionSchedule = $event->data->object;
            Log::channel('webhook')->info('canceled');
            Log::channel('webhook')->info($subscriptionSchedule);
        }
        elseif ($event->type == "subscription_schedule.completed")
        {            
            $subscriptionSchedule = $event->data->object;
            Log::channel('webhook')->info('completed');
            Log::channel('webhook')->info($subscriptionSchedule);
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
}
