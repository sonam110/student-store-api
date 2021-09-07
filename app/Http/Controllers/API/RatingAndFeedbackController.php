<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\RatingAndFeedback;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use App\Models\ProductsServicesBook;
use App\Models\User;
use App\Models\StudentDetail;
use App\Models\ServiceProviderDetail;

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
            if(!empty($request->product_id))
            {
                 $ratingAndFeedbacks = RatingAndFeedback::where('products_services_book_id',$request->product_id)->with('customer','orderItem.productsServicesBook.user');
            }
            elseif(!empty($request->user_id))
            {
                $ratingAndFeedbacks = RatingAndFeedback::where('to_user',$request->user_id)->with('customer','orderItem.productsServicesBook.user');
            }
            else
            {
                $ratingAndFeedbacks = RatingAndFeedback::with('customer','orderItem.productsServicesBook.user');
            }
            // $ratingAndFeedbacks = RatingAndFeedback::where('from_user',Auth::id())->with('orderItem.productsServicesBook.user')->get();
            if(!empty($request->per_page_record))
            {
                $ratingAndFeedbacks = $ratingAndFeedbacks->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $ratingAndFeedbacks = $ratingAndFeedbacks->get();
            }
            return response(prepareResult(false, $ratingAndFeedbacks, getLangByLabelGroups('messages','message_rating_and_feedback_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
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
            'order_item_id'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $orderItem = OrderItem::find($request->order_item_id);
            $ratingAndFeedback = new RatingAndFeedback;
            $ratingAndFeedback->from_user     	            = Auth::id();
            $ratingAndFeedback->to_user       				= $orderItem->productsServicesBook->user_id;
            $ratingAndFeedback->order_item_id               = $request->order_item_id;
            $ratingAndFeedback->products_services_book_id   = $orderItem->products_services_book_id;
            $ratingAndFeedback->product_feedback            = $request->product_feedback;
            $ratingAndFeedback->product_rating              = $request->product_rating;
            $ratingAndFeedback->user_feedback               = $request->user_feedback;
            $ratingAndFeedback->user_rating                 = $request->user_rating;
            $ratingAndFeedback->is_feedback_approved        = 0;
            $ratingAndFeedback->save();

            $orderItem->update(['is_rated'=>true]);

            $product = ProductsServicesBook::find($ratingAndFeedback->products_services_book_id);
            $productRating = (RatingAndFeedback::where('products_services_book_id',$ratingAndFeedback->products_services_book_id)->sum('product_rating'))/(RatingAndFeedback::where('products_services_book_id',$ratingAndFeedback->products_services_book_id)->count());
            $product->update(['avg_rating' => $productRating]);

            $user = User::find($ratingAndFeedback->to_user);
            $userRating = (RatingAndFeedback::where('to_user',$ratingAndFeedback->to_user)->sum('user_rating'))/(RatingAndFeedback::where('to_user',$ratingAndFeedback->to_user)->count());

            if($user->user_type_id == 2)
            {
                StudentDetail::where('user_id',$ratingAndFeedback->to_user)->update(['avg_rating' => $userRating]);
            }
            else
            {
                ServiceProviderDetail::where('user_id',$ratingAndFeedback->to_user)->update(['avg_rating' => $userRating]);
            }
            
            // Notification Start

            $productsServicesBook = ProductsServicesBook::find($ratingAndFeedback->products_services_book_id);

            $title = 'New Rating And Feedback';
            $body = 'Your order for product '.$productsServicesBook->title.' has been rated.';
            $type = 'Rating And Feedback';
            $user_type = 'seller';
            if($productsServicesBook->type == 'book')
            {
                $module = 'book';
            }
            else
            {
                $module = 'product_service';
            }
            pushNotification($title,$body,$user,$type,true,$user_type,$module,$request->order_item_id,'my-orders');

            // Notification End

            DB::commit();
            return response()->json(prepareResult(false, $ratingAndFeedback, getLangByLabelGroups('messages','message_rating_and_feedback_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

   

    public function show(RatingAndFeedback $ratingAndFeedback)
    {
        return response()->json(prepareResult(false, $ratingAndFeedback, getLangByLabelGroups('messages','message_rating_and_feedback_list')), config('http_response.success'));
    }

    

    public function destroy(RatingAndFeedback $ratingAndFeedback)
    {
        $ratingAndFeedback->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_rating_and_feedback_deleted')), config('http_response.success'));
    }

    public function approve($id)
    {
        $ratingAndFeedback = RatingAndFeedback::find($id);
        RatingAndFeedback::find($id)->update(['is_feedback_approved'=>true]);

        $ratingAndFeedback = RatingAndFeedback::find($id);
        return response()->json(prepareResult(false, $ratingAndFeedback, getLangByLabelGroups('messages','message_rating_and_feedback_approved')), config('http_response.success'));
    }
} 