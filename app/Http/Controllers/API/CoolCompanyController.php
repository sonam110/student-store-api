<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use App\Models\CoolCompanyAssignment;
use App\Models\CoolCompanyFreelancer;
use Auth;

class CoolCompanyController extends Controller
{
    public function index(Request $request)
    {
        try
        {

            if(!empty($request->per_page_record))
            {
                $assignments = CoolCompanyAssignment::with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.studentDetail:user_id,cool_company_id');
                if(Auth::user()->user_type_id != 1) 
                {
                    $assignmentsList = $assignments->where('user_id', Auth::id());
                }
                $assignmentsList = $assignments->orderBy('created_at','DESC')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $assignments = CoolCompanyAssignment::with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.studentDetail:user_id,cool_company_id');
                if(Auth::user()->user_type_id != 1) 
                {
                    $assignmentsList = $assignments->where('user_id', Auth::id());
                }
                $assignmentsList = $assignments->orderBy('created_at','DESC')->get();
            }
            return response(prepareResult(false, $assignmentsList, getLangByLabelGroups('messages','message__category_master_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function coolCompanyStatistics(Request $request)
    {
        try
        {
            $freelancer = CoolCompanyFreelancer::select('id');
            if(Auth::user()->user_type_id != 1) 
            {
                $totalFreelancer = $freelancer->where('user_id', Auth::id());
            }
            $totalFreelancer = $freelancer->count();


            $assignments = CoolCompanyAssignment::select('id');
            if(Auth::user()->user_type_id != 1) 
            {
                $totalAssignment = $assignments->where('user_id', Auth::id());
            }
            $totalAssignment = $assignments->count();

            $budget = CoolCompanyAssignment::select('id');
            if(Auth::user()->user_type_id != 1) 
            {
                $totalBudget = $budget->where('user_id', Auth::id());
            }
            $totalBudget = $budget->sum('totalBudget');
            $returnObj = [
                'totalFreelancer'   => $totalFreelancer,
                'totalAssignment'   => $totalAssignment,
                'totalBudget'       => $totalBudget
            ];

            return response(prepareResult(false, $returnObj, getLangByLabelGroups('messages','message__category_master_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function getAssignmentInfo($assignmentId)
    {
        $access_token = null;
        $tokenExpired = time();

        if(empty($access_token) || time() > $tokenExpired)
        {
            $getToken = $this->getAccessToken();
            if(!$getToken) {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
            }
            $access_token   = $getToken['access_token'];
            $tokenExpired   = $getToken['expire_time'];
        }
        $response = $this->assignmentInfo($access_token, $assignmentId);
        if(!empty($response))
        {
            return response(prepareResult(false, json_decode($response, true), getLangByLabelGroups('messages','message__category_master_list')), config('http_response.success'));
        }
        return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    }

    public function startAndApproveAssignment(Request $request)
    {
        $access_token = null;
        $tokenExpired = time();

        if(empty($access_token) || time() > $tokenExpired)
        {
            $getToken = $this->getAccessToken();
            if(!$getToken) {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
            }
            $access_token   = $getToken['access_token'];
            $tokenExpired   = $getToken['expire_time'];
        }

        $data = [
            'action'   => 'Start'
        ];

        foreach ($request->cool_company_assignment_ids as $id) {
            $getAssignmentInfo = CoolCompanyAssignment::select('cool_company_freelancer_id','assignmentId','is_start_assignment','start_assignment_date','start_assignment_response')->find($id);
            $startAssignment = $this->startAssignment($access_token, $getAssignmentInfo->assignmentId, $data);
            if(!empty($startAssignment))
            {
                $dataReport = json_decode($startAssignment, true);
                foreach ($dataReport['teamMembers'] as $value) {
                    foreach ($value['reports'] as $report) {
                        $approveAssignment = $this->approveAssignment($access_token, $getAssignmentInfo->assignmentId, $report['id']);
                    }
                }
            
                //update response
                $getAssignmentInfo->is_start_assignment = 1;
                $getAssignmentInfo->start_assignment_date = date('Y-m-d');
                $getAssignmentInfo->start_assignment_response = $dataReport;
                $getAssignmentInfo->save();
            }
        }

        return response(prepareResult(false, [], getLangByLabelGroups('messages','message__category_master_list')), config('http_response.success'));
    }

    public function assignmentCurrentStatus(Request $request)
    {
        $access_token = null;
        $tokenExpired = time();

        if(empty($access_token) || time() > $tokenExpired)
        {
            $getToken = $this->getAccessToken();
            if(!$getToken) {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
            }
            $access_token   = $getToken['access_token'];
            $tokenExpired   = $getToken['expire_time'];
        }
        $assignmentId = $request->assignmentId;
        $timeReportId = $request->timeReportId;
        $response = $this->checkAssignmentStatus($access_token, $assignmentId, $timeReportId);
        if(!empty($response))
        {
            return response(prepareResult(false, json_decode($response, true), getLangByLabelGroups('messages','message__category_master_list')), config('http_response.success'));
        }
        return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    }

    public function paymentCurrentStatus(Request $request)
    {
        $access_token = null;
        $tokenExpired = time();

        if(empty($access_token) || time() > $tokenExpired)
        {
            $getToken = $this->getAccessToken();
            if(!$getToken) {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
            }
            $access_token   = $getToken['access_token'];
            $tokenExpired   = $getToken['expire_time'];
        }
        $assignmentId = $request->assignmentId;
        $timeReportId = $request->timeReportId;
        $response = $this->checkPyamentStatus($access_token, $assignmentId, $timeReportId);
        if(!empty($response))
        {
            return response(prepareResult(false, json_decode($response, true), getLangByLabelGroups('messages','message__category_master_list')), config('http_response.success'));
        }
        return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    }

    //Freelancers List
    public function getTeamMember()
    {
        /*
        $access_token = null;
        $tokenExpired = time();

        if(empty($access_token) || time() > $tokenExpired)
        {
            $getToken = $this->getAccessToken();
            if(!$getToken) {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
            }
            $access_token   = $getToken['access_token'];
            $tokenExpired   = $getToken['expire_time'];
        }

        $response = $this->teamMember($access_token);
        */
        $freelancers = CoolCompanyFreelancer::with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path')->orderBy('auto_id', 'DESC')->get();
        return response(prepareResult(false, $freelancers, getLangByLabelGroups('messages','message__category_master_list')), config('http_response.success'));
    }

    public function getTeamMemberInfo($teamMemberId)
    {
        $access_token = null;
        $tokenExpired = time();

        if(empty($access_token) || time() > $tokenExpired)
        {
            $getToken = $this->getAccessToken();
            if(!$getToken) {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
            }
            $access_token   = $getToken['access_token'];
            $tokenExpired   = $getToken['expire_time'];
        }

        $response = $this->teamMemberInfo($access_token, $teamMemberId);
        if(!empty($response))
        {
            return response(prepareResult(false, json_decode($response, true), getLangByLabelGroups('messages','message__category_master_list')), config('http_response.success'));
        }
        return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    }

    public function getGroupInvoices()
    {
        $access_token = null;
        $tokenExpired = time();

        if(empty($access_token) || time() > $tokenExpired)
        {
            $getToken = $this->getAccessToken();
            if(!$getToken) {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
            }
            $access_token   = $getToken['access_token'];
            $tokenExpired   = $getToken['expire_time'];
        }

        $response = $this->groupInvoice($access_token);
        if(!empty($response))
        {
            return response(prepareResult(false, json_decode($response, true), getLangByLabelGroups('messages','message__category_master_list')), config('http_response.success'));
        }
        return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    }

    public function getGroupInvoiceById($groupInvoiceId)
    {
        $access_token = null;
        $tokenExpired = time();

        if(empty($access_token) || time() > $tokenExpired)
        {
            $getToken = $this->getAccessToken();
            if(!$getToken) {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
            }
            $access_token   = $getToken['access_token'];
            $tokenExpired   = $getToken['expire_time'];
        }
        $response = $this->groupInvoiceById($access_token, $groupInvoiceId);
        if(!empty($response))
        {
            return response(prepareResult(false, json_decode($response, true), getLangByLabelGroups('messages','message__category_master_list')), config('http_response.success'));
        }
        return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    }

    public function getGroupInvoiceReportById($groupInvoiceId)
    {
        $access_token = null;
        $tokenExpired = time();

        if(empty($access_token) || time() > $tokenExpired)
        {
            $getToken = $this->getAccessToken();
            if(!$getToken) {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
            }
            $access_token   = $getToken['access_token'];
            $tokenExpired   = $getToken['expire_time'];
        }
        $response = $this->groupInvoiceReportById($access_token, $groupInvoiceId);
        if(!empty($response))
        {
            return response(prepareResult(false, json_decode($response, true), getLangByLabelGroups('messages','message__category_master_list')), config('http_response.success'));
        }
        return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
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

            return response()->json(prepareResult(true, curl_error($curl), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
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

    private function startAssignment($accessToken, $assignmentId, $data)
    {
        $url = env('COOL_URL_FUNCTION', 'https://stage-open-api.coolcompany.com').'/api/v1/Assignments/'.$assignmentId.'/state';
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
          CURLOPT_CUSTOMREQUEST => 'PATCH',
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
            Log::channel('customlog')->error('Getting error while start  assignment.');
            $error = curl_error($curl);
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

    private function assignmentInfo($accessToken, $assignmentId)
    {
        $url = env('COOL_URL_FUNCTION', 'https://stage-open-api.coolcompany.com').'/api/v1/Assignments/'.$assignmentId;
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Accept-Language: en',
            'Accept: application/json',
            'Authorization: Bearer '.$accessToken,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        if ($response === false) {
            Log::channel('customlog')->error('Getting error while checking payment status.');
            $error = curl_error($curl);
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

    private function approveAssignment($accessToken, $assignmentId, $timeReportId)
    {
        $url = env('COOL_URL_FUNCTION', 'https://stage-open-api.coolcompany.com').'/api/v1/Assignments/'.$assignmentId.'/reports/'.$timeReportId.'/approve';
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'PATCH',
          CURLOPT_HTTPHEADER => array(
            'Accept-Language: en',
            'Accept: application/json',
            'Authorization: Bearer '.$accessToken,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        if ($response === false) {
            Log::channel('customlog')->error('Getting error while approve assignments.');
            $error = curl_error($curl);
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

    private function checkAssignmentStatus($accessToken, $assignmentId, $timeReportId)
    {
        $url = env('COOL_URL_FUNCTION', 'https://stage-open-api.coolcompany.com').'/api/v1/Assignments/'.$assignmentId.'/reports/'.$timeReportId;
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Accept-Language: en',
            'Accept: application/json',
            'Authorization: Bearer '.$accessToken,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        if ($response === false) {
            Log::channel('customlog')->error('Getting error while checking assignment status.');
            $error = curl_error($curl);
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

    private function checkPyamentStatus($accessToken, $assignmentId, $timeReportId)
    {
        $url = env('COOL_URL_FUNCTION', 'https://stage-open-api.coolcompany.com').'/api/v1/Assignments/'.$assignmentId.'/reports/'.$timeReportId.'/payment';
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Accept-Language: en',
            'Accept: application/json',
            'Authorization: Bearer '.$accessToken,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        if ($response === false) {
            Log::channel('customlog')->error('Getting error while checking payment status.');
            $error = curl_error($curl);
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

    private function teamMember($accessToken)
    {
        $url = env('COOL_URL_FUNCTION', 'https://stage-open-api.coolcompany.com').'/api/v1/Teammembers';
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Accept-Language: en',
            'Accept: application/json',
            'Authorization: Bearer '.$accessToken,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        if ($response === false) {
            Log::channel('customlog')->error('Getting error while getting team member list.');
            $error = curl_error($curl);
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

    private function teamMemberInfo($accessToken, $teamMemberId)
    {
        $url = env('COOL_URL_FUNCTION', 'https://stage-open-api.coolcompany.com').'/api/v1/Teammembers/'.$teamMemberId;
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Accept-Language: en',
            'Accept: application/json',
            'Authorization: Bearer '.$accessToken,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        if ($response === false) {
            Log::channel('customlog')->error('Getting error while getting team member info.');
            $error = curl_error($curl);
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

    private function groupInvoice($accessToken)
    {
        $url = env('COOL_URL_FUNCTION', 'https://stage-open-api.coolcompany.com').'/api/v1/GroupInvoice';
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Accept-Language: en',
            'Accept: application/json',
            'Authorization: Bearer '.$accessToken,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        if ($response === false) {
            Log::channel('customlog')->error('Getting error while getting geoup invoice.');
            $error = curl_error($curl);
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

    private function groupInvoiceById($accessToken, $groupInvoiceId)
    {
        $url = env('COOL_URL_FUNCTION', 'https://stage-open-api.coolcompany.com').'/api/v1/GroupInvoice/'.$groupInvoiceId;
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Accept-Language: en',
            'Accept: application/json',
            'Authorization: Bearer '.$accessToken,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        if ($response === false) {
            Log::channel('customlog')->error('Getting error while checking group invoice by ID.');
            $error = curl_error($curl);
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

    private function groupInvoiceReportById($accessToken, $groupInvoiceId)
    {
        $url = env('COOL_URL_FUNCTION', 'https://stage-open-api.coolcompany.com').'/api/v1/GroupInvoice/'.$groupInvoiceId.'/reports';
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Accept-Language: en',
            'Accept: application/json',
            'Authorization: Bearer '.$accessToken,
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        if ($response === false) {
            Log::channel('customlog')->error('Getting error while checking invoice report.');
            $error = curl_error($curl);
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
