<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductsServicesBook;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use App\Models\FavouriteProduct;
use App\Models\Notification;
use App\Models\ProductImage;
use App\Models\CategoryDetail;
use App\Models\ProductTag;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\UserPackageSubscription;
use App\Models\CartDetail;
use RecursiveArrayIterator, RecursiveIteratorIterator;
use App\Models\OrderItem;
use App\Models\ContactList;
use App\Models\Brand;

class ProductsServicesBookController extends Controller
{
    public function index(Request $request)
    {
        try
        {
            $productsServicesBooks = ProductsServicesBook::select('products_services_books.*')
                                        ->join('users', function ($join) {
                                            $join->on('products_services_books.user_id', '=', 'users.id');
                                        })
                                        ->orderBy('products_services_books.created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','addressDetail','categoryMaster','subCategory','coverImage','productTags');
            if($request->type)
            {
                $productsServicesBooks = $productsServicesBooks->where('type',$request->type);
            }
            if($request->user_type)
            {
                if($request->user_type == 'student')
                {
                    $user_type_id = 2;
                }
                if($request->user_type == 'company')
                {
                    $user_type_id = 3;
                }
                $productsServicesBooks = $productsServicesBooks->where('users.user_type_id',$user_type_id);
            }
            if($request->category_master_id)
            {
                $productsServicesBooks = $productsServicesBooks->where('category_master_id',$request->category_master_id);
            }

            if(!empty($request->per_page_record))
            {
                $productsServicesBooks = $productsServicesBooks->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $productsServicesBooks = $productsServicesBooks->get();
            }

            // if($productsServicesBooks->orderItems->count() > 0)
            // {
            //     $productsServicesBooks['is_deletable'] = false;
            // }
            // else
            // {
            //     $productsServicesBooks['is_deletable'] = true;
            // }
            
            return response(prepareResult(false, $productsServicesBooks, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'title'             => 'required',
            'price'             => 'required',
            'description'       => 'required'
        ]);

        if ($validation->fails()) {
            \Log::info($request->all());
            \Log::info($validation);
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }
        DB::beginTransaction();
        try
        {
            if(!empty($request->user_id))
            {
                $user_id = $request->user_id;
            }
            else
            {
                $user_id = Auth::id();
            }

            if(!empty($request->is_used_item))
            {
                $is_used_item = $request->is_used_item;
            }
            else
            {
                $is_used_item = '0';
            }


            $checkSlugExist = ProductsServicesBook::where('title', $request->title)->count();
            $productsServicesBook                               = new ProductsServicesBook;
            $productsServicesBook->user_id                      = $user_id;
            $productsServicesBook->address_detail_id            = $request->address_detail_id;
            $productsServicesBook->category_master_id           = $request->category_master_id;
            $productsServicesBook->sub_category_slug            = $request->sub_category_slug;
            $productsServicesBook->type                         = $request->type;
            $productsServicesBook->brand                        = $request->brand;
            $productsServicesBook->sku                          = $request->sku;
            $productsServicesBook->gtin_isbn                    = $request->gtin_isbn;
            $productsServicesBook->title                        = $request->title;
            $productsServicesBook->slug                         = ($checkSlugExist>0 ? Str::slug($request->title).'-'.($checkSlugExist+1) : Str::slug($request->title));
            $productsServicesBook->price                        = $request->price;
            $productsServicesBook->shipping_charge              = $request->shipping_charge;
            $productsServicesBook->discounted_price             = $request->discounted_price;
            $productsServicesBook->is_on_offer                  = $request->is_on_offer;
            $productsServicesBook->discount_type                = $request->discount_type;
            $productsServicesBook->discount_value               = $request->discount_value;
            $productsServicesBook->quantity                     = $request->quantity;
            $productsServicesBook->short_summary                = $request->short_summary;
            $productsServicesBook->description                  = $request->description;
            $productsServicesBook->attribute_details    = ($request->attribute_details=='[]') ? null : $request->attribute_details;
            $productsServicesBook->meta_description             = $request->meta_description;
            $productsServicesBook->sell_type                    = $request->sell_type;
            $productsServicesBook->deposit_amount               = $request->deposit_amount;
            $productsServicesBook->is_used_item                 = $is_used_item;
            $productsServicesBook->item_condition               = $request->item_condition;
            $productsServicesBook->author                       = $request->author;
            $productsServicesBook->published_year               = $request->published_year;
            $productsServicesBook->publisher                    = $request->publisher;
            $productsServicesBook->language                     = $request->language;
            $productsServicesBook->no_of_pages                  = $request->no_of_pages;
            $productsServicesBook->suitable_age                 = $request->suitable_age;
            $productsServicesBook->book_cover                   = $request->book_cover;
            $productsServicesBook->dimension_length             = $request->dimension_length;
            $productsServicesBook->dimension_width              = $request->dimension_width;
            $productsServicesBook->dimension_height             = $request->dimension_height;
            $productsServicesBook->weight                       = $request->weight;
            $productsServicesBook->service_type                 = $request->service_type;
            $productsServicesBook->delivery_type                = $request->delivery_type;
            $productsServicesBook->service_period_time          = $request->service_period_time;
            $productsServicesBook->service_period_time_type     = $request->service_period_time_type;
            $productsServicesBook->service_online_link          = $request->service_online_link;
            $productsServicesBook->service_languages            = json_encode($request->service_languages);
            $productsServicesBook->tags                         = json_encode($request->tags);
            $productsServicesBook->meta_title                   = $request->meta_title;
            $productsServicesBook->meta_keywords                = $request->meta_keywords;
            $productsServicesBook->is_promoted                  = $request->is_promoted;
            // $productsServicesBook->status                       = $request->status;
            if($request->is_promoted==1)
            {
                $productsServicesBook->promotion_start_at       = $request->promotion_start_at;
                $productsServicesBook->promotion_end_at         = $request->promotion_end_at;
            }
            $productsServicesBook->is_published                 = $request->is_published;
            $productsServicesBook->published_at                 = ($request->is_published==1) ? date('Y-m-d H:i:s') : null;

            $productsServicesBook->is_reward_point_applicable   = $request->is_reward_point_applicable;
            $productsServicesBook->reward_points                = $request->reward_points;
            DB::commit();
            if($productsServicesBook->save())
            {
                if(!empty($request->brand) && Brand::where('name',$productsServicesBook->brand)->count() <= 0)
                {
                    $brand = new Brand;
                    $brand->category_master_id  = $productsServicesBook->category_master_id;
                    $brand->user_id             = $user_id;
                    $brand->name                = $productsServicesBook->brand;
                    $brand->save();
                }

                if(!empty($request->images) && is_array($request->images))
                {
                    foreach ($request->images as $key => $image) {
                        $productImage = new ProductImage;
                        $productImage->products_services_book_id   = $productsServicesBook->id;
                        $productImage->image_path                  = $image['file_name'];
                        $productImage->thumb_image_path            = env('CDN_DOC_THUMB_URL').basename($image['file_name']);
                        $productImage->cover                       = $image['cover'];
                        $productImage->save();
                    }
                }

                if(!empty($request->tags) && is_array($request->tags))
                {
                    foreach ($request->tags as $key => $tag) {
                        $allTypeTag = new ProductTag;
                        $allTypeTag->products_services_book_id  = $productsServicesBook->id;
                        $allTypeTag->user_id                    = $user_id;
                        $allTypeTag->title                      = $tag;
                        $allTypeTag->type                       = $request->type;
                        $allTypeTag->save();
                    }
                }
            }

            
            $productsServicesBook = ProductsServicesBook::with('categoryMaster','subCategory','user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','addressDetail','productImages','productTags')->find($productsServicesBook->id);
            $productsServicesBook['category_detail'] = CategoryDetail::where('category_master_id',$productsServicesBook->category_master_id)->where('language_id',$productsServicesBook->language_id)->first();
            return response()->json(prepareResult(false, $productsServicesBook, getLangByLabelGroups('messages','messages_products_services_book_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            \Log::error($exception->getMessage());
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function show(Request $request,ProductsServicesBook $productsServicesBook)
    {
        $productsServicesBook = ProductsServicesBook::with('categoryMaster', 'subCategory','user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path,show_email,show_contact_number','user.serviceProviderDetail','addressDetail','productImages','productTags')->with(['ratings.customer' => function($query){
                $query->take(3);
            }])->withCount('ratings')->find($productsServicesBook->id);
        $productsServicesBook['category_detail'] = CategoryDetail::where('category_master_id',$productsServicesBook->category_master_id)->where('language_id',$productsServicesBook->language_id)->first();
        return response()->json(prepareResult(false, $productsServicesBook, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
    }

    public function update(Request $request, ProductsServicesBook $productsServicesBook)
    {
        $validation = Validator::make($request->all(), [
            'title'             => 'required',
            'price'             => 'required',
            'description'       => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        { 
        	if(!empty($request->user_id))
        	{
        	    $user_id = $request->user_id;
        	}
        	else
        	{
        	    $user_id = Auth::id();
        	}

        	if(!empty($request->is_used_item))
            {
                $is_used_item = $request->is_used_item;
            }
            else
            {
                $is_used_item = '0';
            }

            $productsServicesBook->address_detail_id        = $request->address_detail_id;
            $productsServicesBook->category_master_id       = $request->category_master_id;
            $productsServicesBook->sub_category_slug        = $request->sub_category_slug;
            $productsServicesBook->type                     = $request->type;
            $productsServicesBook->brand                    = $request->brand;
            $productsServicesBook->sku                      = $request->sku;
            $productsServicesBook->gtin_isbn                = $request->gtin_isbn;
            $productsServicesBook->title                    = $request->title;
            $productsServicesBook->price                    = $request->price;
            $productsServicesBook->shipping_charge          = $request->shipping_charge;
            $productsServicesBook->discounted_price         = $request->discounted_price;
            $productsServicesBook->is_on_offer              = $request->is_on_offer;
            $productsServicesBook->discount_type            = $request->discount_type;
            $productsServicesBook->discount_value           = $request->discount_value;
            $productsServicesBook->quantity                 = $request->quantity;
            $productsServicesBook->short_summary            = $request->short_summary;
            $productsServicesBook->description              = $request->description;
            $productsServicesBook->attribute_details        = $request->attribute_details;
            $productsServicesBook->meta_description         = $request->meta_description;
            $productsServicesBook->sell_type                = $request->sell_type;
            $productsServicesBook->deposit_amount           = $request->deposit_amount;
            $productsServicesBook->is_used_item             = $is_used_item;
            $productsServicesBook->item_condition           = $request->item_condition;
            $productsServicesBook->author                   = $request->author;
            $productsServicesBook->published_year           = $request->published_year;
            $productsServicesBook->publisher                = $request->publisher;
            $productsServicesBook->language                 = $request->language;
            $productsServicesBook->no_of_pages              = $request->no_of_pages;
            $productsServicesBook->suitable_age             = $request->suitable_age;
            $productsServicesBook->book_cover               = $request->book_cover;
            $productsServicesBook->dimension_length         = $request->dimension_length;
            $productsServicesBook->dimension_width          = $request->dimension_width;
            $productsServicesBook->dimension_height         = $request->dimension_height;
            $productsServicesBook->weight                   = $request->weight;
            $productsServicesBook->service_type             = $request->service_type;
            $productsServicesBook->delivery_type            = $request->delivery_type;
            $productsServicesBook->service_period_time      = $request->service_period_time;
            $productsServicesBook->service_period_time_type = $request->service_period_time_type;
            $productsServicesBook->service_online_link      = $request->service_online_link;
            $productsServicesBook->service_languages        = json_encode($request->service_languages);
            $productsServicesBook->tags                     = json_encode($request->tags);
            $productsServicesBook->meta_title               = $request->meta_title;
            $productsServicesBook->meta_keywords            = $request->meta_keywords;
            $productsServicesBook->is_promoted              = $request->is_promoted;
            if($request->is_promoted==1)
            {
                $productsServicesBook->promotion_start_at   = $request->promotion_start_at;
                $productsServicesBook->promotion_end_at     = $request->promotion_end_at;
            }
            $productsServicesBook->is_published             = $request->is_published;
            $productsServicesBook->published_at             = ($request->is_published==1) ? date('Y-m-d H:i:s') : null;
            if($productsServicesBook->save())
            {
                if(!empty($request->brand) && Brand::where('name',$productsServicesBook->brand)->count() <= 0)
                {
                    $brand = new Brand;
                    $brand->category_master_id  = $productsServicesBook->category_master_id;
                    $brand->user_id             = $user_id;
                    $brand->name                = $productsServicesBook->brand;
                    $brand->save();
                }

                if(!empty($request->images) && is_array($request->images))
                {
                	ProductImage::where('products_services_book_id',$productsServicesBook->id)->delete();
                    foreach ($request->images as $key => $image) {
                        $productImage = new ProductImage;
                        $productImage->products_services_book_id   = $productsServicesBook->id;
                        $productImage->image_path                  = $image['file_name'];
                        $productImage->thumb_image_path            = env('CDN_DOC_THUMB_URL').basename($image['file_name']);
                        $productImage->cover                       = $image['cover'];
                        $productImage->save();
                    }
                }

                ProductTag::where('products_services_book_id',$productsServicesBook->id)->delete();
                foreach ($request->tags as $key => $tag) {
                    $allTypeTag 								= new ProductTag;
                    $allTypeTag->products_services_book_id      = $productsServicesBook->id;
                    $allTypeTag->user_id                        = $user_id;
                    $allTypeTag->title                          = $tag;
                    $allTypeTag->type                           = $request->type;
                    $allTypeTag->save();
                }
            }

            DB::commit();
            $productsServicesBook = ProductsServicesBook::with('categoryMaster', 'subCategory','user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','addressDetail','productImages','productTags')->find($productsServicesBook->id);
            $productsServicesBook['category_detail'] = CategoryDetail::where('category_master_id',$productsServicesBook->category_master_id)->where('language_id',$productsServicesBook->language_id)->first();
            return response()->json(prepareResult(false, $productsServicesBook, getLangByLabelGroups('messages','messages_products_services_book_updated')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function action($productsServicesBook_id, Request $request)
    {
        if($request->action=='update-status')
        {
            $validation = Validator::make($request->all(), [
                'status'    => 'required'
            ]);
        }
        if($request->action=='publish') 
        {
            $validation = Validator::make($request->all(), [
                'is_published'    => 'required|boolean'
            ]);
            
        }

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $getProductsServicesBook = ProductsServicesBook::find($productsServicesBook_id);
            if(!$getProductsServicesBook)
            {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_doesnt_exist')), config('http_response.internal_server_error'));
            }

            //For Update Product status
            if($request->action=='update-status')
            {
                $getProductsServicesBook->status = $request->status;
                $title = 'Product Status Updated';
                $body =  'Product '.$getProductsServicesBook->title.' status has been successfully updated.';
            }

            if($request->action=='publish') 
            {
                $getProductsServicesBook->is_published = $request->is_published;
                if($request->is_published == true)
                {
                    $getProductsServicesBook->published_at = date('Y-m-d');
                }
                $title = 'Product Published';
                $body =  'Product '.$getProductsServicesBook->title.'has been successfully Published.';
            }

            $getProductsServicesBook->save();

            $type = 'Product Action';
            if($getProductsServicesBook->type == 'book')
	        {
	            $module = 'book';
	        }
	        else
	        {
	            $module = 'product_service';
	        }
            pushNotification($title,$body,$getProductsServicesBook->user,$type,true,'creator',$module,$getProductsServicesBook->id,'my-listing');

            DB::commit();
            return response()->json(prepareResult(false, $getProductsServicesBook, getLangByLabelGroups('messages','messages_products_services_book_updated')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function multipleStatusUpdate(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'status'    => 'required',
            'products_services_book_id'    => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $productsServicesBooks = ProductsServicesBook::whereIn('id',$request->products_services_book_id)->get();
            foreach ($productsServicesBooks as $key => $productsServicesBook) {
                $productsServicesBook->status = $request->status;
                
                $title = 'Product Status Updated';
                $body =  'Product '.$productsServicesBook->title.' status has been successfully updated.';
                $productsServicesBook->save();

                $type = 'Product Action';
                if($productsServicesBook->type == 'book')
                {
                    $module = 'book';
                }
                else
                {
                    $module = 'product_service';
                }
                pushNotification($title,$body,$productsServicesBook->user,$type,true,'creator',$module,$productsServicesBook->id,'my-listing');
            }

            DB::commit();
            return response()->json(prepareResult(false, $productsServicesBooks, getLangByLabelGroups('messages','messages_products_services_book_updated')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function multiplePublishUpdate(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'is_published'    => 'required|boolean',
            'products_services_book_id'    => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $productsServicesBooks = ProductsServicesBook::whereIn('id',$request->products_services_book_id)->get();
            foreach ($productsServicesBooks as $key => $productsServicesBook) {
                $productsServicesBook->is_published = $request->is_published;
                if($request->is_published == true)
                {
                    $productsServicesBook->published_at = date('Y-m-d');
                }
                
                $title = 'Product Published';
                $body =  'Product '.$productsServicesBook->title.'has been successfully Published.';
                $productsServicesBook->save();

                $type = 'Product Action';
                if($productsServicesBook->type == 'book')
                {
                    $module = 'book';
                }
                else
                {
                    $module = 'product_service';
                }
                pushNotification($title,$body,$productsServicesBook->user,$type,true,'creator',$module,$productsServicesBook->id,'my-listing');
            }

            DB::commit();
            return response()->json(prepareResult(false, $productsServicesBooks, getLangByLabelGroups('messages','messages_products_services_book_updated')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }


    public function filter(Request $request)
    {
        try
        { 
            // return $request->status;
            $type = 'product';
            if(!empty($request->type))
            {
                $type = $request->type;
            }
            $searchType = $request->searchType; 
            $products = ProductsServicesBook::select('products_services_books.id','products_services_books.user_id', 'products_services_books.category_master_id', 'products_services_books.address_detail_id', 'products_services_books.title', 'products_services_books.slug', 'products_services_books.short_summary', 'products_services_books.type', 'products_services_books.price', 'products_services_books.is_on_offer', 'products_services_books.discount_type', 'products_services_books.discount_value','products_services_books.sell_type', 'products_services_books.service_online_link', 'products_services_books.service_type','products_services_books.service_period_time','products_services_books.service_period_time_type','products_services_books.service_languages', 'products_services_books.delivery_type', 'products_services_books.avg_rating', 'products_services_books.status','discounted_price','deposit_amount','products_services_books.is_used_item','products_services_books.sub_category_slug','products_services_books.is_published','products_services_books.brand')
            ->where('products_services_books.type', $type)
            // ->where('products_services_books.status', $request->status)
            ->with('user:id,user_type_id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
            ->orderBy('products_services_books.created_at','DESC');

            if($request->user_type == 'student')
            {
                $products->join('users', function ($join) {
                    $join->on('products_services_books.user_id', '=', 'users.id')
                    ->where('user_type_id', 2);
                });
            }
            elseif($request->user_type == 'company')
            {
                $products->join('users', function ($join) {
                    $join->on('products_services_books.user_id', '=', 'users.id')
                    ->where('user_type_id', 3);
                });
            }

            if($request->is_published == "1")
            {
                $products->where('products_services_books.is_published', 1);
            }
            elseif($request->is_published == "0")
            {
                $products->where('products_services_books.is_published', 0);
            }

            if($searchType=='filter')
            {
                if($request->status == "0" || !empty($request->status))
                {
                    $products->where('products_services_books.status', $request->status);
                }
                if(!empty($request->title))
                {
                    $products->where('products_services_books.title', 'LIKE', '%'.$request->title.'%');
                }
                if(!empty($request->avg_rating))
                {
                    $products->where('products_services_books.avg_rating', '>=', $request->avg_rating);
                }
                if(!empty($request->brand))
                {
                    $products->where('products_services_books.brand', $request->brand);
                }
                if(!empty($request->category_master_id))
                {
                    $products->where('products_services_books.category_master_id', $request->category_master_id);
                }
                if(!empty($request->sub_category_slug))
                {
                    $products->where('products_services_books.sub_category_slug', $request->sub_category_slug);
                }
                
                // if(!empty($request->min_price))
                // {
                //     $products->where('products_services_books.discounted_price', '>=', $request->min_price);
                // }
                // if(!empty($request->max_price))
                // {
                //     $products->where('products_services_books.discounted_price', '<=', $request->max_price);
                // }

                if(!empty($request->price_min))
                {
                    $products->where('products_services_books.price', '>=', $request->price_min);
                }
                if(!empty($request->price_max))
                {
                    $products->where('products_services_books.price', '<=', $request->price_max);
                }

                if(!empty($request->min_price))
                {
                    $products->where('products_services_books.price', '>=', $request->min_price);
                }
                if(!empty($request->max_price))
                {
                    $products->where('products_services_books.price', '<=', $request->max_price);
                }

                if(!empty($request->sell_type))
                {
                    $products->where('products_services_books.sell_type', $request->sell_type);
                }
                if(!empty($request->city))
                {
                    $products->join('address_details', function ($join) use ($request) {
                        $join->on('products_services_books.address_detail_id', '=', 'address_details.id')
                        ->whereIn('city', $request->city);
                    });
                }
                if(!empty($request->author))
                {
                    $products->where('products_services_books.author', $request->author);
                }
                if(!empty($request->language))
                {
                    $products->where('products_services_books.language', $request->language);
                }
                if(!empty($request->published_year))
                {
                    $products->where('products_services_books.published_year', $request->published_year);
                }
                if(!empty($request->publisher))
                {
                    $products->where('products_services_books.publisher', $request->publisher);
                }
                if(!empty($request->service_languages))
                {
                    $products->where(function($query) use ($request) {
                        foreach ($request->service_languages as $key => $service_language) {
                            if ($key === 0) {
                                $query->where('products_services_books.service_languages', 'LIKE', '%'.$service_language.'%');
                                continue;
                            }
                            $query->orWhere('products_services_books.service_languages', 'LIKE', '%'.$service_language.'%');
                        }
                    });
                }
                if(!empty($request->service_period_time))
                {
                    $products->where('products_services_books.service_period_time', $request->service_period_time);
                }
                if(!empty($request->service_period_time_type))
                {
                    $products->where('products_services_books.service_period_time_type', $request->service_period_time_type);
                }
                if(!empty($request->service_type))
                {
                    $products->where('products_services_books.service_type', $request->service_type);
                }
                if(!empty($request->suitable_age))
                {
                    $products->where('products_services_books.suitable_age', $request->suitable_age);
                }
                if(!empty($request->attributes_data))
                {
                    $attr = $request->attributes_data;
                    $newAttribute = new RecursiveIteratorIterator(new RecursiveArrayIterator($request->all()), RecursiveIteratorIterator::SELF_FIRST);
                    $result = [];
                    foreach ($newAttribute as $key => $value) {
                        if ($key === 'bucket_group_attributes' && $key) {
                            $result = array_merge($result, $value);
                        }
                    }
                    $arrForSelecteds = [];
                    foreach ($result as $key => $value) {
                        if($value['selected'])
                        {
                            $arrForSelecteds[] = $value;
                        }  
                    }

                    $products->where(function($query) use ($arrForSelecteds) {
                        foreach ($arrForSelecteds as $key => $arrForSelecte) {
                            if ($key === 0) {
                                $query->where('products_services_books.attribute_details', 'LIKE', '%'.json_encode($arrForSelecte).'%');
                                continue;
                            }
                            $query->orWhere('products_services_books.attribute_details', 'LIKE', '%'.json_encode($arrForSelecte).'%');
                        }
                    });
                }

                if(!empty($request->delivery_type))
                {
                    $products->where('products_services_books.delivery_type', $request->delivery_type);
                }

                if(!empty($request->user_id))
                {
                    $products->where('products_services_books.user_id', $request->user_id);
                }
                if(!empty($request->gtin_isbn))
                {
                    $products->where('gtin_isbn','like', '%'.$request->gtin_isbn.'%');
                }
            }
            elseif($searchType=='promotion')
            {
                $products->where('products_services_books.is_promoted', '1')
                ->where('products_services_books.promotion_start_at','<=', date('Y-m-d'))
                ->where('products_services_books.promotion_end_at','>=', date('Y-m-d'));
            }
            elseif($searchType=='latest')
            {
                $products->orderBy('products_services_books.created_at','DESC');
            }
            elseif($searchType=='bestSelling')
            {
                $products->where('products_services_books.top_selling', '1')
                ->where('products_services_books.top_selling_start_at','<=', date('Y-m-d'))
                ->where('products_services_books.top_selling_end_at','>=', date('Y-m-d'));
                if($products->count() <= 0)
                {
                     $products = ProductsServicesBook::select('products_services_books.id','products_services_books.user_id', 'products_services_books.category_master_id', 'products_services_books.address_detail_id', 'products_services_books.title', 'products_services_books.slug', 'products_services_books.short_summary', 'products_services_books.type', 'products_services_books.price', 'products_services_books.is_on_offer', 'products_services_books.discount_type', 'products_services_books.discount_value','products_services_books.sell_type', 'products_services_books.service_online_link', 'products_services_books.service_type','products_services_books.service_period_time','products_services_books.service_period_time_type','products_services_books.service_languages', 'products_services_books.delivery_type', 'products_services_books.avg_rating', 'products_services_books.status','discounted_price','deposit_amount','products_services_books.is_used_item','products_services_books.sub_category_slug','products_services_books.is_published','products_services_books.brand')
                                ->where('products_services_books.user_id', '!=', Auth::id())
                                ->where('products_services_books.status', '2')
                                ->where('products_services_books.is_used_item', $is_used_item)
                                ->where('products_services_books.type', $type)
                                ->where('products_services_books.is_published', '1')
                                ->withCount('orderItems')->orderBy('order_items_count','desc')
                                ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite'); 
                }
            }
            elseif($searchType=='topRated')
            {
                $products->orderBy('avg_rating','DESC');
            }
            elseif($searchType=='random')
            {
                $products->inRandomOrder();
            }
            if(!empty($request->per_page_record))
            {
                $productsData = $products->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $productsData = $products->get();
            }
            if($request->other_function=='yes')
            {
                return $productsData;
            }
            // $productsData['total_records'] = $products->count();
                //dd(DB::getQueryLog());
            return response(prepareResult(false, $productsData, getLangByLabelGroups('messages','messages_users_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function destroy(ProductsServicesBook $productsServicesBook)
    {
        if($productsServicesBook->orderItems->count() > 0)
        {
            return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','messages_products_order_exists')), config('http_response.success'));
        }
        $productsServicesBook->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_products_services_book_deleted')), config('http_response.success'));
    }

    public function productsImport(Request $request) 
    {
        $data = ['address_detail_id'=>$request->address_detail_id, 'category_master_id'=>$request->category_master_id, 'sub_category_slug'=>$request->sub_category_slug];
        $import = Excel::import(new ProductsImport($data),request()->file('file'));

        return response(prepareResult(false, [], getLangByLabelGroups('messages','messages_products_services_book_imported')), config('http_response.success'));
    }
}
