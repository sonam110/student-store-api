<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentDetail;
use App\Models\OrderItem;
use App\Models\AppSetting;
use App\Models\CoolCompanyAssignment;
use App\Models\CoolCompanyFreelancer;
use Log;
use \mervick\aesEverywhere\AES256;

class CoolCompanyCreateAssignment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:assignment';

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

        $today          = new \DateTime();
        $before15Days   = $today->sub(new \DateInterval('P15D'))->format('Y-m-d');

        $vatRateId = AppSetting::select('coolCompanyVatRateId')->first()->coolCompanyVatRateId;

        $getUserIds = OrderItem::select('products_services_books.user_id')
            ->where('order_items.is_returned', '0')
            ->where('order_items.is_replaced', '0')
            ->where('order_items.is_disputed', '0')
            ->where('order_items.product_type', 'service')
            ->where('order_items.is_sent_to_cool_company', '!=', '1')
            ->whereDate('order_items.delivery_completed_date', $before15Days)
            ->where('order_items.item_status', 'completed')
            ->orderBy('order_items.products_services_book_id', 'ASC')
            ->join('products_services_books', 'products_services_books.id','=','order_items.products_services_book_id')
            ->groupBy('products_services_books.user_id')
            ->get();
        foreach($getUserIds as $user)
        {
            $reportArray = [];
            $orderItemId = [];
            $createBatchUserWise = OrderItem::select('products_services_books.user_id','order_items.id','order_items.order_id','order_items.products_services_book_id','order_items.price','order_items.quantity','student_details.cool_company_id')
            ->where('order_items.is_returned', '0')
            ->where('order_items.is_replaced', '0')
            ->where('order_items.is_disputed', '0')
            ->where('order_items.product_type', 'service')
            ->where('order_items.is_sent_to_cool_company', '!=', '1')
            ->whereDate('order_items.delivery_completed_date', $before15Days)
            ->where('order_items.item_status', 'completed')
            ->join('products_services_books', 'products_services_books.id','=','order_items.products_services_book_id')
            ->where('products_services_books.user_id', $user->user_id)
            ->join('student_details', 'student_details.user_id','=','products_services_books.user_id')
            ->where('student_details.cool_company_id', '!=', null)
            ->get();
            foreach ($createBatchUserWise as $key => $batchInfo) {
                $teamMemberId = $batchInfo->cool_company_id;
                $orderItemId[] = $batchInfo->id;
                $reportArray[] = [
                    'dateFrom'    => $before15Days.'T00:00:01Z',
                    'dateTo'      => $before15Days.'T23:59:59Z',
                    'paymentType' => 2,
                    'customUnitType'    => 'days',
                    'unitQuantity'=> $batchInfo->quantity,
                    'unitRate'    => $batchInfo->price,
                    'totalHours'  => 24

                ];
            }
            if(sizeof($reportArray)>0)
            {
                if(empty($access_token) || time() > $tokenExpired)
                {
                    $getToken = $this->getAccessToken();
                    $access_token   = $getToken['access_token'];
                    $tokenExpired   = $getToken['expire_time'];
                }

                $data = [
                    'name'          => $user->user_id.' :Assignment, Date:'.date('Y-m-d'),
                    'workTypeId'    => 2,
                    'teamMembers'   => [
                      [
                      'teamMemberId'  =>  $teamMemberId,
                        'unitCurrencyId'  => 'SEK',
                        'vatRateId'       => $vatRateId,
                        'reports'         => $reportArray
                      ]
                    ]
                  ];
                $createdAssignmentInfo = $this->createAssignments($access_token, $data);

                //Create Assignment Record
                $resDecode = json_decode($createdAssignmentInfo, true);
                if(!empty($createdAssignmentInfo)) {
                    $cool_company_freelancer_id = CoolCompanyFreelancer::select('id')->where('cool_company_id', $teamMemberId)->first()->id;
                    $createAssignment = new CoolCompanyAssignment;
                    $createAssignment->user_id = $user->user_id;
                    $createAssignment->cool_company_freelancer_id  = $cool_company_freelancer_id;
                    $createAssignment->assignment_name = $resDecode['name'];
                    $createAssignment->send_object = json_encode($data);
                    $createAssignment->assignmentId = $resDecode['id'];
                    $createAssignment->agreementId = $resDecode['agreementId'];
                    $createAssignment->totalBudget = $resDecode['totalBudget'];
                    $createAssignment->bdaId = $resDecode['bdaId'];
                    $createAssignment->status = $resDecode['status'];
                    $createAssignment->response = $createdAssignmentInfo;
                    $createAssignment->save();

                    if($createAssignment) {
                        //Update Record
                        $updateOrderInfo = OrderItem::select('id','is_sent_to_cool_company','sent_to_cool_company_date')->whereIn('id', $orderItemId)->update([
                            'is_sent_to_cool_company'   => '1',
                            'sent_to_cool_company_date' => date('Y-m-d')
                        ]);
                    }
                    Log::channel('customlog')->info('Assignment Created.');
                } else {
                    Log::channel('customlog')->Error('Assignment Not Created.');
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

    private function createAssignments($accessToken, $data)
    {
        $url = env('COOL_URL_FUNCTION', 'https://stage-open-api.coolcompany.com').'/api/v1/Assignments';
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
            Log::channel('customlog')->error('Getting error while create an assignments.');
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
