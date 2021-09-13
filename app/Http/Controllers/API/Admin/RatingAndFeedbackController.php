<?php

namespace App\Http\Controllers\API\Admin;


use App\Http\Controllers\Controller;
use App\Models\RatingAndFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RatingAndFeedbacksImport;
use Auth;

class RatingAndFeedbackController extends Controller
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
                $ratingAndFeedbacks = RatingAndFeedback::with('product','fromUser','toUser')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $ratingAndFeedbacks = RatingAndFeedback::with('product','fromUser','toUser')->get();
            }
            return response(prepareResult(false, $ratingAndFeedbacks, getLangByLabelGroups('messages','message_reason_for_action_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    
    public function show(RatingAndFeedback $ratingAndFeedback)
    {
        $ratingAndFeedback = RatingAndFeedback::with('product','fromUser','toUser')->find($ratingAndFeedback->id);
        return response()->json(prepareResult(false, $ratingAndFeedback, getLangByLabelGroups('messages','message_reason_for_action_list')), config('http_response.success'));
    }

    public function statusUpdate(Request $request,$id)
    {
        $ratingAndFeedback = RatingAndFeedback::find($id)->update(['is_feedback_approved'=>$request->is_feedback_approved]);
        $ratingAndFeedback = RatingAndFeedback::with('product','fromUser','toUser')->find($id);
        return response()->json(prepareResult(false, $ratingAndFeedback, getLangByLabelGroups('messages','message_reason_for_action_list')), config('http_response.success'));
    }

    public function multipleStatusUpdate(Request $request)
    {
        $ratingAndFeedback = RatingAndFeedback::whereIn('id',$request->rating_and_feedback_id)->update(['is_feedback_approved'=>$request->is_feedback_approved]);
        $ratingAndFeedback = RatingAndFeedback::whereIn('id',$request->rating_and_feedback_id)->with('product','fromUser','toUser')->get();
        return response()->json(prepareResult(false, $ratingAndFeedback, getLangByLabelGroups('messages','message_reason_for_action_list')), config('http_response.success'));
    }

    
    public function destroy(RatingAndFeedback $ratingAndFeedback)
    {
        $ratingAndFeedback->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_reason_for_action_deleted')), config('http_response.success'));
    }

    public function filter(Request $request)
    {
        try
        {
            $ratings = RatingAndFeedback::orderBy('rating_and_feedback.created_at','desc')
                    ->with('product','fromUser','toUser');

            if(!empty($request->avg_rating))
            {
                $ratings->where('rating_and_feedback.product_rating', $request->avg_rating)->orWhere('rating_and_feedback.user_rating', $request->avg_rating);
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
                $ratings = $ratings->join('users', function ($join) {
                            $join->on('rating_and_feedback.to_user', '=', 'users.id');
                        })
                ->where('users.user_type_id',$user_type_id);
            }
            if(!empty($request->type))
            {
                $ratings = $ratings->join('products_services_books', function ($join) {
                            $join->on('rating_and_feedback.products_services_book_id', '=', 'products_services_books.id');
                        })
                ->where('products_services_books.type',$request->type);
            }

            if(!empty($request->status))
            {
                $ratings->whereIn('rating_and_feedback.is_feedback_approved', $request->status);
            }

            if(!empty($request->per_page_record))
            {
                $ratingsData = $ratings->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $ratingsData = $ratings->get();
            }
            return response(prepareResult(false, $ratingsData, getLangByLabelGroups('messages','messages_job_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function import(Request $request)
    {
        $data = ['products_services_book_id' => $request->category_master_id, 'user_id' => $request->user_id];
        $import = Excel::import(new RatingAndFeedbacksImport($data),request()->file('file'));

        return response(prepareResult(false, [], getLangByLabelGroups('messages','messages_products_services_book_imported')), config('http_response.success'));
    }
}