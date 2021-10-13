<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentGatewaySetting;

class KlarnaPaymentController extends Controller
{
    function __construct()
    {
        $this->paymentInfo = PaymentGatewaySetting::first();
    }

    public function createKlarnaSession(Request $request)
    {
        $url = env('KLARNA_URL').'/payments/v1/sessions';
        $username = $this->paymentInfo->klarna_username;
        $password = $this->paymentInfo->klarna_password;
        $auth     = base64_encode($username.":".$password);

        $data = [
            'purchase_country'  => 'SE',
            'purchase_currency' => 'SEK',
            'locale'            => 'sv-SE',
            'order_amount'      => 10,
            'order_tax_amount'  => 10,
            'order_lines'       => 10,
            'order_amount'      => [
                'type'      => 'physical',
                'reference' => '19-402',
                'name'      => 'Battery Power Pack',
                'quantity'  => 1,
                'unit_price'=> 10,
                'tax_rate'  => 0,
                'total_amount'          => 10,
                'total_discount_amount' => 0,
                'total_tax_amount'      => 0,
                'image_url' => 'https://www.exampleobjects.com/logo.png',
                'product_url' => 'https://www.estore.com/products/f2a8d7e34'
            ]
        ];
        $postData = json_encode($data);

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $postData,
          CURLOPT_HTTPHEADER => array(
            'Authorization: Basic '.$auth,
            'Content-Type: application/json',
          ),
        ));

        $response = curl_exec($curl);
        if(curl_errno($curl)>0)
        {
            $info = curl_errno($curl)>0 ? array("curl_error_".curl_errno($curl)=>curl_error($curl)) : curl_getinfo($curl);
            return response()->json(prepareResult(true, $info, "Error while creating klarna session"), config('http_response.internal_server_error'));
        }
        curl_close($curl);
        return response()->json(prepareResult(false, $response, "Session successfully created."), config('http_response.success'));
    }
}
