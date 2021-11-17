<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPackageSubscription;
use App\Models\PaymentGatewaySetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TransactionDetail;
use App\Models\AddressDetail;
use App\Mail\OrderMail;
use App\Mail\OrderConfirmedMail;
use mervick\aesEverywhere\AES256;
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

        /*if ($event->type == "subscription_schedule.aborted" || $event->type == "subscription_schedule.canceled")
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
        elseif ($event->type == "customer.subscription.created")
        { 
            
            $subscriptionSchedule = $event->data->object;
            Log::channel('webhook')->info('customer.subscription.created');
            Log::channel('webhook')->info($subscriptionSchedule);
        }*/
        
        if ($event->type == "customer.subscription.updated")
        { 
            $subscriptionSchedule = $event->data->object;
            $subscription_id = $subscriptionSchedule->subscription;
            $this->customerSubscriptionUpdated($subscription_id, $subscriptionSchedule);
            Log::channel('webhook')->info('customer.subscription.created');
            Log::channel('webhook')->info($subscriptionSchedule);
        }
        elseif ($event->type == "customer.subscription.deleted") {
            $subscriptionSchedule = $event->data->object;
            $subscription_id = $subscriptionSchedule->subscription;
            $this->abortedSubscription($subscription_id);
            Log::channel('webhook')->info('customer.subscription.created');
            Log::channel('webhook')->info($subscriptionSchedule);
        }
        Log::channel('webhook')->info('payload');
        Log::channel('webhook')->info($payload);
        http_response_code(200);
    }

    private function customerSubscriptionCreated($subscription_id) 
    {
        $subscribedPackage = UserPackageSubscription::where('subscription_id', $subscription_id)->orderBy('auto_id', 'DESC')->first();
        if($subscribedPackage)
        {
            // $subscribedPackage->is_canceled = 1;
            // $subscribedPackage->canceled_date = date('Y-m-d');
            // $subscribedPackage->save();

            // $title = 'Package Subscription Canceled';
            // $body =  'Your '.$subscribedPackage->package->module.' module '.getLangByLabelGroups('packages', $subscribedPackage->package->type_of_package).' package is successfully canceled.';
            // $user = $subscribedPackage->user;
            // $type = 'Package';
            // $user_type = 'buyer';
            // $module = 'profile';
            // pushNotification($title,$body,$user,$type,true,$user_type,$module,'no-data','package');
            // Log::channel('webhook')->info('Subscription canceled. User Package Subscription Id: '. $subscribedPackage->id);
        }
    }

    private function customerSubscriptionUpdated($subscription_id, $subscriptionSchedule) 
    {
        $subscribedPackage = UserPackageSubscription::where('subscription_id', $subscription_id)->orderBy('auto_id', 'DESC')->first();
        if($subscribedPackage)
        {
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
            if($addressfind)
            {
                $order->latitude            = $addressfind->latitude;
                $order->longitude           = $addressfind->longitude;
                $order->country             = $addressfind->country;
                $order->state               = $addressfind->state;
                $order->city                = $addressfind->city;
                $order->full_address        = $addressfind->full_address;
                $order->zip_code            = $addressfind->zip_code;
            }
            $order                      = new Order;
            $order->order_number        = $order_number;
            $order->user_id             = Auth::id();
            $order->address_detail_id   = $request->address_detail_id;
            $order->order_status        = $request->order_status;
            $order->sub_total           = $request->sub_total;
            $order->item_discount       = $request->item_discount;
            $order->shipping_charge     = $shipping_charge;
            $order->total               = $request->total;
            $order->promo_code          = $request->promo_code;
            $order->promo_code_discount = $request->promo_code_discount;
            $order->grand_total         = $request->grand_total;
            $order->remark              = $request->remark;
            $order->first_name          = (!empty(Auth::user()->first_name)) ? AES256::decrypt(Auth::user()->first_name, env('ENCRYPTION_KEY')) : NULL;
            $order->last_name           = (!empty(Auth::user()->last_name)) ? AES256::decrypt(Auth::user()->last_name, env('ENCRYPTION_KEY')) : NULL;
            $order->email               = (!empty(Auth::user()->email)) ? AES256::decrypt(Auth::user()->email, env('ENCRYPTION_KEY')) : NULL;
            $order->contact_number      = (!empty(Auth::user()->contact_number)) ? AES256::decrypt(Auth::user()->contact_number, env('ENCRYPTION_KEY')) : NULL;
            
            $order->used_reward_points  = $request->used_reward_points;
            $order->order_for           = $request->order_for;
            $order->reward_point_status = 'used';
            $order->save();

            if($order)
            {
                $productsServicesBook = Package::find($orderedItem['package_id']);
                $vat_percent = '0';
                if($productsServicesBook->price == 0)
                {
                    $price = $productsServicesBook->subscription;
                    $vendor_price = $price;
                }
                else
                {
                    $price = $productsServicesBook->price;
                    $vendor_price = $price;
                }
                $title = $productsServicesBook->type_of_package;
                $user_package = UserPackageSubscription::where('user_id',null)->first();

                if($user_package)
                {
                    $commission = $user_package->commission_per_sale;
                }
                else
                {
                    $commission = 0;
                }

                $orderItem = new OrderItem;
                $orderItem->user_id                         = Auth::id();
                $orderItem->order_id                        = $order->id;
                $orderItem->package_id                      = $orderedItem['package_id'];
                $orderItem->title   = $title;
                    $orderItem->sku     = $productsServicesBook->sku;
                $orderItem->price                           = $price;
                $orderItem->earned_reward_points            = $earned_reward_points;
                $orderItem->quantity                        = $orderedItem['quantity'];
                $orderItem->discount                        = $discount;
                $orderItem->cover_image                     = $orderedItem['cover_image'];
                $orderItem->sell_type                       = $productsServicesBook->sell_type;
                $orderItem->rent_duration                   = $productsServicesBook->rent_duration;
                $orderItem->item_status                     = $request->order_status;
                $orderItem->item_payment_status             = true;
                $orderItem->amount_transferred_to_vendor    = $amount_transferred_to_vendor;
                $orderItem->student_store_commission        = $student_store_commission;
                $orderItem->cool_company_commission         = $cool_company_commission;
                $orderItem->student_store_commission_percent= $commission;
                $orderItem->cool_company_commission_percent = $coolCompanyCommission;
                $orderItem->vat_percent                     = $vat_percent;
                $orderItem->delivery_code                   = $delivery_code;
                $orderItem->save();

                $emailTemplate = EmailTemplate::where('template_for','order_placed')->where('language_id',$order->user->language_id)->first();
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
                    // 'order_details' => Order::with('orderItems')->find($order->id),
                    'order_details' => $order,
                ];
                
                Mail::to(AES256::decrypt($order->email, env('ENCRYPTION_KEY')))->send(new OrderPlacedMail($details));
                //mail-end


                $paymentCardDetail = false;
                if(!empty($request->transaction_detail['payment_card_detail_id']))
                {
                    $paymentCardDetail = PaymentCardDetail::find($request->transaction_detail['payment_card_detail_id']);
                }
                
                $transactionDetail = new TransactionDetail;
                $transactionDetail->order_id                    = $order->id;

                if($paymentCardDetail)
                {
                    $transactionDetail->payment_card_detail_id      = $request->transaction_detail['payment_card_detail_id'];
                    $transactionDetail->card_number                 = $paymentCardDetail->card_number;
                    $transactionDetail->card_type                   = $paymentCardDetail->card_type;
                    $transactionDetail->card_cvv                    = $paymentCardDetail->card_cvv;
                    $transactionDetail->card_expiry                 = $paymentCardDetail->card_expiry;
                    $transactionDetail->card_holder_name            = $paymentCardDetail->card_holder_name;
                }

                
                $transactionDetail->transaction_id              = $request->transaction_detail['transaction_id'];

                $transactionDetail->description                 = $request->transaction_detail['description'];
                $transactionDetail->receipt_email               = $request->transaction_detail['receipt_email'];
                $transactionDetail->receipt_number              = $request->transaction_detail['receipt_number'];
                $transactionDetail->receipt_url                 = $request->transaction_detail['receipt_url'];
                $transactionDetail->refund_url                  = $request->transaction_detail['refund_url'];

                $transactionDetail->transaction_status          = $request->transaction_detail['transaction_status'];
                $transactionDetail->transaction_reference_no    = $request->transaction_detail['transaction_reference_no'];
                $transactionDetail->transaction_amount          = $request->transaction_detail['transaction_amount'];
                $transactionDetail->transaction_type            = $request->transaction_detail['transaction_type'];
                $transactionDetail->transaction_mode            = $request->transaction_detail['transaction_mode'];
                $transactionDetail->gateway_detail              = $request->transaction_detail['gateway_detail'];

                $transactionDetail->transaction_timestamp       = $request->transaction_detail['transaction_timestamp'];
                $transactionDetail->currency                    = $request->transaction_detail['currency'];
                $transactionDetail->save();



                $reward_point_value = AppSetting::first()->customer_rewards_pt_value * $request->used_reward_points;

                $total = $sub_total - $reward_point_value;


                $vat = (AppSetting::first()->vat) * $total / 100;

                // $total = $total + $vat + $shipping_charge;

                $total = $total + $shipping_charge;

                $order->update([
                    'sub_total' => $sub_total,
                    'total'  => $total,
                    'shipping_charge'  => $shipping_charge,
                    'vat' => $vat,
                    'grand_total' => $total -  $request->promo_code_discount,
                    'payable_amount' => $total -  $request->promo_code_discount,
                ]);
            }
        }
    }

    private function abortedSubscription($subscription_id) 
    {
        $subscribedPackage = UserPackageSubscription::where('subscription_id', $subscription_id)->orderBy('auto_id', 'DESC')->first();
        if($subscribedPackage)
        {
            $subscribedPackage->is_canceled = 1;
            $subscribedPackage->canceled_date = date('Y-m-d');
            $subscribedPackage->save();

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
}
