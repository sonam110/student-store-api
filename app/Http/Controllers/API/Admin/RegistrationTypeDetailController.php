<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationType;
use App\Models\RegistrationTypeDetail;
use App\Models\ServiceProviderDetail;
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
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function store(Request $request)
	{        
		foreach ($request->registration_type as $key => $value) 
		{
			if($value['language_id']=='1')
			{
				if(RegistrationType::where('slug', Str::slug($value['title']))->count() > 0)
				{
					$registrationType = RegistrationType::where('slug' ,Str::slug($value['title']))->first();
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

		foreach ($request->registration_type as $key => $value) 
		{
			if(RegistrationTypeDetail::where('slug', Str::slug($value['title']))->where('language_id', $value['language_id'])->count() > 0)
			{
				$registrationTypeDetail = RegistrationTypeDetail::where('slug' ,Str::slug($value['title']))->where('language_id', $value['language_id'])->first();
			}
			else
			{
				$registrationTypeDetail = new RegistrationTypeDetail;
			}

			$registrationTypeDetail->language_id 	= $value['language_id'];
			$registrationTypeDetail->registration_type_id 	= $registrationType->id;
			$registrationTypeDetail->title	= $value['title'];
			$registrationTypeDetail->slug     = Str::slug($value['title']);
			$registrationTypeDetail->status   = 1;
			$registrationTypeDetail->save();
		}
		return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_registration_type_detail_created')), config('http_response.created'));
	}


	public function show($id)
	{
		$registrationType = RegistrationType::with('registrationTypeDetails')->find($id);
		return response()->json(prepareResult(false, $registrationType, 'Registration Type retrieved successfully.'), config('http_response.success'));
	}



	public function registrationTypeDetailUpdate(Request $request)
	{
		foreach ($request->registration_type as $key => $value) 
		{
			if($value['language_id']=='1')
			{
				$findDetailData = RegistrationTypeDetail::find($value['id']);
				$registrationType = RegistrationType::find($findDetailData->registration_type_id);
				if($registrationType)
				{
					$registrationType->title	= $value['title'];
					$registrationType->slug     = Str::slug($value['title']);
					$registrationType->status   = 1;
					$registrationType->save();
					break;
				}
			}
		}

		foreach ($request->registration_type as $key => $value) 
		{
			if(!empty($value['id']))
			{
				$registrationTypeDetail = RegistrationTypeDetail::find($value['id']);
			}
			else
			{
				$registrationTypeDetail = new RegistrationTypeDetail;
			}

			$registrationTypeDetail->language_id 	= $value['language_id'];
			$registrationTypeDetail->registration_type_id 	= $registrationType->id;
			$registrationTypeDetail->title	= $value['title'];
			$registrationTypeDetail->slug     = Str::slug($value['title']);
			$registrationTypeDetail->status   = 1;
			$registrationTypeDetail->save();
		}
		return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_registration_type_detail_created')), config('http_response.created'));
	}


	
	public function registrationTypeDestroy($id)
	{
		if(ServiceProviderDetail::where('registration_type_id', $id)->count()<1)
		{
			$isExist = RegistrationType::find($id);
			if(!$isExist)
			{
				return response()->json(prepareResult(true, [], "Record not found."), config('http_response.bad_request'));
			}
			$isExist->delete();
			return response()->json(prepareResult(false, [], "Deleted successfully."), config('http_response.success'));
		}
		return response()->json(prepareResult(true, [], "This registration type cannot be removed because some users are registered with it."), config('http_response.bad_request'));
	}
	

	public function registrationTypeFilter(Request $request)
	{
		try
		{
			$registrationTypes = RegistrationType::with('registrationTypeDetails')->withCount('serviceProviderTypes');
			if(!empty($request->title))
			{
				$records = $registrationTypes->where('title', 'LIKE','%'.$request->title.'%');
			}

			if(!empty($request->per_page_record))
			{
			    $records = $registrationTypes->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
			    $records = $registrationTypes->get();
			}
			return response(prepareResult(false, $records, "Registration Types retrieved successfully."), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}
}
