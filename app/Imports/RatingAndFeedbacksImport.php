<?php

namespace App\Imports;

use App\Models\Label;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use DB;
use Hash;
use Auth;
use App\Models\RatingAndFeedback;
use Str;
use App\Models\Language;
use App\Models\ProductsServicesBook;
use App\Models\User;
use App\Models\StudentDetail;
use App\Models\ServiceProviderDetail;


class RatingAndFeedbacksImport implements ToModel,WithHeadingRow
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $product_id = $this->data['products_services_book_id'];
        $user_id = $this->data['user_id'];

        $brand = new RatingAndFeedback;
        $brand->to_user            	        = $user_id;
        $brand->products_services_book_id 	= $product_id;
        $brand->user_name              	    = $row['user_name'];
        $brand->product_rating              = $row['product_rating'] ? $row['product_rating'] : "";
        $brand->user_rating                 = $row['user_rating'] ? $row['user_rating'] : "";
        $brand->product_feedback            = $row['product_feedback'] ? $row['product_feedback'] : "";
        $brand->user_feedback               = $row['user_feedback'] ? $row['user_feedback'] : "";
        $brand->is_feedback_approved        = 1;
        $brand->save();


        $product = ProductsServicesBook::find($product_id);
        $productRating = (RatingAndFeedback::where('products_services_book_id',$product_id)->sum('product_rating'))/(RatingAndFeedback::where('products_services_book_id',$product_id)->count());
        $product->update(['avg_rating' => $productRating]);

        $user = User::find($user_id);
        $userRating = (RatingAndFeedback::where('to_user',$user_id)->sum('user_rating'))/(RatingAndFeedback::where('to_user',$user_id)->count());

        if($user->user_type_id == 2)
        {
            StudentDetail::where('user_id',$user_id)->update(['avg_rating' => $userRating]);
        }
        else
        {
            ServiceProviderDetail::where('user_id',$user_id)->update(['avg_rating' => $userRating]);
        }
        return;
    }
}
