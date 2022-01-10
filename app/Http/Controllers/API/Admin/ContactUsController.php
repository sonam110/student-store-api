<?php

namespace App\Http\Controllers\API\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\ContactUs;
use App\Models\User;
use Str;
class ContactUsController extends Controller
{
    public function index(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $data = ContactUs::orderBy('created_at','DESC')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $data = ContactUs::orderBy('created_at','DESC')->get();
            }
            return response(prepareResult(false, $data, getLangByLabelGroups('messages','message_contact_us_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function destroy($id)
    {
        try
        {
            ContactUs::find($id)->delete();
            return response(prepareResult(false, [], getLangByLabelGroups('messages','message_contact_us_deleted')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

}
