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
use App\Models\LangForDDL;
use mervick\aesEverywhere\AES256;

class UserCvDetailController extends Controller
{
	public function cvDetailUpdate(Request $request)
	{
		if($request->is_published == true)
		{
			$published_at = date('Y-m-d');
		}
		else
		{
			$published_at = null;
		}

		$userCvDetail = UserCvDetail::firstOrNew(['user_id' =>  Auth::id()]);
		if($request->present_my_cv==1)
		{
			$userCvDetail->is_published         = $request->is_published;
			$userCvDetail->published_at         = $published_at;
			$userCvDetail->save();

			return response(prepareResult(false, $userCvDetail, getLangByLabelGroups('messages','message_user_cv_presented')), config('http_response.created'));
		}

		$validation = \Validator::make($request->all(),[ 
			'title' => 'required',
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}

		$destinationPath = 'uploads/';
		$cv_name = Str::slug(substr(AES256::decrypt(Auth::user()->first_name, env('ENCRYPTION_KEY')), 0, 15)).'-'.Auth::user()->qr_code_number.'-'.rand(1,5).'.pdf';
		
	    $userCvDetail->user_id         		= Auth::id();
		$userCvDetail->address_detail_id    = $request->address_detail_id;
		$userCvDetail->title         		= $request->title;
		$userCvDetail->languages_known      = (!empty($request->languages_known)) ? json_encode($request->languages_known) : null;
		$userCvDetail->key_skills         	= (!empty($request->key_skills)) ? json_encode($request->key_skills) : null;
		$userCvDetail->preferred_job_env    = (!empty($request->preferred_job_env)) ? json_encode($request->preferred_job_env) : null;
		$userCvDetail->other_description    = $request->other_description;
		$userCvDetail->is_published         = $request->is_published;
		$userCvDetail->published_at     	= $published_at;
		$userCvDetail->cv_url				= $request->cv_url;
		$userCvDetail->generated_cv_file	= env('CDN_DOC_URL').$destinationPath.$cv_name;
		$userCvDetail->cv_update_status 		= 1;
		$userCvDetail->total_experience     = $request->total_experience;
		if($userCvDetail->save())
		{
			//Create CV pdf
			if(Auth::user()->userWorkExperiences->count() > 0 && Auth::user()->userEducationDetails->count() > 0)
			{
				//createResume($cv_name,Auth::user());
				$cvDetail = Auth::user()->userCvDetail;
				// $cvDetail->generated_cv_file = env('CDN_DOC_URL').$destinationPath.$cv_name;
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

            foreach ($request->languages_known as $key => $lang) {
                if(LangForDDL::where('name', $lang)->count() < 1)
                {
                    $langddl = new LangForDDL;
                    $langddl->name  = $lang;
                    $langddl->save();
                }
            }

            createResume($cv_name,Auth::user());

			return response(prepareResult(false, $userCvDetail, getLangByLabelGroups('messages','message_user_cv_detail_updated')), config('http_response.created'));
		}
		else
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function downloadResume($user_id)
	{
		$findUser = User::where('user_type_id', '2')->find($user_id);
		if($findUser)
		{
			$destinationPath = 'uploads/';
	        $cv_name = Str::slug(substr(AES256::decrypt($findUser->first_name, env('ENCRYPTION_KEY')), 0, 15)).'-'.$findUser->qr_code_number.'-'.rand(1,5).'.pdf';

	        $resumeDownloadPath = env('CDN_DOC_URL').$destinationPath.$cv_name;

	        createResume($cv_name, $findUser);
	        return response()->json(prepareResult(false, $resumeDownloadPath, 'Download Resume'), config('http_response.success'));
		}
		else
		{
			return response()->json(prepareResult(true, 'User not found.', 'User not found.'), config('http_response.not_found'));
		}
		
	}
}