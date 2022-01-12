<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContestWinner;
use App\Http\Resources\ContestWinnerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use App\Models\Language;

class ContestWinnerController extends Controller
{
    function __construct()
    {
        $this->lang_id = Language::select('id')->first()->id;
        if(!empty(request()->lang_id))
        {
            $this->lang_id = request()->lang_id;
        }
    }

	public function index(Request $request)
	{
		try
		{
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            $contestWinners = ContestWinner::with('contest','user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','contest.categoryMaster','contest.subCategory')
            ->with(['contest.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['contest.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
            ->orderBy('created_at','DESC');
			if(!empty($request->per_page_record))
			{
			   $contestWinners = $contestWinners->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
			    $contestWinners = $contestWinners->get();
			}
			return response(prepareResult(false, $contestWinners, getLangByLabelGroups('messages','message_contest_application_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}


	public function show(ContestWinner $contestWinner)
	{	
        $contestWinner = ContestWinner::with('contest','user')->find($contestWinner->id);	
		return response()->json(prepareResult(false, $contestWinner, getLangByLabelGroups('messages','message_contest_application_list')), config('http_response.success'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\ContestWinner  $contestWinner
	 * @return \Illuminate\Http\Response
	 */
	

   
	public function destroy(ContestWinner $contestWinner)
	{
		$contestWinner->delete();
		return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_contest_application_deleted')), config('http_response.success'));
	}

	public function winnerFilter(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

        	$winners = ContestWinner::select('contest_winners.*')
        	        ->join('users', function ($join) {
        	            $join->on('contest_winners.user_id', '=', 'users.id');
        	        })
                    ->join('contests', function ($join) {
                        $join->on('contest_winners.contest_id', '=', 'contests.id');
                    })
        	        ->with('contest.categoryMaster','contest.subCategory','contest.cancellationRanges','user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path,status','user.cvDetail','user.defaultAddress')
                    ->with(['contest.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['contest.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
                    
            if(!empty($request->user_id))
            {
                $winners->where('contest_winners.user_id',$request->user_id);
            }
            if(!empty($request->contest_id))
            {
                $winners->where('contest_winners.contest_id',$request->contest_id);
            }
        	if(!empty($request->type))
            {
                $winners->where('contests.type',$request->type);
            }
            if(!empty($request->prize))
            {
                $winners->where('contest_winners.prize',$request->prize);
            }
            if(!empty($request->mode))
            {
                $winners->where('contests.mode',$request->mode);
            }
            if(!empty($request->contest_title))
            {
                $winners->where('contests.title', 'LIKE', '%'.$request->contest_title.'%');
            }
            if(!empty($request->first_name))
            {
                $winners->where('users.first_name', 'LIKE', '%'.$request->first_name.'%');
            }
            if(!empty($request->last_name))
            {
                $winners->where('users.last_name', 'LIKE', '%'.$request->last_name.'%');
            }
            if(!empty($request->email))
            {
                $$winners->where('users.email', 'LIKE', '%'.$request->email.'%');
            }
            if(!empty($request->userStatus))
            {
                $winners->whereIn('users.status', $request->userStatus);
            }

            if(!empty($request->user_type))
            {
                if($request->user_type == 'student')
                {
                    $user_type_id = '2';
                }
                elseif($request->user_type == 'company')
                {
                    $user_type_id = '3';
                }
                else
                {
                    $user_type_id = '4';
                }
                $winners->where('users.user_type_id', $user_type_id);
            }

            if(!empty($request->per_page_record))
            {
                $winnersData = $winners->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $winnersData = $winners->get();
            }
            return response(prepareResult(false, $winnersData, getLangByLabelGroups('messages','messages_contest_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

}
