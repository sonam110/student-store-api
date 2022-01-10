<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddressDetail;
use App\Http\Resources\AddressDetailResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;

class UserAddressMgmtController extends Controller
{
    public function store(Request $request)
    {        
        $validation = Validator::make($request->all(), [
            'country'  => 'required',
            'user_id'  => 'required|exists:users,id'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            if($request->is_default == true)
            {
                AddressDetail::where('user_id', $request->user_id)->update(['is_default' => false]);
            }
            $addressDetail = new AddressDetail;
            $addressDetail->user_id         = $request->user_id;
            $addressDetail->latitude        = $request->latitude;
            $addressDetail->longitude       = $request->longitude;
            $addressDetail->country         = $request->country;
            $addressDetail->state           = $request->state;
            $addressDetail->city            = $request->city;
            $addressDetail->zip_code        = $request->zip_code;
            $addressDetail->full_address    = $request->full_address;
            $addressDetail->address_type    = $request->address_type;
            $addressDetail->is_default      = $request->is_default;
            $addressDetail->is_deleted      = false;
            $addressDetail->status          = 1;
            $addressDetail->save();
            DB::commit();
            return response()->json(prepareResult(false, new AddressDetailResource($addressDetail), getLangByLabelGroups('messages','message_address_detail_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}
