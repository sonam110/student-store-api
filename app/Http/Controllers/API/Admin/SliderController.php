<?php

namespace App\Http\Controllers\API\Admin;


use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class SliderController extends Controller
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
    			$sliders = Slider::with('language:id,title')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
    		}
    		else
    		{
    			$sliders = Slider::with('language:id,title')->get();
    		}
    		return response(prepareResult(false, $sliders, getLangByLabelGroups('messages','message_slider_list')), config('http_response.success'));
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
    		'image_path'  => 'required'
    	]);

    	if ($validation->fails()) {
    		return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
    	}

    	DB::beginTransaction();
    	try
    	{
    		$slider = new Slider;
    		$slider->language_id          	= $request->language_id;
    		$slider->image_path   			= $request->image_path;
    		$slider->save();
    		DB::commit();
    		return response()->json(prepareResult(false, $slider, getLangByLabelGroups('messages','message_slider_created')), config('http_response.created'));
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
     * @param  \App\Slider  $slider
     * @return \Illuminate\Http\Response
     */
    public function show(Slider $slider)
    {
    	return response()->json(prepareResult(false, $slider, getLangByLabelGroups('messages','message_slider_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Slider  $slider
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,Slider $slider)
    {
    	$validation = Validator::make($request->all(), [
    		'image_path' => 'required'
    	]);

    	if ($validation->fails()) {
    		return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
    	}

    	DB::beginTransaction();
    	try
    	{
    		$slider->language_id          	= $request->language_id;
    		$slider->image_path   		    = $request->image_path;
    		$slider->save();
    		DB::commit();
    		return response()->json(prepareResult(false, $slider, getLangByLabelGroups('messages','message_slider_updated')), config('http_response.success'));
    	}
    	catch (\Throwable $exception)
    	{
    		DB::rollback();
    		\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Slider $slider
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Slider $slider)
    {
    	$slider->delete();
    	return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_slider_deleted')), config('http_response.success'));
    }
}