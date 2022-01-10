<?php

namespace App\Http\Controllers\API\Contests;

use App\Http\Controllers\Controller;
use App\Models\ContestWinner;
use App\Models\ContestApplication;
use App\Models\Contest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use Event;

class ContestWinnerController extends Controller
{
    public function store(Request $request)
    {        
    	$validation = Validator::make($request->all(), [
    		'first_winner_id'  => 'required'
    	]);

    	if ($validation->fails()) {
    		return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
    	}

    	DB::beginTransaction();
    	try
    	{
            
            $contestApplication = ContestApplication::find($request->first_winner_id);
            $contest = Contest::find($contestApplication->contest_id);


            $contestWinner = new ContestWinner;
            $contestWinner->contest_application_id  = $contestApplication->id;
            $contestWinner->user_id                 = $contestApplication->user_id;
            $contestWinner->contest_id              = $contestApplication->contest_id;
            $contestWinner->prize                   = 'first';
            $contestWinner->contest_prize_detail    = json_decode($contest->winner_prizes)[0];
            $contestWinner->remark                  = $request->remark;
            $contestWinner->save();

            $contestApplication->winner = 'first_winner';
            $contestApplication->save();

            $user = $contestApplication->user;
            $title = 'First winner';
            $body =  'You have been selected as first winner for Contest '.$contest->title.'.';
            $type = 'Contest Winner';
            pushNotification($title,$body,$user,$type,true,'contest applicant','contest',$contestApplication->id,'joined');


            if(!empty($request->second_winner_id))
            {
                $contestApplication = ContestApplication::find($request->second_winner_id);
                $contest = Contest::find($contestApplication->contest_id);

                $contestWinner = new ContestWinner;
                $contestWinner->contest_application_id  = $contestApplication->id;
                $contestWinner->user_id                 = $contestApplication->user_id;
                $contestWinner->contest_id              = $contestApplication->contest_id;
                $contestWinner->prize                   = 'second';
                $contestWinner->contest_prize_detail    = json_decode($contest->winner_prizes)[1];
                $contestWinner->remark                  = $request->remark;
                $contestWinner->save();

                $contestApplication->winner = 'second_winner';
                $contestApplication->save();

                $user = $contestApplication->user;
                $title = 'Second winner';
                $body =  'You have been selected as second winner for Contest '.$contest->title.'.';
                $type = 'Contest Winner';
                pushNotification($title,$body,$user,$type,true,'contest applicant','contest',$contestApplication->id,'joined');
                
            }

            if(!empty($request->third_winner_id))
            {
                $contestApplication = ContestApplication::find($request->third_winner_id);
                $contest = Contest::find($contestApplication->contest_id);

                $contestWinner = new ContestWinner;
                $contestWinner->contest_application_id  = $contestApplication->id;
                $contestWinner->user_id                 = $contestApplication->user_id;
                $contestWinner->contest_id              = $contestApplication->contest_id;
                $contestWinner->prize                   = 'third';
                $contestWinner->contest_prize_detail    = json_decode($contest->winner_prizes)[2];
                $contestWinner->remark                  = $request->remark;
                $contestWinner->save();

                $contestApplication->winner = 'third_winner';
                $contestApplication->save();

                $user = $contestApplication->user;
                $title = 'Third winner';
                $body =  'You have been selected as third winner for Contest '.$contest->title.'.';
                $type = 'Contest Winner';
                pushNotification($title,$body,$user,$type,true,'contest applicant','contest',$contestApplication->id,'joined');
                
            }
            
    		DB::commit();
    		return response()->json(prepareResult(false, $contestWinner, getLangByLabelGroups('messages','message_contest_application_created')), config('http_response.created'));
    	}
    	catch (\Throwable $exception)
    	{
    		DB::rollback();
    		\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }
}
