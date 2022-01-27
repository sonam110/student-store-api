<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentGatewaySetting;
use App\Models\Order;

class CheckSwishPaymentStatus extends Command
{
    protected $signature = 'checkswish:paymentstatus';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $getOrders = Order::where('payment_status', 'processing')
            ->whereNotNull('swish_payment_token')->get();
        if($getOrders->count()>0)
        {
            $paymentInfo = PaymentGatewaySetting::select('swish_access_token')->first();
            $accessToken = $paymentInfo->swish_access_token;
            $transactionUrl = env('SWISH_URL').'/psp/swish/payments/'.$transaction_token.'/sales';
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer '.$accessToken,
            ];

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curl, CURLOPT_URL, $transactionUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_FAILONERROR, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $rawResponse = curl_exec($curl);
            $response = json_decode($rawResponse);
            if(curl_errno($curl)>0)
            {
                $info = curl_errno($curl)>0 ? array("curl_error_".curl_errno($curl)=>curl_error($curl)) : curl_getinfo($curl);
                return response()->json(prepareResult(true, $info, "Error while getting swish transaction information."), config('http_response.internal_server_error'));
            }
            curl_close($curl);
            $returnData = [
                'created'   => time(),
                'swish_response' => $response
            ];
        }
    }
}
