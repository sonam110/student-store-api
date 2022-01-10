<?php

namespace App\Http\Controllers\API\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\FavouriteProduct;
use App\Models\Language;
use Auth;
use DB;

class FavouriteProductController extends Controller
{
    function __construct()
    {
        $this->lang_id = Language::first()->id;
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

            $query = FavouriteProduct::where('user_id', Auth::id())->orderBy('created_at','DESC')
                    ->with('product.user','product.user.serviceProviderDetail','product.categoryMaster','product.subCategory','product.addressDetail','product.coverImage','product.productTags')
                ->with(['product.categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '1');
                }])
                ->with(['product.subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '0');
                }]);
                    
            // if(Auth::user()->user_type_id=='2')
            // {
            //     $query = FavouriteProduct::where('user_id', Auth::id())
            //             ->with('product.user','product.user.serviceProviderDetail','product.categoryMaster','product.subCategory','product.addressDetail','product.coverImage','product.productTags');
            // } else {
            //     $query = FavouriteProduct::select('favourite_products.*')
            //             ->join('products_services_books', function ($join) {
            //                 $join->on('favourite_products.products_services_book_id', '=', 'products_services_books.id');
            //             })
            //             ->where('products_services_books.user_id', Auth::id())
            //             ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.studentDetail');
            // }

            if(!empty($request->per_page_record))
            {
                $favouriteProducts = $query->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $favouriteProducts = $query->get();
            }
            return response(prepareResult(false, $favouriteProducts, getLangByLabelGroups('messages','messages_favourite_product_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $favouriteProducts = new FavouriteProduct;
            $validation = Validator::make($request->all(), [
                                'products_services_book_id'       => 'required'
                            ]);

            if ($validation->fails()) {
                return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
            }

            if(FavouriteProduct::where('products_services_book_id', $request->products_services_book_id)->where('user_id', Auth::id())->count()>0)
            {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_already_exist')), config('http_response.internal_server_error'));
            }

            $favouriteProducts->products_services_book_id  = $request->products_services_book_id;
            $favouriteProducts->user_id   = Auth::id();
            $favouriteProducts->save();
            DB::commit();
            return response()->json(prepareResult(false, $favouriteProducts, getLangByLabelGroups('messages','messages_favourite_product_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function destroy(FavouriteProduct $favouriteProduct)
    {  
        $favouriteProduct->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_favourite_product_deleted')), config('http_response.success'));
    }
}
