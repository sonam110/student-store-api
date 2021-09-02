<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AddressDetail;
use App\Http\Resources\AddressDetailResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;

class UserAddressDetailController extends Controller
{
    public function index()
    {
        try
        {
            $addressDetails = Auth::user()->addressDetails;
            return response(prepareResult(false, AddressDetailResource::collection($addressDetails), getLangByLabelGroups('messages','message__address_detail_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {        
        $validation = Validator::make($request->all(), [
            'country'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            if($request->is_default == true)
            {
                AddressDetail::where('user_id',Auth::id())->update(['is_default' => false]);
            }
            $addressDetail = new AddressDetail;
            $addressDetail->user_id         = Auth::user()->id;
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
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AddressDetail  $addressDetail
     * @return \Illuminate\Http\Response
     */
    public function show(AddressDetail $addressDetail)
    {
        return response()->json(prepareResult(false, new AddressDetailResource($addressDetail), getLangByLabelGroups('messages','message_address_detail_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AddressDetail  $addressDetail
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,AddressDetail $addressDetail)
    {
        $validation = Validator::make($request->all(), [
            'country' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            if($request->is_default == true)
            {
                AddressDetail::where('user_id',Auth::id())->update(['is_default' => false]);
            }
            
            $addressDetail->user_id         = Auth::user()->id;
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
            return response()->json(prepareResult(false, new AddressDetailResource($addressDetail), getLangByLabelGroups('messages','message_address_detail_updated')), config('http_response.success'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\AddressDetail $addressDetail
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(AddressDetail $addressDetail)
    {
        // $addressDetail->delete();
        $addressDetail->update(['is_deleted'=>true]);
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_address_detail_deleted')), config('http_response.success'));
    }
}
