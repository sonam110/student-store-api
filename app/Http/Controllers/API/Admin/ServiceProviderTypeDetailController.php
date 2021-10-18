<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceProviderDetail;
use App\Models\ServiceProviderType;
use App\Models\ServiceProviderTypeDetail;
use App\Http\Resources\ServiceProviderTypeDetailResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class ServiceProviderTypeDetailController extends Controller
{
	public function index(Request $request)
	{
		try
		{
			if(!empty($request->per_page_record))
			{
			    $serviceProviderTypeDetails = ServiceProviderTypeDetail::simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
			    $serviceProviderTypeDetails = ServiceProviderTypeDetail::get();
			}
			return response(prepareResult(false, ServiceProviderTypeDetailResource::collection($serviceProviderTypeDetails), "Service-Provider-Types retrieved successfully."), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function store(Request $request)
	{   

		foreach ($request->registration_type as $key => $value) 
		{
			if($value['language_id']=='1')
			{
				if(RegistrationType::where('title', $value['title'])->count() > 0)
				{
					$registrationType = RegistrationType::where('title' ,$value['title'])->first();
				}
				else
				{
					$registrationType = new RegistrationType;
				}

				$registrationType->title	= $value['title'];
				$registrationType->slug     = Str::slug($value['title']);
				$registrationType->status   = 1;
				$registrationType->save();
				break;
			}
		}

	    
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

			$serviceProviderType = ServiceProviderType::where('title',$title)->first();

			if($serviceProviderType)
			{
				if(ServiceProviderTypeDetail::where('slug',$serviceProviderType->slug)->where('language_id',$language_id)->count() > 0)
				{
					return response()->json(prepareResult(true, [], "Dublicate Entry."), config('http_response.bad_request'));
				}
			}
			else
			{
				$serviceProviderType 						= new ServiceProviderType;
				$serviceProviderType->registration_type_id  = $request->registration_type_id;
				$serviceProviderType->title     			= $title;
				$serviceProviderType->slug     				= $slug;
				$serviceProviderType->status    			= $request->status;
				$serviceProviderType->save();

			}           	


			$sptDetail = new ServiceProviderTypeDetail;
			$sptDetail->service_provider_type_id 	= $serviceProviderType->id;
			$sptDetail->language_id            		= $language_id;
			$sptDetail->title      					= $request->language_title;
			$sptDetail->slug     					= $serviceProviderType->slug;
			$sptDetail->description                 = $request->description;
			$sptDetail->status                 		= $request->status;
			$sptDetail->save();
			DB::commit();
			return response()->json(prepareResult(false, new ServiceProviderTypeDetailResource($sptDetail), getLangByLabelGroups('messages','message_service_provider_type_created')), config('http_response.created'));
		}
		catch (\Throwable $exception)
		{
			DB::rollback();
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}


	public function show(ServiceProviderTypeDetail $serviceProviderTypeDetail)
	{
		return response()->json(prepareResult(false, new ServiceProviderTypeDetailResource($serviceProviderTypeDetail), getLangByLabelGroups('messages','message_service_provider_type_list')), config('http_response.success'));
	}



	public function update(Request $request,ServiceProviderTypeDetail $serviceProviderTypeDetail)
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

			$serviceProviderType = ServiceProviderType::where('id',$serviceProviderTypeDetail->service_provider_type_id)->first();

			$serviceProviderType->registration_type_id  = $request->registration_type_id;
			$serviceProviderType->title     			= $title;
			$serviceProviderType->slug     				= $slug;
			$serviceProviderType->status    			= $request->status;
			$serviceProviderType->save();

			$serviceProviderTypeDetail->service_provider_type_id 	= $serviceProviderType->id;
			$serviceProviderTypeDetail->language_id            		= $language_id;
			$serviceProviderTypeDetail->title      					= $request->language_title;
			$serviceProviderTypeDetail->slug     					= $serviceProviderType->slug;
			$serviceProviderTypeDetail->description                 = $request->description;
			$serviceProviderTypeDetail->status                 		= $request->status;
			$serviceProviderTypeDetail->save();

			
			DB::commit();
			return response()->json(prepareResult(false, new ServiceProviderTypeDetailResource($serviceProviderTypeDetail), getLangByLabelGroups('messages','message_service_provider_type_updated')), config('http_response.success'));
		}
		catch (\Throwable $exception)
		{
			DB::rollback();
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}



	public function destroy(ServiceProviderTypeDetail $serviceProviderTypeDetail)
	{
		if(ServiceProviderDetail::where('service_provider_type_id', $serviceProviderTypeDetail->id)->count()<1)
		{
			$serviceProviderTypeDetail->delete();
			return response()->json(prepareResult(false, [], "Deleted successfully."), config('http_response.success'));
		}
		return response()->json(prepareResult(true, [], "This registration type cannot be removed because some users are registered with it."), config('http_response.bad_request'));

		
		return response()->json(prepareResult(false, [], "Deleted successfully."), config('http_response.success'));
	}

	public function serviceProviderTypeFilter(Request $request)
	{
		try
		{
			$serviceProviderTypes = ServiceProviderType::with('serviceProviderTypeDetails');
			if(!empty($request->registration_type_id))
			{
				$records = $serviceProviderTypes->where('registration_type_id', $request->registration_type_id);
			}

			if(!empty($request->title))
			{
				$records = $serviceProviderTypes->where('title', 'LIKE','%'.$request->title.'%');
			}

			if(!empty($request->per_page_record))
			{
			    $records = $serviceProviderTypes->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
			    $records = $serviceProviderTypes->get();
			}
			return response(prepareResult(false, $records, "Service Provider Types retrieved successfully."), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}
}
