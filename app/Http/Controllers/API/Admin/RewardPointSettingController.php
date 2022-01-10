<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardPointSetting;
use App\Http\Resources\RewardPointSettingResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class RewardPointSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
    	try
    	{
            if(!empty($request->per_page_record))
            {
                $rewardPointSettings = RewardPointSetting::simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $rewardPointSettings = RewardPointSetting::get();
            }
    		return response(prepareResult(false, $rewardPointSettings, getLangByLabelGroups('messages','message_reward_point_setting_list')), config('http_response.success'));
    	}
    	catch (\Throwable $exception) 
    	{
    		\Log::error($exception);
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
    		'reward_points'  => 'required'
    	]);

    	if ($validation->fails()) {
    		return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
    	}

    	DB::beginTransaction();
    	try
    	{
    		$RewardPointSetting = new RewardPointSetting;
    		$RewardPointSetting->reward_points             	= $request->reward_points;
    		$RewardPointSetting->equivalent_currency_value 	= $request->equivalent_currency_value;
    		$RewardPointSetting->applicable_from         	= $request->applicable_from;
    		$RewardPointSetting->applicable_to         		= $request->applicable_to;
            $RewardPointSetting->min_range                  = $request->min_range;
            $RewardPointSetting->min_value                  = $request->min_value;
            $RewardPointSetting->mid_range                  = $request->mid_range;
            $RewardPointSetting->mid_value                  = $request->mid_value;
            $RewardPointSetting->max_range                  = $request->max_range;
            $RewardPointSetting->max_value                  = $request->max_value;
    		$RewardPointSetting->status      				= $request->status;
    		$RewardPointSetting->save();
    		DB::commit();
    		return response()->json(prepareResult(false, new RewardPointSettingResource($RewardPointSetting), getLangByLabelGroups('messages','message_reward_point_setting_created')), config('http_response.created'));
    	}
    	catch (\Throwable $exception)
    	{
    		DB::rollback();
    		\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\RewardPointSetting  $RewardPointSetting
     * @return \Illuminate\Http\Response
     */
    public function show(RewardPointSetting $RewardPointSetting)
    {
    	return response()->json(prepareResult(false, new RewardPointSettingResource($RewardPointSetting), getLangByLabelGroups('messages','message_reward_point_setting_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RewardPointSetting  $RewardPointSetting
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,RewardPointSetting $RewardPointSetting)
    {
    	$validation = Validator::make($request->all(), [
    		'reward_points' => 'required'
    	]);

    	if ($validation->fails()) {
    		return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
    	}

    	DB::beginTransaction();
    	try
    	{
    		$RewardPointSetting->reward_points             	= $request->reward_points;
    		$RewardPointSetting->equivalent_currency_value 	= $request->equivalent_currency_value;
    		$RewardPointSetting->applicable_from         	= $request->applicable_from;
    		$RewardPointSetting->applicable_to         		= $request->applicable_to;
            $RewardPointSetting->min_range                  = $request->min_range;
            $RewardPointSetting->min_value                  = $request->min_value;
            $RewardPointSetting->mid_range                  = $request->mid_range;
            $RewardPointSetting->mid_value                  = $request->mid_value;
            $RewardPointSetting->max_range                  = $request->max_range;
            $RewardPointSetting->max_value                  = $request->max_value;
    		$RewardPointSetting->status      				= $request->status;
    		$RewardPointSetting->save();
    		DB::commit();
    		return response()->json(prepareResult(false, new RewardPointSettingResource($RewardPointSetting), getLangByLabelGroups('messages','message_reward_point_setting_updated')), config('http_response.success'));
    	}
    	catch (\Throwable $exception)
    	{
    		DB::rollback();
    		\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }
}
