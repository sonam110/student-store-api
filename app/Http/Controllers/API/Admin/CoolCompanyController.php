<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use App\Models\CoolCompanyAssignment;


class CoolCompanyController extends Controller
{
    public function index(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $categoryMasters = CoolCompanyAssignment::with('user:id,first_name,last_name,profile_pic_path','user.studentDetail:user_id,cool_company_id')->orderBy('created_at','DESC')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $categoryMasters = CoolCompanyAssignment::with('user:id,first_name,last_name,profile_pic_path','user.studentDetail:user_id,cool_company_id')->orderBy('created_at','DESC')->get();
            }
            return response(prepareResult(false, $categoryMasters, getLangByLabelGroups('messages','message__category_master_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}
