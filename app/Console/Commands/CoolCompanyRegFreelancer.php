<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentDetail;
use App\Models\OrderItem;
use App\Models\AppSetting;
use Log;
use \mervick\aesEverywhere\AES256;

class CoolCompanyRegFreelancer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:freelancer';

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
        $access_token = null;
        $tokenExpired = time();
        
        $getStudentList = StudentDetail::select('users.id','users.first_name','users.last_name','users.email','users.qr_code_number','users.social_security_number','users.bank_account_num','users.bank_identifier_code','users.bank_name','users.bank_account_type')
            ->where('student_details.cool_company_id', null)
            ->where('users.user_type_id', '2')
            ->where('users.status', '1')
            ->where('users.is_email_verified', 1)
            ->where('users.is_contact_number_verified', 1)
            ->join('users', 'users.id','=','student_details.user_id')
            ->get();
        foreach($getStudentList as $student)
        {
            if(empty($access_token) || time() > $tokenExpired)
            {
                $getToken = $this->getAccessToken();
                $access_token   = $getToken['access_token'];
                $tokenExpired   = $getToken['expire_time'];
            }

            if($student->bank_account_type==1) {
                $paymentAccountTypeId = 'Local';
            } elseif($student->bank_account_type==2) {
                $paymentAccountTypeId = 'International';
            } else {
                $paymentAccountTypeId = 'PayPal';
            }
            $data = [
                'firstName'   => $student->first_name,
                'lastName'    => $student->last_name,
                'email'       => $student->email,
                'externalId'  => $student->qr_code_number,
                'workerTypeId'=> 0,
                'icInfo'      => [      
                    'socialNo'              => $student->social_security_number,
                    'paymentAccountTypeId'  => $paymentAccountTypeId,
                    'bankAccountNo'         => $student->bank_account_num,
                    'bankIdentifierCode'    => $student->bank_identifier_code,
                    'bankName'              => $student->bank_name
                  ]
              ];
            $createdFreelancerInfo = $this->createFreelancer($access_token, $data);
            if(!empty($createdFreelancerInfo))
            {
                //Update Record
                $resDecode = json_decode($createdFreelancerInfo, true);
                $studentInfo = StudentDetail::select('id','cool_company_id')->where('user_id', $student->id)->first();
                $studentInfo->cool_company_id = $resDecode['id'];
                $studentInfo->save();
                Log::channel('customlog')->info($student->id.' :Student successfully registered. cool company id: '.$studentInfo->cool_company_id);
                }
            else
            {
                //Lock Error
                Log::channel('customlog')->info($student->id.' :Student registration failed. Please Check');
                }
            }
        }
    }

    private function getAccessToken()
    {
        $url = env('COOL_URL_TOKEN', 'https://stage-ip.coolcompany.com').'/connect/token';
        $clientId = env('COOL_CLIENTID','84a12806-d08b-48ac-8cfb-74aa440d40ef');
        $clientSecret = env('COOL_CLIENTSECRET','9g5f4d2a4a443c5db5ba1f537d2f672fa19f6ab8bf1d73dce92ebc62175bcce1');

        $auth = base64_encode($clientId.":".$clientSecret);
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
          CURLOPT_POSTFIELDS => 'grant_type=client_credentials&scope=BusinessDashboardWebUIApi+BusinessDashboardExternalApi',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Basic '.$auth,
            'Content-Type: application/x-www-form-urlencoded',
          ),
        ));

        $response = curl_exec($curl);
        if ($response === false) {
            Log::channel('customlog')->error('Getting error while generate access token.');
            Log::channel('customlog')->error(curl_error($curl));
            die;
        }
        $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if($response_code==200)
        {
            $getToken = json_decode($response, true);
            $returnData = [
                'access_token'  => $getToken['access_token'],
                'expire_time'   => strtotime($getToken['expires_in'].' sec', time())
            ];
            return $returnData;
        }
        return false;
    }

    private function createFreelancer($accessToken, $data)
    {
        $url = env('COOL_URL_FUNCTION', 'https://stage-open-api.coolcompany.com').'/api/v1/Teammembers';
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
            'Accept-Language: en',
            'Accept: application/json',
            'Authorization: Bearer '.$accessToken,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        if ($response === false) {
            Log::channel('customlog')->error('Getting error while create a freelancer.');
            $error = ["curl_error_".curl_errno($curl) => curl_error($curl)];
            Log::channel('customlog')->error($error);
            die;
        }
        $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if($response_code==200 || $response_code==201)
        {
            return $response;
        }
        return false;
    }
}
