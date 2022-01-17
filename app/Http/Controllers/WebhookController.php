<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPackageSubscription;
use App\Models\PaymentGatewaySetting;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TransactionDetail;
use App\Models\AddressDetail;
use App\Mail\OrderMail;
use App\Mail\OrderPlacedMail;
use mervick\aesEverywhere\AES256;
use App\Models\EmailTemplate;
use Mail;
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
            http_response_code(400);
            exit();
        }
        catch(\Stripe\Exception\SignatureVerificationException $e)
        {
            Log::channel('webhook')->info('Invalid signature');
            Log::channel('webhook')->info($e);
            http_response_code(400);
            exit();
        }

        /*if ($event->type == "subscription_schedule.completed")
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
        elseif ($event->type == "customer.subscription.created")
        { 
            
            $subscriptionSchedule = $event->data->object;
            Log::channel('webhook')->info('customer.subscription.created');
            Log::channel('webhook')->info($subscriptionSchedule);
        }*/
        Log::channel('webhook')->info($event->type);
        if ($event->type == "customer.subscription.created")
        { 
            $subscriptionSchedule = $event->data->object;
            $subscription_id = $subscriptionSchedule->id;
            $this->customerSubscriptionCreated($subscription_id, $subscriptionSchedule);
            Log::channel('webhook')->info($subscriptionSchedule);
        }
        elseif ($event->type == "customer.subscription.updated")
        { 
            $subscriptionSchedule = $event->data->object;
            $subscription_id = $subscriptionSchedule->subscription;
            //$this->customerSubscriptionUpdated($subscription_id, $subscriptionSchedule);*/
            Log::channel('webhook')->info($subscriptionSchedule);
        }
        elseif ($event->type == "customer.subscription.deleted" || $event->type == "subscription_schedule.aborted" || $event->type == "subscription_schedule.canceled") {
            $subscriptionSchedule = $event->data->object;
            $subscription_id = $subscriptionSchedule->id;
            $this->abortedSubscription($subscription_id);
            Log::channel('webhook')->info($subscriptionSchedule);
        }
        elseif ($event->type == "invoice.payment_succeeded") {
            $subscriptionSchedule = $event->data->object;
            $invoice_id = $subscriptionSchedule->id;
            $status = $subscriptionSchedule->status;
            $finalized_date = @$subscriptionSchedule->status_transitions->finalized_at;
            $this->paymentStatus($invoice_id, $status, $finalized_date);
            Log::channel('webhook')->info($subscriptionSchedule);
        }
        elseif ($event->type == "invoice.payment_failed") {
            $subscriptionSchedule = $event->data->object;
            $invoice_id = $subscriptionSchedule->id;
            $status = $subscriptionSchedule->status;
            $finalized_date = @$subscriptionSchedule->status_transitions->finalized_at;
            $this->paymentStatus($invoice_id, $status, $finalized_date);
            Log::channel('webhook')->info($subscriptionSchedule);
        }
        /*elseif ($event->type == "invoice.created") {
            $subscriptionSchedule = $event->data->object;
            $subscription_id = $subscriptionSchedule->id;
            Log::channel('webhook')->info($subscriptionSchedule);
        }
        elseif ($event->type == "invoice.finalized") {
            $subscriptionSchedule = $event->data->object;
            $subscription_id = $subscriptionSchedule->id;
            Log::channel('webhook')->info($subscriptionSchedule);
        }*/

        //Log::channel('webhook')->info('payload');
        //Log::channel('webhook')->info($payload);
        
        http_response_code(200);
    }

    private function customerSubscriptionCreated($subscription_id, $subscriptionSchedule) 
    {
        Log::channel('webhook')->info('subscription_id');
        Log::channel('webhook')->info($subscription_id);
        $subscribedPackage = UserPackageSubscription::where('subscription_status', 1)
            ->where('subscription_id', $subscription_id)
            ->orderBy('auto_id', 'DESC')
            ->first();
        if($subscribedPackage)
        {
            $findUser = User::where('stripe_customer_id', $subscriptionSchedule->customer)->first();
            Log::channel('webhook')->info('User Info');
            Log::channel('webhook')->info($findUser);
            if($findUser)
            {
                $subscribedPackage->subscription_status = 0;
                $subscribedPackage->save();

                $delivery_code = NULL;
                $shipping_charge = 0;

                $getLastOrder = Order::select('order_number')->orderBy('created_at','DESC')->first();
                if(!empty($getLastOrder))
                {
                    $order_number = $getLastOrder->order_number + 1;

                }
                else
                {
                    $order_number = env('ORDER_START_NUMBER');
                }

                $userInfo = $subscribedPackage->user;
                $addressfind = $subscribedPackage->user->defaultAddress;
                $order = new Order;
                if($addressfind)
                {
                    $order->address_detail_id   = $addressfind->id;
                    $order->latitude            = $addressfind->latitude;
                    $order->longitude           = $addressfind->longitude;
                    $order->country             = $addressfind->country;
                    $order->state               = $addressfind->state;
                    $order->city                = $addressfind->city;
                    $order->full_address        = $addressfind->full_address;
                    $order->zip_code            = $addressfind->zip_code;
                }
                $pakcageAmount = ($subscriptionSchedule->items->data[0]->plan->amount)/100;
                
                $order->order_number        = $order_number;
                $order->user_id             = $userInfo->id;
                
                $order->order_status        = 'completed';
                $order->sub_total           = $pakcageAmount;
                $order->item_discount       = 0;
                $order->shipping_charge     = 0;
                $order->total               = $pakcageAmount;
                $order->promo_code          = null;
                $order->promo_code_discount = null;
                $order->grand_total         = $pakcageAmount;
                $order->payable_amount      = $pakcageAmount;
                $order->remark              = $subscriptionSchedule->collection_method;
                $order->first_name          = (!empty($userInfo->first_name)) ? AES256::decrypt($userInfo->first_name, env('ENCRYPTION_KEY')) : NULL;
                $order->last_name           = (!empty($userInfo->last_name)) ? AES256::decrypt($userInfo->last_name, env('ENCRYPTION_KEY')) : NULL;
                $order->email               = (!empty($userInfo->email)) ? AES256::decrypt($userInfo->email, env('ENCRYPTION_KEY')) : NULL;
                $order->contact_number      = (!empty($userInfo->contact_number)) ? AES256::decrypt($userInfo->contact_number, env('ENCRYPTION_KEY')) : NULL;
                
                $order->used_reward_points  = 0;
                $order->order_for           = 'packages';
                $order->reward_point_status = 'used';
                $order->save();

                if($order)
                {
                    $vat_percent = '0';
                    $title = $subscribedPackage->type_of_package;
                    $commission = $subscribedPackage->commission_per_sale;

                    $orderItem = new OrderItem;
                    $orderItem->user_id = $userInfo->id;
                    $orderItem->order_id = $order->id;
                    $orderItem->vendor_user_id = null;
                    $orderItem->package_id = $subscribedPackage->package_id;
                    $orderItem->title = $title;
                    $orderItem->price = $pakcageAmount;
                    $orderItem->earned_reward_points = 0;
                    $orderItem->quantity = 1;
                    $orderItem->discount = 0;
                    $orderItem->item_status = 'completed';
                    $orderItem->item_payment_status = true;
                    $orderItem->amount_transferred_to_vendor = 0;
                    $orderItem->student_store_commission = $pakcageAmount;
                    $orderItem->cool_company_commission = 0;
                    $orderItem->student_store_commission_percent = 0;
                    $orderItem->cool_company_commission_percent = 0;
                    $orderItem->vat_percent = 0;
                    $orderItem->delivery_code = null;
                    $orderItem->save();

                    // create UserPackageSubscription

                    \Log::info('trigger webhook and added new userPackageSubscription. please recheck');
                    \Log::info($subscribedPackage);
                    $userPackageSubscription = new UserPackageSubscription;
                    $userPackageSubscription->user_id = $userInfo->id;
                    $userPackageSubscription->subscription_id = $subscribedPackage->subscription_id;
                    $userPackageSubscription->payby                 = $subscribedPackage->payby;
                    $userPackageSubscription->package_id            = $subscribedPackage->package_id;
                    $userPackageSubscription->package_valid_till    = date("Y-m-d H:i:s", $subscriptionSchedule->current_period_end);
                    $userPackageSubscription->subscription_status   = 1;
                    $userPackageSubscription->module                = $subscribedPackage->module;
                    $userPackageSubscription->type_of_package       = $subscribedPackage->type_of_package;
                    $userPackageSubscription->job_ads               = $subscribedPackage->job_ads;
                    $userPackageSubscription->publications_day      = $subscribedPackage->publications_day;
                    $userPackageSubscription->duration              = $subscribedPackage->duration;
                    $userPackageSubscription->cvs_view              = $subscribedPackage->cvs_view;
                    $userPackageSubscription->employees_per_job_ad  = $subscribedPackage->employees_per_job_ad;
                    $userPackageSubscription->no_of_boost           = $subscribedPackage->no_of_boost;
                    $userPackageSubscription->boost_no_of_days      = $subscribedPackage->boost_no_of_days;
                    $userPackageSubscription->most_popular          = $subscribedPackage->most_popular;
                    $userPackageSubscription->most_popular_no_of_days= $subscribedPackage->most_popular_no_of_days;
                    $userPackageSubscription->top_selling           = $subscribedPackage->top_selling;
                    $userPackageSubscription->top_selling_no_of_days= $subscribedPackage->top_selling_no_of_days;
                    $userPackageSubscription->price                 = $subscribedPackage->price;
                    $userPackageSubscription->start_up_fee          = $subscribedPackage->start_up_fee;
                    $userPackageSubscription->subscription          = $subscribedPackage->subscription;
                    $userPackageSubscription->commission_per_sale   = $subscribedPackage->commission_per_sale;
                    $userPackageSubscription->number_of_product     = $subscribedPackage->number_of_product;
                    $userPackageSubscription->number_of_service     = $subscribedPackage->number_of_service;
                    $userPackageSubscription->number_of_book        = $subscribedPackage->number_of_book;
                    $userPackageSubscription->number_of_contest     = $subscribedPackage->number_of_contest;
                    $userPackageSubscription->number_of_event       = $subscribedPackage->number_of_event;
                    $userPackageSubscription->notice_month          = $subscribedPackage->notice_month;
                    $userPackageSubscription->locations             = $subscribedPackage->locations;
                    $userPackageSubscription->organization          = $subscribedPackage->organization;
                    $userPackageSubscription->attendees             = $subscribedPackage->attendees;
                    $userPackageSubscription->range_of_age          = $subscribedPackage->range_of_age;
                    $userPackageSubscription->cost_for_each_attendee= $subscribedPackage->cost_for_each_attendee;
                    $userPackageSubscription->top_up_fee            = $subscribedPackage->top_up_fee;
                    $userPackageSubscription->is_recurring_transactionis_recurring_transaction = 1;
                    $userPackageSubscription->stripe_invoice_id = $subscriptionSchedule->latest_invoice;
                    $userPackageSubscription->save();

                    //Transaction create
                    $getTransactionDetail = TransactionDetail::where('user_package_subscription_id', $subscribedPackage->id)->first();
                    if($getTransactionDetail)
                    {
                        $transactionDetail = new TransactionDetail;
                        $transactionDetail->order_id = $order->id;
                        $transactionDetail->payment_card_detail_id = $getTransactionDetail->payment_card_detail_id;
                        $transactionDetail->card_number = $getTransactionDetail->card_number;
                        $transactionDetail->card_type = $getTransactionDetail->card_type;
                        $transactionDetail->card_cvv = $getTransactionDetail->card_cvv;
                        $transactionDetail->card_expiry = $getTransactionDetail->card_expiry;
                        $transactionDetail->card_holder_name = $getTransactionDetail->card_holder_name;
                    
                        $transactionDetail->transaction_id = $subscriptionSchedule->id; //this is event id

                        $transactionDetail->description = $getTransactionDetail->description;
                        $transactionDetail->receipt_email = $getTransactionDetail->receipt_email;
                        $transactionDetail->receipt_number = $getTransactionDetail->receipt_number;
                        $transactionDetail->receipt_url = null;
                        $transactionDetail->refund_url = null;

                        $transactionDetail->transaction_status = 'Succeeded';
                        $transactionDetail->transaction_reference_no = null;
                        $transactionDetail->transaction_amount = $pakcageAmount;
                        $transactionDetail->transaction_type = 'paid';
                        $transactionDetail->transaction_mode = 'Online';
                        $transactionDetail->gateway_detail = 'stripe';

                        $transactionDetail->transaction_timestamp = $subscriptionSchedule->created;
                        $transactionDetail->currency = $getTransactionDetail->currency;
                        $transactionDetail->save();
                    }

                    
                    $title = 'Package Subscription renew';
                    $body =  'Your '.$subscribedPackage->package->module.' module '.getLangByLabelGroups('packages', $subscribedPackage->package->type_of_package).' package is successfully renewed.';
                    $user = $subscribedPackage->user;
                    $type = 'Package';
                    $user_type = 'buyer';
                    $module = 'profile';
                    pushNotification($title,$body,$user,$type,true,$user_type,$module,'no-data','package');

                    //Email
                    $emailTemplate = EmailTemplate::where('template_for','order_placed')->where('language_id', $userInfo->language_id)->first();
                    if(empty($emailTemplate))
                    {
                        $emailTemplate = EmailTemplate::where('template_for','order_placed')->first();
                    }

                    $email_body = $emailTemplate->body;

                    $arrayVal = [
                        '{{user_name}}' => AES256::decrypt($order->user->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($order->user->last_name, env('ENCRYPTION_KEY')),
                        '{{order_number}}' => $order->order_number,
                    ];
                    $email_body = strReplaceAssoc($arrayVal, $email_body);
                    
                    $details = [
                        'title' => $emailTemplate->subject,
                        'body' => $email_body,
                        'order_details' => $order,
                    ];
                    
                    Mail::to(AES256::decrypt($order->email, env('ENCRYPTION_KEY')))->send(new OrderPlacedMail($details));
                    //mail-end
                    return 1;
                }
            }
        }
    }

    private function abortedSubscription($subscription_id) 
    {
        $subscribedPackage = UserPackageSubscription::where('subscription_id', $subscription_id)->first();
        if($subscribedPackage)
        {
            $subscribedPackage->is_canceled = 1;
            $subscribedPackage->canceled_date = date('Y-m-d');
            $subscribedPackage->save();

            //if no active package the assign free package with the same module
            $checkIsPackageExist = UserPackageSubscription::where('module', $subscribedPackage->module)->where('subscription_status', 1)->orderBy('auto_id', 'DESC')->first();
            if(!$checkIsPackageExist)
            {
                $userType = $subscribedPackage->user->user_type_id;
                $module = $subscribedPackage->module;
                $userId = $subscribedPackage->user_id;
                createFreePackage($userType, $module, $userId);
            }

            $title = 'Package Subscription Canceled';
            $body =  'Your '.$subscribedPackage->package->module.' module '.getLangByLabelGroups('packages', $subscribedPackage->package->type_of_package).' package is successfully canceled.';
            $user = $subscribedPackage->user;
            $type = 'Package';
            $user_type = 'buyer';
            $module = 'profile';
            pushNotification($title,$body,$user,$type,true,$user_type,$module,'no-data','package');
            Log::channel('webhook')->info('Subscription canceled. User Package Subscription Id: '. $subscribedPackage->id);

        }
    }

    private function paymentStatus($invoice_id, $status, $finalized_date) 
    {
        $subscribedPackage = UserPackageSubscription::where('stripe_invoice_id', $invoice_id)->first();
        if($subscribedPackage)
        {
            $subscribedPackage->stripe_invoice_status = $status;
            $subscribedPackage->stripe_subscription_status = $status;
            $subscribedPackage->stripe_invoice_finalized_date = $finalized_date;
            $subscribedPackage->save();
            if($status)
            {

            }
        }

    }
}
