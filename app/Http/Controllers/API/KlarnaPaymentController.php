<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentGatewaySetting;
use mervick\aesEverywhere\AES256;
use Auth;
use App\Models\User;

class KlarnaPaymentController extends Controller
{
    function __construct()
    {
        $this->paymentInfo = PaymentGatewaySetting::first();
        $username       = $this->paymentInfo->klarna_username;
        $password       = $this->paymentInfo->klarna_password;
        $this->auth     = base64_encode($username.":".$password);
    }

    public function createKlarnaSession(Request $request)
    {
        $user = User::find(Auth::id());
        $url  = env('KLARNA_URL').'/payments/v1/authorizations/'.$request->auth_token.'/customer-token';

        $given_name = AES256::decrypt($user->first_name, env('ENCRYPTION_KEY'));
        $family_name = (!empty(AES256::decrypt($user->last_name, env('ENCRYPTION_KEY')))) ? AES256::decrypt($user->last_name, env('ENCRYPTION_KEY')) : null;
        $email = AES256::decrypt($user->email, env('ENCRYPTION_KEY'));
        $phone = AES256::decrypt($user->contact_number, env('ENCRYPTION_KEY'));
        $street_address = $user->defaultAddress->full_address;
        $postal_code = $user->defaultAddress->zip_code;
        $city = $user->defaultAddress->city;

        $data = [
            'purchase_country'  => 'SE',
            'locale'            => env('KLARNA_LOCALE', 'sv-SE'),
            'billing_address'   => [
                'given_name'    => $given_name,
                'family_name'   => $family_name,
                'email'         => $email,
                'phone'         => $phone,
                'street_address'=> $street_address,
                'postal_code'   => $postal_code,
                'city'          => $city,
                'country'       => 'SE'
            ],
            'description'       => 'Student Store',
            'intended_use'      => 'subscription',
            'merchant_urls'     => [
                'confirmation'  => 'https://www.example.com/confirmation.html'
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
