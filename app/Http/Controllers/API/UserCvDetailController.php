<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserCvDetail;
use App\Models\JobTag;
use App\Models\User;
use App\Models\ServiceProviderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PDF;
use Str;
use DB;
use Auth;

class UserCvDetailController extends Controller
{
	public function cvDetailUpdate(Request $request)
	{
		$validation = \Validator::make($request->all(),[ 
			'title' => 'required',
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}
		if($request->is_published == true)
		{
			$published_at = date('Y-m-d');
		}
		else
		{
			$published_at = null;
		}

		$destinationPath = 'uploads/';
		$cv_name = Str::slug(substr(Auth::user()->first_name, 0, 15)).'-'.Str::slug(substr(Auth::user()->last_name, 0, 15)).'-'.Auth::user()->qr_code_number.'.pdf';
		$userCvDetail = UserCvDetail::firstOrNew(['user_id' =>  Auth::id()]);
	    $userCvDetail->user_id         		= Auth::id();
		$userCvDetail->address_detail_id    = $request->address_detail_id;
		$userCvDetail->title         		= $request->title;
		$userCvDetail->languages_known      = json_encode($request->languages_known);
		$userCvDetail->key_skills         	= json_encode($request->key_skills);
		$userCvDetail->preferred_job_env    = json_encode($request->preferred_job_env);
		$userCvDetail->other_description    = $request->other_description;
		$userCvDetail->is_published         = $request->is_published;
		$userCvDetail->published_at         = $published_at;
		$userCvDetail->cv_url				= $request->cv_url;
		// $userCvDetail->generated_cv_file	= env('CDN_DOC_URL').$destinationPath.$cv_name;
		$userCvDetail->cv_update_status 		= 1;
		$userCvDetail->total_experience     = $request->total_experience;
		if($userCvDetail->save())
		{
			//Create CV pdf
			if(Auth::user()->userWorkExperiences->count() > 0 && Auth::user()->userEducationDetails->count() > 0)
			{
				createResume($cv_name,Auth::user());
				$cvDetail = Auth::user()->userCvDetail;
				$cvDetail->generated_cv_file = env('CDN_DOC_URL').$destinationPath.$cv_name;
				$cvDetail->cv_update_status = 0;
				$cvDetail->save();
			}
			

			if(JobTag::where('cv_id', $userCvDetail->id)->count()>0)
			{
				JobTag::where('cv_id', $userCvDetail->id)->delete();
			}

			foreach ($request->key_skills as $key => $tag) {
                if(JobTag::where('title', $tag)->where('cv_id', $userCvDetail->id)->count()<1)
                {
                    $jobTag = new JobTag;
                    $jobTag->cv_id     	= $userCvDetail->id;
                    $jobTag->title      = $tag;
                    $jobTag->user_id    = Auth::id();
                    $jobTag->save();
                }
            }
			return response(prepareResult(false, $userCvDetail, getLangByLabelGroups('messages','message_user_cv_detail_updated')), config('http_response.created'));
		}
		else
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	
}