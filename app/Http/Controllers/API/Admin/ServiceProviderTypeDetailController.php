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
		foreach ($request->service_provider_type as $key => $value) 
		{
			if($value['language_id']=='1')
			{
				if(ServiceProviderType::where('registration_type_id', $request->registration_type_id)->where('slug', Str::slug($value['title']))->count() > 0)
				{
					$serviceProviderType = ServiceProviderType::where('registration_type_id', $request->registration_type_id)->where('slug' ,Str::slug($value['title']))->first();
				}
				else
				{
					$serviceProviderType = new ServiceProviderType;
				}

				$serviceProviderType->registration_type_id	= $request->registration_type_id;
				$serviceProviderType->title	= $value['title'];
				$serviceProviderType->slug     = Str::slug($value['title']);
				$serviceProviderType->status   = 1;
				$serviceProviderType->save();
				break;
			}
		}

		foreach ($request->service_provider_type as $key => $value) 
		{
			if(ServiceProviderTypeDetail::where('service_provider_type_id', $serviceProviderType->id)->where('slug', Str::slug($value['title']))->where('language_id', $value['language_id'])->count() > 0)
			{
				$serviceProviderTypeDetail = ServiceProviderTypeDetail::where('service_provider_type_id', $serviceProviderType->id)->where('slug' ,Str::slug($value['title']))->where('language_id', $value['language_id'])->first();
			}
			else
			{
				$serviceProviderTypeDetail = new ServiceProviderTypeDetail;
			}

			$serviceProviderTypeDetail->service_provider_type_id 	= $serviceProviderType->id;
			$serviceProviderTypeDetail->language_id 	= $value['language_id'];
			$serviceProviderTypeDetail->title	= $value['title'];
			$serviceProviderTypeDetail->slug     = Str::slug($value['title']);
			$serviceProviderTypeDetail->status   = 1;
			$serviceProviderTypeDetail->save();
		}
		return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_service_provider_type_created')), config('http_response.created'));
	}


	public function show($id)
	{
		$serviceProviderType = ServiceProviderType::with('serviceProviderTypeDetails')->find($id);
		return response()->json(prepareResult(false, $serviceProviderType, getLangByLabelGroups('messages','message_service_provider_type_list')), config('http_response.success'));
	}



	public function serviceProviderTypeUpdate(Request $request)
	{
		foreach ($request->service_provider_type as $key => $value) 
		{
			if($value['language_id']=='1')
			{
				if(ServiceProviderType::where('registration_type_id', $request->registration_type_id)->where('slug', Str::slug($value['title']))->count() > 0)
				{
					$serviceProviderType = ServiceProviderType::where('registration_type_id', $request->registration_type_id)->where('slug' ,Str::slug($value['title']))->first();
				}
				else
				{
					$serviceProviderType = new ServiceProviderType;
				}

				$serviceProviderType->registration_type_id	= $request->registration_type_id;
				$serviceProviderType->title	= $value['title'];
				$serviceProviderType->slug     = Str::slug($value['title']);
				$serviceProviderType->status   = 1;
				$serviceProviderType->save();
				break;
			}
		}

		foreach ($request->service_provider_type as $key => $value) 
		{
			if(!empty($value['id']))
			{
				$serviceProviderTypeDetail = ServiceProviderTypeDetail::find($value['id']);
			}
			else
			{
				$serviceProviderTypeDetail = new ServiceProviderTypeDetail;
			}

			$serviceProviderTypeDetail->service_provider_type_id 	= $serviceProviderType->id;
			$serviceProviderTypeDetail->language_id 	= $value['language_id'];
			$serviceProviderTypeDetail->title	= $value['title'];
			$serviceProviderTypeDetail->slug     = Str::slug($value['title']);
			$serviceProviderTypeDetail->status   = 1;
			$serviceProviderTypeDetail->save();
		}
		return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_service_provider_type_updated')), config('http_response.created'));
	}



	public function serviceProviderTypeDelete($id)
	{
		if(ServiceProviderDetail::where('service_provider_type_id', $id)->count()<1)
		{
			ServiceProviderType::find($id)->delete();
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
