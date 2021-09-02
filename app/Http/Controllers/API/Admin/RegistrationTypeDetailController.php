<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationType;
use App\Models\RegistrationTypeDetail;
use App\Http\Resources\RegistrationTypeDetailResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class RegistrationTypeDetailController extends Controller
{
	public function index(Request $request)
	{
		try
		{
			if(!empty($request->per_page_record))
			{
			    $registrationTypeDetails = RegistrationTypeDetail::simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
			    $registrationTypeDetails = RegistrationTypeDetail::get();
			}
			return response(prepareResult(false, RegistrationTypeDetailResource::collection($registrationTypeDetails), "Service-Provider-Types retrieved successfully."), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function store(Request $request)
	{        
		$validation = Validator::make($request->all(), [
			'title'  => 'required'
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}

		DB::beginTransaction();
		try
		{
			$language_id 	= $request->language_id;
			$title 			= $request->title;
			$slug 			= Str::slug($request->title);

			$registrationType = RegistrationType::where('title',$title)->first();

			if($registrationType)
			{
				if(RegistrationTypeDetail::where('slug',$registrationType->slug)->where('language_id',$language_id)->count() > 0)
				{
					return response()->json(prepareResult(true, [], "Dublicate Entry."), config('http_response.bad_request'));
				}
			}
			else
			{
				$registrationType 						= new RegistrationType;
				$registrationType->title     			= $title;
				$registrationType->slug     			= $slug;
				$registrationType->status    			= $request->status;
				$registrationType->save();

			}           	


			$sptDetail = new RegistrationTypeDetail;
			$sptDetail->registration_type_id 		= $registrationType->id;
			$sptDetail->language_id            		= $language_id;
			$sptDetail->title      					= $request->language_title;
			$sptDetail->slug     					= $registrationType->slug;
			$sptDetail->description                 = $request->description;
			$sptDetail->status                 		= $request->status;
			$sptDetail->save();
			DB::commit();
			return response()->json(prepareResult(false, new RegistrationTypeDetailResource($sptDetail), getLangByLabelGroups('messages','message_service_provider_type_created')), config('http_response.created'));
		}
		catch (\Throwable $exception)
		{
			DB::rollback();
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}


	public function show(RegistrationTypeDetail $registrationTypeDetail)
	{
		return response()->json(prepareResult(false, new RegistrationTypeDetailResource($registrationTypeDetail), getLangByLabelGroups('messages','message_service_provider_type_list')), config('http_response.success'));
	}



	public function update(Request $request,RegistrationTypeDetail $registrationTypeDetail)
	{
		$validation = Validator::make($request->all(), [
			'title' => 'required'
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}

		DB::beginTransaction();
		try
		{
			$language_id 	= $request->language_id;
			$title 			= $request->title;
			$slug 			= Str::slug($request->title);

			$registrationType = RegistrationType::where('id',$registrationTypeDetail->registration_type_id)->first();

			
			$registrationType->title     			= $title;
			$registrationType->slug     			= $slug;
			$registrationType->status    			= $request->status;
			$registrationType->save();

			$registrationTypeDetail->registration_type_id 	= $registrationType->id;
			$registrationTypeDetail->language_id            = $language_id;
			$registrationTypeDetail->title      			= $request->language_title;
			$registrationTypeDetail->slug     				= $registrationType->slug;
			$registrationTypeDetail->description            = $request->description;
			$registrationTypeDetail->status                 = $request->status;
			$registrationTypeDetail->save();

			
			DB::commit();
			return response()->json(prepareResult(false, new RegistrationTypeDetailResource($registrationTypeDetail), getLangByLabelGroups('messages','message_service_provider_type_updated')), config('http_response.success'));
		}
		catch (\Throwable $exception)
		{
			DB::rollback();
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}



	// public function destroy(RegistrationTypeDetail $registrationTypeDetail)
	// {
	// 	$registrationTypeDetail->delete();
	// 	return response()->json(prepareResult(false, [], "Deleted successfully."), config('http_response.success'));
	// }
}
