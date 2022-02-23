<?php

namespace App\Http\Controllers\API\Products;

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
use App\Models\Abuse;
use App\Models\Brand;
use App\Models\User;
use App\Models\Language;
use App\Models\CategoryMaster;

class ProductsServicesBookController extends Controller
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

            $productsServicesBooks = ProductsServicesBook::where('is_published', '1')->where('status', '2')->orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
            ->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
            ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }]);
            if($request->type)
            {
                $productsServicesBooks = $productsServicesBooks->where('type',$request->type);
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
            return response(prepareResult(false, $productsServicesBooks, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function allProductsByUser(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            if($request->user_id)
            {
                $products = ProductsServicesBook::where('user_id', $request->user_id)
                    ->where('type','product')
                    ->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
                    ->orderBy('created_at','DESC')
                    ->with('user.serviceProviderDetail','categoryMaster','subCategory','addressDetail','coverImage','productTags')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
            }
            else
            {
                $products = ProductsServicesBook::where('user_id', Auth::id())
                    ->where('type','product')
                    ->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
                    ->orderBy('created_at','DESC')
                    ->with('categoryMaster','subCategory','addressDetail','coverImage','productTags')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
            }
            if($request->type=='c')
            {
                $products = $products->where('status', '2')
                ->where('is_published', '1')
                ->where('quantity', '>', 0);
            }

            if(!empty($request->per_page_record))
            {
                $products = $products->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $products = $products->get();
            }
            return response(prepareResult(false, $products, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function allServicesByUser(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            if($request->user_id)
            {
                $products = ProductsServicesBook::where('user_id', $request->user_id)
                    ->where('type','service')
                    ->orderBy('created_at','DESC')
                    ->with('user.serviceProviderDetail','categoryMaster','subCategory','addressDetail','coverImage','productTags')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
            }
            else
            {
                $products = ProductsServicesBook::where('user_id', Auth::id())
                    ->where('type','service')
                    ->orderBy('created_at','DESC')
                    ->with('categoryMaster','subCategory','addressDetail','coverImage','productTags')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
            }
            if($request->type=='c')
            {
                $products = $products->where('status', '2')
                ->where('is_published', '1')
                ->where('quantity', '>', 0);
            }
            if(!empty($request->per_page_record))
            {
                $products = $products->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $products = $products->get();
            }
            return response(prepareResult(false, $products, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function allBooksByUser(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            if($request->user_id)
            {
                $products = ProductsServicesBook::where('user_id', $request->user_id)
                    ->where('type','book')
                    ->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
                    ->orderBy('created_at','DESC')
                    ->with('user.serviceProviderDetail','categoryMaster','subCategory','addressDetail','coverImage','productTags')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
            }
            else
            {
                $products = ProductsServicesBook::where('user_id', Auth::id())
                    ->where('type','book')
                    ->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
                    ->orderBy('created_at','DESC')
                    ->with('categoryMaster','subCategory','addressDetail','coverImage','productTags')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
            }
            if($request->type=='c')
            {
                $products = $products->where('status', '2')
                ->where('is_published', '1')
                ->where('quantity', '>', 0);
            }
            if(!empty($request->per_page_record))
            {
                $products = $products->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $products = $products->get();
            }
            return response(prepareResult(false, $products, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            // 'address_detail_id' => 'required',
            'title'             => 'required',
            // 'price'             => 'required',
            //'short_summary'     => 'required',
            // 'quantity'          => 'required',
            'description'       => 'required',
            'published_year' => 'nullable|digits:4|integer|min:1500|max:'.(date('Y')),
        ]);

        if ($validation->fails()) {
            \Log::info($request->all());
            \Log::info($validation->messages());
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }
        DB::beginTransaction();
        try
        {
            if(!empty($request->is_used_item))
            {
                $is_used_item = $request->is_used_item;
            }
            else
            {
                $is_used_item = '0';
            }

            $type = $request->type;
            $user_package = UserPackageSubscription::where('user_id',Auth::id())->where('module',$type)->where('subscription_status', 1)->orderBy('created_at','desc')->first();
            if(empty($user_package))
            {
                return response()->json(prepareResult(true, ['No Package Subscribed'], getLangByLabelGroups('messages','message_no_package_subscribed_error')), config('http_response.internal_server_error'));
            }
            elseif($type== 'product' && $user_package->number_of_product == $user_package->used_number_of_product)
            {
                return response()->json(prepareResult(true, ['Package Use Exhausted'], getLangByLabelGroups('messages','message_job_ads_exhausted_error')), config('http_response.internal_server_error'));
            }
            elseif($type== 'servce' && $user_package->number_of_servce == $user_package->used_number_of_servce)
            {
                return response()->json(prepareResult(true, ['Package Use Exhausted'], getLangByLabelGroups('messages','message_job_ads_exhausted_error')), config('http_response.internal_server_error'));
            }
            elseif($type== 'book' && $user_package->number_of_book == $user_package->used_number_of_book)
            {
                return response()->json(prepareResult(true, ['Package Use Exhausted'], getLangByLabelGroups('messages','message_job_ads_exhausted_error')), config('http_response.internal_server_error'));
            }
            else
            {

                $amount = $request->basic_price_wo_vat;
                $is_on_offer = $request->is_on_offer;
                $discount_type = $request->discount_type;
                $discount_value = $request->discount_value;
                $vat_percentage = 0;
                $catVatId = CategoryMaster::select('vat')->find($request->category_master_id);
                if($catVatId)
                {
                    $vat_percentage = $catVatId->vat;
                }
                $user_id = Auth::id();
                
                $getCommVal = updateCommissions($amount, $is_on_offer, $discount_type, $discount_value, $vat_percentage, $user_id, $request->type);

                $checkSlugExist = ProductsServicesBook::where('title', $request->title)->count();
                $productsServicesBook                               = new ProductsServicesBook;
                $productsServicesBook->user_id                      = Auth::id();
                $productsServicesBook->address_detail_id            = $request->address_detail_id;
                $productsServicesBook->category_master_id           = $request->category_master_id;
                $productsServicesBook->sub_category_slug            = $request->sub_category_slug;
                $productsServicesBook->type                         = $request->type;
                $productsServicesBook->brand                        = $request->brand;
                $productsServicesBook->sku                          = $request->sku;
                $productsServicesBook->gtin_isbn                    = $request->gtin_isbn;
                $productsServicesBook->title                        = $request->title;
                $productsServicesBook->slug                         = ($checkSlugExist>0 ? Str::slug($request->title).'-'.($checkSlugExist+1) : Str::slug($request->title));
                $productsServicesBook->basic_price_wo_vat           = $request->basic_price_wo_vat;
                $productsServicesBook->price = $getCommVal['price_with_all_com_vat'];
                $productsServicesBook->shipping_charge              = $request->shipping_charge;
                $productsServicesBook->discounted_price             = $getCommVal['totalAmount'];

                $productsServicesBook->vat_percentage = $vat_percentage;
                $productsServicesBook->vat_amount = $getCommVal['vat_amount'];
                $productsServicesBook->ss_commission_percent = $getCommVal['ss_commission_percent'];
                $productsServicesBook->ss_commission_amount = $getCommVal['ss_commission_amount'];
                $productsServicesBook->cc_commission_percent_all = $getCommVal['totalCCPercent'];
                $productsServicesBook->cc_commission_amount_all = $getCommVal['totalCCAmount'];

                $productsServicesBook->is_on_offer                  = $request->is_on_offer;
                $productsServicesBook->discount_type                = $request->discount_type;
                $productsServicesBook->discount_value               = $request->discount_value;
                if($type == 'service') {
                    $qty = 1000;
                } else {
                    $qty = $request->quantity;
                }
                $productsServicesBook->quantity                     = $qty;
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
                $productsServicesBook->language                     = (!empty($request->language)) ? $request->language : null;
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
                $productsServicesBook->service_languages        = (!empty($request->service_languages)) ? json_encode($request->service_languages, JSON_UNESCAPED_UNICODE) : null;
                $productsServicesBook->tags                     = (!empty($request->tags)) ? json_encode($request->tags, JSON_UNESCAPED_UNICODE) : null;
                // $productsServicesBook->status                       = '2';//should be commented
                $productsServicesBook->meta_title                   = $request->meta_title;
                $productsServicesBook->meta_keywords                = $request->meta_keywords;
                $productsServicesBook->is_promoted                  = $request->promote;
                if($request->is_promoted==1)
                {
                    $productsServicesBook->promotion_start_at       = $request->promotion_start_at;
                    $productsServicesBook->promotion_end_at         = $request->promotion_end_at;
                }
                $productsServicesBook->is_published                 = $request->is_published;
                $productsServicesBook->published_at                 = ($request->is_published==1) ? date('Y-m-d H:i:s') : null;

                $productsServicesBook->is_reward_point_applicable   = $request->is_reward_point_applicable;
                $productsServicesBook->reward_points                = $request->reward_points;
                
                if($productsServicesBook->save())
                {
                    if($type == 'product')
                    {
                        $user_package->update(['used_number_of_product'=>($user_package->used_number_of_product + 1)]);
                    }
                    elseif($type == 'service')
                    {
                        $user_package->update(['used_number_of_service'=>($user_package->used_number_of_service + 1)]);
                    }
                    else
                    {
                        $user_package->update(['used_number_of_book'=>($user_package->used_number_of_book + 1)]);
                    }
                    
                    if(!empty($request->brand) && Brand::where('name',$productsServicesBook->brand)->count() <= 0)
                    {
                        $brand = new Brand;
                        $brand->category_master_id  = $productsServicesBook->category_master_id;
                        $brand->user_id             = Auth::id();
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
                            $allTypeTag->user_id                    = Auth::id();
                            $allTypeTag->title                      = $tag;
                            $allTypeTag->type                       = $request->type;
                            $allTypeTag->save();
                        }
                    }
                }
            }

            DB::commit();
            $productsServicesBook = ProductsServicesBook::with('categoryMaster','subCategory','user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','addressDetail','productImages','productTags')->find($productsServicesBook->id);
            $productsServicesBook['category_detail'] = CategoryDetail::where('category_master_id',$productsServicesBook->category_master_id)->where('language_id',$productsServicesBook->language_id)->first();
            return response()->json(prepareResult(false, $productsServicesBook, getLangByLabelGroups('messages','messages_products_services_book_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            \Log::error($exception->getMessage());
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }

    public function show(Request $request,ProductsServicesBook $productsServicesBook)
    {
        if(auth()->id()!=$productsServicesBook->user_id)
        {
            if($productsServicesBook->status!=2 || $productsServicesBook->is_published!=1)
            {
                return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('not_found','page_not_found')), config('http_response.not_found'));
            }
        }
        
        $lang_id = $this->lang_id;
        if(empty($lang_id))
        {
            $lang_id = Language::select('id')->first()->id;
        }

    	if($request->view_count == true)
    	{
    		$productsServicesBook->update(['view_count' => $productsServicesBook->view_count + 1]);
    	}
        if($fav = FavouriteProduct::where('products_services_book_id',$productsServicesBook->id)->where('user_id',Auth::id())->first())
        {
            $favouriteProductsServicesBook = true;
            $favouriteId = $fav->id;
        }
        else
        {
            $favouriteProductsServicesBook = false;
            $favouriteId = null;
        }

        if($cart = CartDetail::where('products_services_book_id',$productsServicesBook->id)->where('user_id',Auth::id())->first())
        {
            $in_cart = true;
            $cartId = $cart->id;
        }
        else
        {
            $in_cart = false;
            $cartId = null;
        }
        if($message = ContactList::where('products_services_book_id',$productsServicesBook->id)->where('buyer_id',Auth::id())->first())
        {
            $is_chat_initiated = true;
            $contactListId = $message->id;
        }
        else
        {
            $is_chat_initiated = false;
            $contactListId = null;
        }

        if($abuse = Abuse::where('products_services_book_id',$productsServicesBook->id)->where('user_id',Auth::id())->first())
        {
            $is_abuse_reported = true;
        }
        else
        {
            $is_abuse_reported = false;
        }
        $productsServicesBook = ProductsServicesBook::with('categoryMaster', 'subCategory','user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path,show_email,show_contact_number','user.serviceProviderDetail','user.shippingConditions','addressDetail','productImages','productTags')->with(['ratings.customer' => function($query){
                $query->take(3);
            }])
            ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
            ->withCount('ratings')->find($productsServicesBook->id);
        $productsServicesBook['favourite_products_services_book'] = $favouriteProductsServicesBook;
        $productsServicesBook['favourite_id'] = $favouriteId;
        $productsServicesBook['in_cart'] = $in_cart;
        $productsServicesBook['cart_id'] = $cartId;
        $productsServicesBook['is_chat_initiated'] = $is_chat_initiated;
        $productsServicesBook['contact_list_id'] = $contactListId;
        $productsServicesBook['is_abuse_reported'] = $is_abuse_reported;
        $productsServicesBook['language_id'] = $productsServicesBook->language_id;
        $productsServicesBook['category_detail'] = CategoryDetail::where('category_master_id',$productsServicesBook->category_master_id)->where('language_id',$productsServicesBook->language_id)->first();
        return response()->json(prepareResult(false, $productsServicesBook, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
    }

    public function detail(Request $request,$id)
    {
        $lang_id = $this->lang_id;
        if(empty($lang_id))
        {
            $lang_id = Language::select('id')->first()->id;
        }

        $productsServicesBook = ProductsServicesBook::find($id);
        if($request->view_count == true)
        {
            $productsServicesBook->update(['view_count' => $productsServicesBook->view_count + 1]);
        }
        if($fav = FavouriteProduct::where('products_services_book_id',$productsServicesBook->id)->where('user_id',Auth::id())->first())
        {
            $favouriteProductsServicesBook = true;
            $favouriteId = $fav->id;
        }
        else
        {
            $favouriteProductsServicesBook = false;
            $favouriteId = null;
        }

        if($cart = CartDetail::where('products_services_book_id',$productsServicesBook->id)->where('user_id',Auth::id())->first())
        {
            $in_cart = true;
            $cartId = $cart->id;
        }
        else
        {
            $in_cart = false;
            $cartId = null;
        }
        if($message = ContactList::where('products_services_book_id',$productsServicesBook->id)->where('buyer_id',Auth::id())->first())
        {
            $is_chat_initiated = true;
            $contactListId = $message->id;
        }
        else
        {
            $is_chat_initiated = false;
            $contactListId = null;
        }
        $productsServicesBook = ProductsServicesBook::with('categoryMaster', 'subCategory','user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','productImages','productTags')
        ->with(['ratings.customer' => function($query){
                $query->take(3);
            }])
        ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
        ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '0');
        }])
        ->withCount('ratings')
        ->find($id);
        $productsServicesBook['favourite_products_services_book'] = $favouriteProductsServicesBook;
        $productsServicesBook['favourite_id'] = $favouriteId;
        $productsServicesBook['in_cart'] = $in_cart;
        $productsServicesBook['cart_id'] = $cartId;
        $productsServicesBook['is_chat_initiated'] = $is_chat_initiated;
        $productsServicesBook['contact_list_id'] = $contactListId;
        $productsServicesBook['category_detail'] = CategoryDetail::where('category_master_id',$productsServicesBook->category_master_id)->where('language_id',$productsServicesBook->language_id)->first();
        return response()->json(prepareResult(false, $productsServicesBook, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
    }

    public function update(Request $request, ProductsServicesBook $productsServicesBook)
    {
        $validation = Validator::make($request->all(), [
            // 'address_detail_id' => 'required',
            'title'             => 'required',
            // 'price'             => 'required',
            //'short_summary'     => 'required',
            // 'quantity'          => 'required',
            'description'       => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        { 
            if($productsServicesBook->user_id != Auth::id())
            {
                return response(prepareResult(true, [], getLangByLabelGroups('messages','message_unauthorized')), config('http_response.unauthorized'));
            }
            if(!empty($request->is_used_item))
            {
                $is_used_item = $request->is_used_item;
            }
            else
            {
                $is_used_item = '0';
            }

            $type = $productsServicesBook->type;

            $amount = $request->basic_price_wo_vat;
            $is_on_offer = $request->is_on_offer;
            $discount_type = $request->discount_type;
            $discount_value = $request->discount_value;
            $vat_percentage = 0;
            $catVatId = CategoryMaster::select('vat')->find($request->category_master_id);
            if($catVatId)
            {
                $vat_percentage = $catVatId->vat;
            }
            $user_id = Auth::id();
            
            $getCommVal = updateCommissions($amount, $is_on_offer, $discount_type, $discount_value, $vat_percentage, $user_id, $request->type);

            $productsServicesBook->address_detail_id    = $request->address_detail_id;
            $productsServicesBook->category_master_id   = $request->category_master_id;
            $productsServicesBook->sub_category_slug    = $request->sub_category_slug;
            $productsServicesBook->type     = $request->type;
            $productsServicesBook->brand    = $request->brand;
            $productsServicesBook->sku      = $request->sku;
            $productsServicesBook->gtin_isbn= $request->gtin_isbn;
            $productsServicesBook->title    = $request->title;
            $productsServicesBook->basic_price_wo_vat           = $request->basic_price_wo_vat;
            $productsServicesBook->price = $getCommVal['price_with_all_com_vat'];
            $productsServicesBook->shipping_charge              = $request->shipping_charge;
            $productsServicesBook->discounted_price = $getCommVal['totalAmount'];

            $productsServicesBook->vat_percentage = $vat_percentage;
            $productsServicesBook->vat_amount = $getCommVal['vat_amount'];
            $productsServicesBook->ss_commission_percent = $getCommVal['ss_commission_percent'];
            $productsServicesBook->ss_commission_amount = $getCommVal['ss_commission_amount'];
            $productsServicesBook->cc_commission_percent_all = $getCommVal['totalCCPercent'];
            $productsServicesBook->cc_commission_amount_all = $getCommVal['totalCCAmount'];

            $productsServicesBook->is_on_offer      = $request->is_on_offer;
            $productsServicesBook->discount_type    = $request->discount_type;
            $productsServicesBook->discount_value   = $request->discount_value;
            if($type == 'service') {
                $qty = 1000;
            } else {
                $qty = $request->quantity;
            }
            $productsServicesBook->quantity         = $qty;
            $productsServicesBook->short_summary    = $request->short_summary;
            $productsServicesBook->description      = $request->description;
            $productsServicesBook->attribute_details    = ($request->attribute_details=='[]') ? null : $request->attribute_details;
            $productsServicesBook->meta_description = $request->meta_description;
            $productsServicesBook->sell_type        = $request->sell_type;
            $productsServicesBook->deposit_amount   = $request->deposit_amount;
            $productsServicesBook->is_used_item     = $is_used_item;
            $productsServicesBook->item_condition   = $request->item_condition;
            $productsServicesBook->author           = $request->author;
            $productsServicesBook->published_year   = $request->published_year;
            $productsServicesBook->publisher        = $request->publisher;
            $productsServicesBook->language         = (!empty($request->language)) ? $request->language : null;
            $productsServicesBook->no_of_pages      = $request->no_of_pages;
            $productsServicesBook->suitable_age     = $request->suitable_age;
            $productsServicesBook->book_cover       = $request->book_cover;
            $productsServicesBook->dimension_length = $request->dimension_length;
            $productsServicesBook->dimension_width  = $request->dimension_width;
            $productsServicesBook->dimension_height = $request->dimension_height;
            $productsServicesBook->weight           = $request->weight;
            $productsServicesBook->service_type     = $request->service_type;
            $productsServicesBook->delivery_type    = $request->delivery_type;
            $productsServicesBook->service_period_time      = $request->service_period_time;
            $productsServicesBook->service_period_time_type = $request->service_period_time_type;
            $productsServicesBook->service_online_link      = $request->service_online_link;
            $productsServicesBook->service_languages        = (!empty($request->service_languages)) ? json_encode($request->service_languages, JSON_UNESCAPED_UNICODE) : null;
            $productsServicesBook->tags                     = (!empty($request->tags)) ? json_encode($request->tags, JSON_UNESCAPED_UNICODE) : null;
            // $productsServicesBook->is_promoted      = $request->is_promoted;
            $productsServicesBook->meta_title                 = $request->meta_title;
            $productsServicesBook->meta_keywords              = $request->meta_keywords;
            $productsServicesBook->is_reward_point_applicable   = $request->is_reward_point_applicable;
            $productsServicesBook->reward_points                = $request->reward_points;
            // if($request->is_promoted==1)
            // {
            //     $productsServicesBook->promotion_start_at       = $request->promotion_start_at;
            //     $productsServicesBook->promotion_end_at = $request->promotion_end_at;
            // }
            $productsServicesBook->is_published     = $request->is_published;
            $productsServicesBook->published_at     = ($request->is_published==1) ? date('Y-m-d H:i:s') : null;
            if($productsServicesBook->save())
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

                ProductTag::where('products_services_book_id',$productsServicesBook->id)->delete();
                foreach ($request->tags as $key => $tag) {
                    $allTypeTag = new ProductTag;
                    $allTypeTag->products_services_book_id   = $productsServicesBook->id;
                    $allTypeTag->user_id                  = Auth::id();
                    $allTypeTag->title                    = $tag;
                    $allTypeTag->type                     = $request->type;
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
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
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

    public function stockUpdate(Request $request, $id)
    {
        $lang_id = $this->lang_id;
        if(empty($lang_id))
        {
            $lang_id = Language::select('id')->first()->id;
        }

        $productsServicesBook = ProductsServicesBook::find($id);
        $productsServicesBook->update(['quantity'=>($productsServicesBook->quantity + $request->quantity)]);
        $productsServicesBook = ProductsServicesBook::with('categoryMaster','subCategory','user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','productImages','productTags')
        ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
            ->find($id);
        $productsServicesBook['category_detail'] = CategoryDetail::where('category_master_id',$productsServicesBook->category_master_id)->where('language_id',$productsServicesBook->language_id)->first();
        return response()->json(prepareResult(false, $productsServicesBook, getLangByLabelGroups('messages','messages_products_services_book_stock_updated')), config('http_response.success'));
    }

    public function action($productsServicesBook_id, Request $request)
    {
        $validation = Validator::make($request->all(), [
                
        ]);
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
        if($request->action=='promote') 
        {
            $validation = Validator::make($request->all(), [
                'is_promoted'           => 'required|boolean',
            ]);
        }

        if($request->action=='most_popular') 
        {
            $validation = Validator::make($request->all(), [
                'most_popular'           => 'required|boolean',
            ]);
        }
        if($request->action=='top_selling') 
        {
            $validation = Validator::make($request->all(), [
                'top_selling'           => 'required|boolean',
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
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
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
                $body =  'Product '.$getProductsServicesBook->title.' has been successfully Published.';
            }

            if($request->action=='promote') 
            {
                $getProductsServicesBook->is_promoted        = $request->is_promoted;
                if($request->is_promoted == true)
                {
                	$getProductsServicesBook->promotion_start_at = date('Y-m-d');
                	$user_package = UserPackageSubscription::where('user_id',Auth::id())->where('subscription_status', 1)->where('module',$getProductsServicesBook->type)->orderBy('created_at','desc')->first();
                    if(empty($user_package))
                    {
                        return response()->json(prepareResult(true, ['No Package Subscribed'], getLangByLabelGroups('messages','message_no_package_subscribed_error')), config('http_response.internal_server_error'));
                    }
                	if($user_package->no_of_boost == $user_package->used_no_of_boost)
                	{
                	    DB::rollback();
                	    return response()->json(prepareResult(true, ['Package Use Exhasted'], getLangByLabelGroups('messages','message_no_of_boost_exhausted_error')), config('http_response.internal_server_error'));
                	}
                	$getProductsServicesBook->promotion_end_at  = date('Y-m-d',strtotime('+'.$user_package->boost_no_of_days.'days'));
                	$user_package->update(['used_no_of_boost'=>($user_package->used_no_of_boost + 1)]);
                    $title = 'Product Promoted';
                    $body =  'Product '.$getProductsServicesBook->title.' has been successfully Promoted  from '.$getProductsServicesBook->promotion_start_at.' to '.$getProductsServicesBook->promotion_end_at.'.';
                }
                else
                {
                    $title = 'Product Removed from Promoted';
                    $body =  'Product '.$getProductsServicesBook->title.' has been successfully Removed from Promotion.';
                }
            }

            if($request->action=='most_popular') 
            {
                $getProductsServicesBook->most_popular        = $request->most_popular;
                if($request->most_popular == true)
                {
	                $getProductsServicesBook->most_popular_start_at = date('Y-m-d');
	                $user_package = UserPackageSubscription::where('user_id',Auth::id())->where('subscription_status', 1)->where('module',$getProductsServicesBook->type)->orderBy('created_at','desc')->first();
                    if(empty($user_package))
                    {
                        return response()->json(prepareResult(true, ['No Package Subscribed'], getLangByLabelGroups('messages','message_no_package_subscribed_error')), config('http_response.internal_server_error'));
                    }
	                if($user_package->most_popular == $user_package->used_most_popular)
	                {
	                    DB::rollback();
	                    return response()->json(prepareResult(true, ['Package Use Exhasted'], getLangByLabelGroups('messages','message_most_popular_exhausted_error')), config('http_response.internal_server_error'));
	                }
	                $getProductsServicesBook->most_popular_end_at  = date('Y-m-d',strtotime('+'.$user_package->most_popular_no_of_days.'days'));
	                $user_package->update(['used_most_popular'=>$user_package->used_most_popular + 1]);
	            	$title = 'Product  updated as Most Popular';
                    $body =  'Product '.$getProductsServicesBook->title.' has been successfully updated as Most Popular  from '.$getProductsServicesBook->most_popular_start_at.' to '.$getProductsServicesBook->most_popular_end_at.'.';
                }
                else
                {
                    $title = 'Product Removed from Most Popular';
                    $body =  'Product '.$getProductsServicesBook->title.' has been successfully Removed from Most Popular.';
                }
                
            }

            if($request->action=='top_selling') 
            {
                $getProductsServicesBook->top_selling        = $request->top_selling;
                if($request->top_selling == true)
                {

	                $getProductsServicesBook->top_selling_start_at = date('Y-m-d');
	                $user_package = UserPackageSubscription::where('user_id',Auth::id())->where('subscription_status', 1)->where('module',$getProductsServicesBook->type)->orderBy('created_at','desc')->first();
                    if(empty($user_package))
                    {
                        return response()->json(prepareResult(true, ['No Package Subscribed'], getLangByLabelGroups('messages','message_no_package_subscribed_error')), config('http_response.internal_server_error'));
                    }
	                if($user_package->top_selling == $user_package->used_top_selling)
	                {
	                    DB::rollback();
	                    return response()->json(prepareResult(true, ['Package Use Exhasted'], getLangByLabelGroups('messages','message_top_selling_exhausted_error')), config('http_response.internal_server_error'));
	                }
	                $getProductsServicesBook->top_selling_end_at  = date('Y-m-d',strtotime('+'.$user_package->top_selling_no_of_days.'days'));
	                $user_package->update(['used_top_selling'=>$user_package->used_top_selling + 1]);
	            	$title = 'Product  updated as Top Selling';
                    $body =  'Product '.$getProductsServicesBook->title.' has been successfully updated as Top Selling  from '.$getProductsServicesBook->top_selling_start_at.' to '.$getProductsServicesBook->top_selling_end_at.'.';
                }
                else
                {
                    $title = 'Product Removed from Top Selling';
                    $body =  'Product '.$getProductsServicesBook->title.' has been successfully Removed from Top Selling.';
                }   
            }

            if($request->action=='all_boost') 
            {
                if($getProductsServicesBook->is_promoted != $request->promote)
                {
                    $getProductsServicesBook->is_promoted = $request->promote;
                    if($request->promote == true)
                    {
                        $getProductsServicesBook->promotion_start_at = date('Y-m-d');
                        $user_package = UserPackageSubscription::where('user_id',Auth::id())->where('subscription_status', 1)->where('module',$getProductsServicesBook->type)->orderBy('created_at','desc')->first();
                        if(empty($user_package))
                        {
                            return response()->json(prepareResult(true, ['No Package Subscribed'], getLangByLabelGroups('messages','message_no_package_subscribed_error')), config('http_response.internal_server_error'));
                        }
                        if($user_package->no_of_boost == $user_package->used_no_of_boost)
                        {
                            DB::rollback();
                            return response()->json(prepareResult(true, ['promotion Package Use Exhasted'], getLangByLabelGroups('messages','message_no_of_boost_exhausted_error')), config('http_response.internal_server_error'));
                        }
                        $getProductsServicesBook->promotion_end_at  = date('Y-m-d',strtotime('+'.$user_package->boost_no_of_days.'days'));
                        $user_package->update(['used_no_of_boost'=>($user_package->used_no_of_boost + 1)]);
                        $title = 'Product Promoted';
                        $body =  'Product '.$getProductsServicesBook->title.' has been successfully Promoted  from '.$getProductsServicesBook->promotion_start_at.' to '.$getProductsServicesBook->promotion_end_at.'.';
                    }
                    else
                    {
                        $title = 'Product Removed from Promoted';
                        $body =  'Product '.$getProductsServicesBook->title.' has been successfully Removed from Promotion.';
                    }
                    $type = 'Product Action';
                    if($getProductsServicesBook->type == 'book')
                    {
                        $module = 'book';
                    }
                    else
                    {
                        $module = 'product_service';
                    }
                    pushNotification($title,$body,Auth::user(),$type,true,'creator',$module,$getProductsServicesBook->id,'my-listing');
                }

                if($getProductsServicesBook->most_popular != $request->most_popular)
                {
                    $getProductsServicesBook->most_popular        = $request->most_popular;
                    if($request->most_popular == true)
                    {
                        $getProductsServicesBook->most_popular_start_at = date('Y-m-d');
                        $user_package = UserPackageSubscription::where('user_id',Auth::id())->where('subscription_status', 1)->where('module',$getProductsServicesBook->type)->orderBy('created_at','desc')->first();
                        if(empty($user_package))
                        {
                            return response()->json(prepareResult(true, ['No Package Subscribed'], getLangByLabelGroups('messages','message_no_package_subscribed_error')), config('http_response.internal_server_error'));
                        }
                        if($user_package->most_popular == $user_package->used_most_popular)
                        {
                            DB::rollback();
                            return response()->json(prepareResult(true, ['Popular Package Use Exhasted'], getLangByLabelGroups('messages','message_most_popular_exhausted_error')), config('http_response.internal_server_error'));
                        }
                        $getProductsServicesBook->most_popular_end_at  = date('Y-m-d',strtotime('+'.$user_package->most_popular_no_of_days.'days'));
                        $user_package->update(['used_most_popular'=>$user_package->used_most_popular + 1]);
                        $title = 'Product  updated as Most Popular';
                        $body =  'Product '.$getProductsServicesBook->title.' has been successfully updated as Most Popular  from '.$getProductsServicesBook->most_popular_start_at.' to '.$getProductsServicesBook->most_popular_end_at.'.';
                    }
                    else
                    {
                        $title = 'Product Removed from Most Popular';
                        $body =  'Product '.$getProductsServicesBook->title.' has been successfully Removed from Most Popular.';
                    }
                    $type = 'Product Action';
                    if($getProductsServicesBook->type == 'book')
                    {
                        $module = 'book';
                    }
                    else
                    {
                        $module = 'product_service';
                    }
                    pushNotification($title,$body,Auth::user(),$type,true,'creator',$module,$getProductsServicesBook->id,'my-listing');
                }

                if($getProductsServicesBook->top_selling != $request->top_selling)
                {
                    $getProductsServicesBook->top_selling        = $request->top_selling;
                    if($request->top_selling == true)
                    {

                        $getProductsServicesBook->top_selling_start_at = date('Y-m-d');
                        $user_package = UserPackageSubscription::where('user_id',Auth::id())->where('subscription_status', 1)->where('module',$getProductsServicesBook->type)->orderBy('created_at','desc')->first();
                        if(empty($user_package))
                        {
                            return response()->json(prepareResult(true, ['No Package Subscribed'], getLangByLabelGroups('messages','message_no_package_subscribed_error')), config('http_response.internal_server_error'));
                        }
                        if($user_package->top_selling == $user_package->used_top_selling)
                        {
                            DB::rollback();
                            return response()->json(prepareResult(true, ['Top selling Package Use Exhasted'], getLangByLabelGroups('messages','message_top_selling_exhausted_error')), config('http_response.internal_server_error'));
                        }
                        $getProductsServicesBook->top_selling_end_at  = date('Y-m-d',strtotime('+'.$user_package->top_selling_no_of_days.'days'));
                        $user_package->update(['used_top_selling'=>$user_package->used_top_selling + 1]);
                        $title = 'Product  updated as Top Selling';
                        $body =  'Product '.$getProductsServicesBook->title.' has been successfully updated as Top Selling  from '.$getProductsServicesBook->top_selling_start_at.' to '.$getProductsServicesBook->top_selling_end_at.'.';
                    }
                    else
                    {
                        $title = 'Product Removed from Top Selling';
                        $body =  'Product '.$getProductsServicesBook->title.' has been successfully Removed from Top Selling.';
                    }  
                    $type = 'Product Action';
                    if($getProductsServicesBook->type == 'book')
                    {
                        $module = 'book';
                    }
                    else
                    {
                        $module = 'product_service';
                    }
                    pushNotification($title,$body,Auth::user(),$type,true,'creator',$module,$getProductsServicesBook->id,'my-listing');
                }
            }

            $getProductsServicesBook->save();

            DB::commit();
            return response()->json(prepareResult(false, $getProductsServicesBook, getLangByLabelGroups('messages','messages_products_services_book_'.$request->action)), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }


    public function usedProducts(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            $productsServicesBooks = ProductsServicesBook::where('is_used_item', true)
            ->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
            ->orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags')
            ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }]);
            if(!empty($request->per_page_record))
            {
                $productsServicesBooks = $productsServicesBooks->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $productsServicesBooks = $productsServicesBooks->get();
            }
            return response(prepareResult(false, $productsServicesBooks, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function productsImport(Request $request) 
    {
        $data = [
            'user_id'               => $request->user_id, 
            'is_used_item'          => (User::find($request->user_id)->user_type_id==2) ? 1 : 0, 
            'address_detail_id'     => $request->address_detail_id, 
            'category_master_id'    => $request->category_master_id, 
            'sub_category_slug'     => $request->sub_category_slug
        ];
        $import = Excel::import(new ProductsImport($data),request()->file('file'));

        return response(prepareResult(false, [], getLangByLabelGroups('messages','messages_products_services_book_imported')), config('http_response.success'));
    }

    

    public function companyProductsFilter(Request $request)
    {

        //for all type of filter
        //\DB::enableQueryLog();

        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            $type = 'product';
            if(!empty($request->type))
            {
                $type = $request->type;
            }
            $searchType = $request->searchType; 
            $products = ProductsServicesBook::select('products_services_books.*')
            //->where('products_services_books.user_id', '!=', Auth::id())
            ->where('products_services_books.status', '2')
            ->where('products_services_books.is_published', '1')
            ->where('products_services_books.quantity','>' ,'0')
            ->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
            ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
            ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }]);
            
            if($request->is_used_item!='both')
            {
                if($request->is_used_item=='yes')
                {
                    $products->where('products_services_books.is_used_item', 1)->where('products_services_books.is_sold', '0');
                }
                else
                {
                    $products->where('products_services_books.is_used_item', 0);
                }
                
            }

            if($searchType=='promotion' || $searchType=='latest' || $searchType=='bestSelling' || $searchType=='topRated' || $searchType=='random' || $searchType=='popular') 
            {
                $products->where('products_services_books.type', $type);
            }
            
            if($searchType=='filter')
            {
                if(empty($request->search))
                {
                    $products->where('products_services_books.type', $type);
                }

                if(!empty($request->title))
                {
                    $products->where('products_services_books.title', 'LIKE', '%'.$request->title.'%');
                }
                if(!empty($request->search_title))
                {
                    $products->where('products_services_books.title', 'LIKE', '%'.$request->search_title.'%');
                }
                if(!empty($request->avg_rating))
                {
                    $products->where('products_services_books.avg_rating', '>=', $request->avg_rating);
                }
                if(!empty($request->brand))
                {
                    $products->whereIn('products_services_books.brand', $request->brand);
                }
                if(!empty($request->category_master_id))
                {
                    $products->where('products_services_books.category_master_id', $request->category_master_id);
                }
                if(!empty($request->sub_category_slug))
                {
                    $products->where('products_services_books.sub_category_slug', $request->sub_category_slug);
                }
                
                if(!empty($request->min_price))
                {
                    $min_price = (float) $request->min_price;
                    
                    $products->whereRaw("(CASE WHEN products_services_books.is_on_offer = 1 THEN products_services_books.discounted_price >= $min_price ELSE products_services_books.price >= $min_price END)");
                    
                }
                if(!empty($request->max_price))
                {
                    $max_price = (float) $request->max_price;
                    $products->whereRaw("(CASE WHEN products_services_books.is_on_offer = 1 THEN products_services_books.discounted_price <= $max_price ELSE products_services_books.price <= $max_price END)");
                }
                if(!empty($request->sell_type))
                {
                    $products->where('products_services_books.sell_type', $request->sell_type);
                }
                if(!empty($request->city))
                {
                    $products->join('address_details', function ($join) {
                        $join->on('products_services_books.address_detail_id', '=', 'address_details.id');
                    })->whereIn('address_details.city', $request->city);
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
                    $products->where('products_services_books.suitable_age',$request->suitable_age);
                }
                if(!empty($request->attributes_data))
                {
                    $attr = $request->attributes_data;
                    $newAttribute = new RecursiveIteratorIterator(new RecursiveArrayIterator($request->all()), RecursiveIteratorIterator::SELF_FIRST);
                    $result = [];
                    foreach ($newAttribute as $key => $value) {
                        if (($key === 'bucket_group_attributes' || $key === 'bucket_group_attributes_2') && $key) {
                            $result = array_merge($result, $value);
                        }
                    }
                    $arrForSelecteds = [];
                    foreach ($result as $key => $value) {
                        if(@$value['selected'])
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
                	 $products = ProductsServicesBook::select('products_services_books.*')
			            //->where('products_services_books.user_id', '!=', Auth::id())
			            ->where('products_services_books.status', '2')
			            ->where('products_services_books.type', $type)
			            ->where('products_services_books.is_published', '1')
                        ->where('products_services_books.quantity','>' ,'0')
                        ->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
        	 			->withCount('orderItems')->orderBy('order_items_count','desc')
			            ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
                        ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                            $q->select('id','category_master_id','title','slug')
                                ->where('language_id', $lang_id)
                                ->where('is_parent', '1');
                        }])
                        ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                            $q->select('id','category_master_id','title','slug')
                                ->where('language_id', $lang_id)
                                ->where('is_parent', '0');
                        }]); 
                        if($request->is_used_item!='both')
                        {
                            if($request->is_used_item=='yes')
                            {
                                $products->where('products_services_books.is_used_item', '1')->where('products_services_books.is_sold', '0');
                            }
                            else
                            {
                                $products->where('products_services_books.is_used_item', '0');
                            }
                            
                        }
                }
            }
            elseif($searchType=='topRated')
            {
                $products->orderBy('products_services_books.avg_rating','DESC');
            }
            elseif($searchType=='random')
            {
                $products->orderBy('products_services_books.auto_id', 'ASC')->inRandomOrder();
            }
            elseif($searchType=='popular')
            {
                $products->where('products_services_books.most_popular', '1')
                ->where('products_services_books.most_popular_start_at','<=', date('Y-m-d'))
                ->where('products_services_books.most_popular_end_at','>=', date('Y-m-d'));

                if($products->count() <= 0)
                {
                    $products = ProductsServicesBook::select('products_services_books.*')
                    ->join('users', function ($join) {
                        $join->on('products_services_books.user_id', '=', 'users.id');
                    })
                    ->where('products_services_books.status', '2')
                    ->where('products_services_books.is_published', '1')
                    ->where('products_services_books.quantity','>', '0')
                    ->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
                    //->where('products_services_books.user_id', '!=', Auth::id())
                    ->where('users.user_type_id','3')
                    ->orderBy('products_services_books.view_count', 'DESC')->limit(10)
                    ->where('products_services_books.type', $type)
                    ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
                }
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
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function companyServicesFilter(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            $searchType = $request->searchType; //filter, promotion, latest, closingSoon, random, criteria users
            $products = ProductsServicesBook::select('products_services_books.*')
            ->join('users', function ($join) {
                $join->on('products_services_books.user_id', '=', 'users.id');
            })
            ->where('products_services_books.status', '2')
            ->where('products_services_books.is_published', '1')
            //->where('products_services_books.user_id', '!=', Auth::id())
            ->where('users.user_type_id','3')
            ->where('products_services_books.type','service')
            ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
            ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }]);
            if($searchType=='promotion')
            {
                $products->where('products_services_books.is_promoted', '1')
                ->where('products_services_books.promotion_start_at','<=', date('Y-m-d'))
                ->where('products_services_books.promotion_end_at','>=', date('Y-m-d'));
            }
            elseif($searchType=='latest')
            {
                $products->orderBy('created_at','DESC');
            }
            elseif($searchType=='popular')
            {
                $products->where('products_services_books.most_popular', '1')
                ->where('products_services_books.most_popular_start_at','<=', date('Y-m-d'))
                ->where('products_services_books.most_popular_end_at','>=', date('Y-m-d'));

                if($products->count() <= 0)
                {
                    $products = ProductsServicesBook::select('products_services_books.*')
                    ->join('users', function ($join) {
                        $join->on('products_services_books.user_id', '=', 'users.id');
                    })
                    ->where('products_services_books.status', '2')
                    ->where('products_services_books.is_published', '1')
                    ->where('products_services_books.quantity','>', '0')
                    //->where('products_services_books.user_id', '!=', Auth::id())
                    ->where('users.user_type_id','3')
                    ->orderBy('products_services_books.view_count', 'DESC')->limit(10)
                    ->where('products_services_books.type','service')
                    ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
                }
            }
            elseif($searchType=='topRated')
            {
                $products->orderBy('products_services_books.avg_rating','DESC');
            }
            elseif($searchType=='random')
            {
                $products->orderBy('products_services_books.auto_id', 'ASC')->inRandomOrder();
            }
            elseif($searchType=='popular')
            {
                $products->where('products_services_books.most_popular', '1')
                ->where('products_services_books.most_popular_start_at','<=', date('Y-m-d'))
                ->where('products_services_books.most_popular_end_at','>=', date('Y-m-d'));

                if($products->count() <= 0)
                {
                    $products = ProductsServicesBook::select('products_services_books.*')
                    ->join('users', function ($join) {
                        $join->on('products_services_books.user_id', '=', 'users.id');
                    })
                    ->where('products_services_books.status', '2')
                    ->where('products_services_books.is_published', '1')
                    ->where('products_services_books.quantity','>', '0')
                    //->where('products_services_books.user_id', '!=', Auth::id())
                    ->where('users.user_type_id','3')
                    ->orderBy('products_services_books.view_count', 'DESC')->limit(10)
                    ->where('products_services_books.type','service')
                    ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
                }
            }
            if(!empty($request->per_page_record))
            {
                $productsData = $products->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $productsData = $products->get();
            }
            // $productsData['total_records'] = $products->count();
            if($request->other_function=='yes')
            {
                return $productsData;
            }
            return response(prepareResult(false, $productsData, getLangByLabelGroups('messages','messages_users_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function studentProductsFilter(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            $searchType = $request->searchType; 
            $type = 'product';
            if(!empty($request->type))
            {
                $type = $request->type;
            }
            $products = ProductsServicesBook::where('is_used_item', '1')
            //->where('user_id', '!=', Auth::id())
            ->where('status', '2')
            ->where('is_published', '1')
            ->where('is_sold', '0')
            ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.studentDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
            ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }]);

            if($searchType=='promotion' || $searchType=='latest' || $searchType=='bestSelling' || $searchType=='topRated' || $searchType=='random' || $searchType=='popular') 
            {
                $products->where('products_services_books.type', $type);
            }
            if($searchType=='filter')
            {
                
            }
            elseif($searchType=='promotion')
            {
                $products->where('is_promoted', '1')
                ->where('promotion_start_at','<=', date('Y-m-d'))
                ->where('promotion_end_at','>=', date('Y-m-d'));
            }
            elseif($searchType=='latest')
            {
                $products->orderBy('created_at','DESC');
            }
            elseif($searchType=='bestSelling')
            {
                $products->where('top_selling', '1')
                ->where('top_selling_start_at','<=', date('Y-m-d'))
                ->where('top_selling_end_at','>=', date('Y-m-d'));
                if($products->count() <= 0)
                {
                	$products = ProductsServicesBook::where('is_used_item', '1')
                    ->where('type','product')
                    //->where('user_id', '!=', Auth::id())
                    ->where('status', '2')
                    ->where('is_sold', '0')
                    ->where('is_published', '1')
                    ->where('quantity','>' ,'0')
    	 			->withCount('orderItems')->orderBy('order_items_count','desc')
		            ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.studentDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]); 
                }
            }
            elseif($searchType=='topRated')
            {
                $products->orderBy('avg_rating','DESC');
            }
            elseif($searchType=='random')
            {
                $products->orderBy('products_services_books.auto_id', 'ASC')->inRandomOrder();
            }
            elseif($searchType=='popular')
            {
                $products->where('products_services_books.most_popular', '1')
                ->where('products_services_books.most_popular_start_at','<=', date('Y-m-d'))
                ->where('products_services_books.most_popular_end_at','>=', date('Y-m-d'));

                if($products->count() <= 0)
                {
                    $products = ProductsServicesBook::select('products_services_books.*')
                    ->join('users', function ($join) {
                        $join->on('products_services_books.user_id', '=', 'users.id');
                    })
                    ->where('products_services_books.status', '2')
                    ->where('products_services_books.is_published', '1')
                    ->where('products_services_books.quantity','>', '0')
                    //->where('products_services_books.user_id', '!=', Auth::id())
                    ->where('users.user_type_id','2')
                    ->where('products_services_books.is_sold', '0')
                    ->orderBy('products_services_books.view_count', 'DESC')->limit(10)
                    ->where('products_services_books.type','product')
                    ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
                }
            }
            if(!empty($request->per_page_record))
            {
                $productsData = $products->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $productsData = $products->get();
            }
            // $productsData['total_records'] = $products->count();
            if($request->other_function=='yes')
            {
                return $productsData;
            }

            return response(prepareResult(false, $productsData, getLangByLabelGroups('messages','messages_users_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function studentServicesFilter(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            $searchType = $request->searchType; //filter, promotion, latest, closingSoon, random, criteria users
            $products = ProductsServicesBook::select('products_services_books.*')
            ->join('users', function ($join) {
                $join->on('products_services_books.user_id', '=', 'users.id');
            })
            //->where('products_services_books.user_id', '!=', Auth::id())
            ->where('products_services_books.status', '2')
            ->where('products_services_books.is_published', '1')
            ->where('users.user_type_id','2')
            ->where('products_services_books.type','service')
            ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.studentDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
            ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }]);
            if($searchType=='filter')
            {
                if(!empty($request->title))
                {
                    $products->where('products_services_books.title', 'LIKE', '%'.$request->title.'%');
                }
                if(!empty($request->search_title))
                {
                    $products->where('products_services_books.title', 'LIKE', '%'.$request->search_title.'%');
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
                
                if(!empty($request->min_price))
                {
                    $products->where('products_services_books.discounted_price', '>=', $request->min_price);
                }
                if(!empty($request->max_price))
                {
                    $products->where('products_services_books.discounted_price', '<=', $request->max_price);
                }
                if(!empty($request->sell_type))
                {
                    $products->where('products_services_books.sell_type', $request->sell_type);
                }
                if(!empty($request->city))
                {
                    $products->join('address_details', function ($join)  use ($request){
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
                        if (($key === 'bucket_group_attributes' || $key === 'bucket_group_attributes_2') && $key) {
                            $result = array_merge($result, $value);
                        }
                    }
                    $arrForSelecteds = [];
                    foreach ($result as $key => $value) {
                        if(@$value['selected'])
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
            elseif($searchType=='popular')
            {
                $products->where('products_services_books.most_popular', '1')
                ->where('products_services_books.most_popular_start_at','<=', date('Y-m-d'))
                ->where('products_services_books.most_popular_end_at','>=', date('Y-m-d'));
                if($products->count() <= 0)
                {
                    $products = ProductsServicesBook::select('products_services_books.*')
                    ->join('users', function ($join) {
                        $join->on('products_services_books.user_id', '=', 'users.id');
                    })
                    //->where('products_services_books.user_id', '!=', Auth::id())
                    ->where('products_services_books.status', '2')
                    ->where('products_services_books.is_published', '1')
                    ->where('products_services_books.quantity','>', '0')
                    ->where('users.user_type_id','2')
                    ->where('products_services_books.type','service')
                    ->orderBy('products_services_books.view_count', 'DESC')->limit(10)
                    ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.studentDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
                }
            }
            elseif($searchType=='topRated')
            {
                $products->orderBy('products_services_books.avg_rating','DESC');
            }
            if(!empty($request->per_page_record))
            {
                $productsData = $products->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $productsData = $products->get();
            }
            // $productsData['total_records'] = $products->count();
            if($request->other_function=='yes')
            {
                return $productsData;
            }
            return response(prepareResult(false, $productsData, getLangByLabelGroups('messages','messages_users_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
    
    public function productLandingPage(Request $request)
    {
        $content = new Request();
        $content->is_used_item = 'no';
        $content->type = 'product';
        $content->searchType = 'promotion';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_product_promotions = $this->companyProductsFilter($content);
        
        $content = new Request();
        $content->is_used_item = 'no';
        $content->type = 'product';
        $content->searchType = 'bestSelling';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_product_best_selling = $this->companyProductsFilter($content);
        
        $content = new Request();
        $content->is_used_item = 'no';
        $content->type = 'product';
        $content->searchType = 'topRated';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_product_top_rated = $this->companyProductsFilter($content);
        
        $content = new Request();
        $content->is_used_item = 'no';
        $content->type = 'product';
        $content->searchType = 'random';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_product_random = $this->companyProductsFilter($content);
        
        $content = new Request();
        $content->is_used_item = 'no';
        $content->type = 'product';
        $content->searchType = 'latest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_product_latest = $this->companyProductsFilter($content);

        $content = new Request();
        $content->is_used_item = 'no';
        $content->type = 'product';
        $content->searchType = 'popular';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_product_popular = $this->companyProductsFilter($content);
        
        
        $content = new Request();
        $content->type = 'service';
        $content->is_used_item = 'no';
        $content->searchType = 'promotion';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_service_promotions = $this->companyServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->is_used_item = 'no';
        $content->searchType = 'latest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_service_latest = $this->companyServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->is_used_item = 'no';
        $content->searchType = 'popular';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_service_popular = $this->companyServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->is_used_item = 'no';
        $content->searchType = 'topRated';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_service_top_rated = $this->companyServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->is_used_item = 'no';
        $content->searchType = 'random';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_service_random = $this->companyServicesFilter($content);


        $content = new Request();
        $content->type = 'book';
        $content->is_used_item = 'no';
        $content->searchType = 'promotion';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_promotions = $this->companyBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->is_used_item = 'no';
        $content->searchType = 'latest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_latest = $this->companyBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->is_used_item = 'no';
        $content->searchType = 'popular';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_popular = $this->companyBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->is_used_item = 'no';
        $content->searchType = 'topRated';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_top_rated = $this->companyBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->is_used_item = 'no';
        $content->searchType = 'random';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_random = $this->companyBooksFilter($content);

        $content = new Request();
        $content->type = 'book';
        $content->is_used_item = 'no';
        $content->searchType = 'bestSelling';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_best_selling = $this->companyBooksFilter($content);
        
        
        $returnObj = [
                'products' => [
                    'company_product_promotions'    => $company_product_promotions, 
                    'company_product_best_selling'  => $company_product_best_selling, 
                    'company_product_top_rated'     => $company_product_top_rated,
                    'company_product_random'        => $company_product_random, 
                    'company_product_latest'        => $company_product_latest,
                    'company_product_popular'       => $company_product_popular 
                ],
                'services' => [
                    'company_service_promotions'    => $company_service_promotions, 
                    'company_service_latest'        => $company_service_latest, 
                    'company_service_popular'       => $company_service_popular,
                    'company_service_top_rated'     => $company_service_top_rated, 
                    'company_service_random'        => $company_service_random,
                    'company_service_best_selling'  => $company_service_random
                ],
                'books' => [
                    'company_book_promotions'    => $company_book_promotions, 
                    'company_book_latest'        => $company_book_latest, 
                    'company_book_popular'       => $company_book_popular,
                    'company_book_top_rated'     => $company_book_top_rated, 
                    'company_book_random'        => $company_book_random,
                    'company_book_best_selling'  => $company_book_best_selling
                ]
            ];
        
        return response(prepareResult(false, $returnObj, getLangByLabelGroups('messages','messages_users_list')), config('http_response.success'));
    }

    public function studentProductLandingPage(Request $request)
    {
        $content = new Request();
        $content->is_used_item = 'yes';
        $content->type = 'product';
        $content->searchType = 'promotion';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_product_promotions = $this->studentProductsFilter($content);
        
        // $content = new Request();
        // $content->is_used_item = 'yes';
        // $content->type = 'product';
        // $content->searchType = 'bestSelling';
        // $content->per_page_record = '5';
        // $content->other_function = 'yes';
        // $content->lang_id = $request->lang_id;
        // $student_product_best_selling = $this->studentProductsFilter($content);
        
        // $content = new Request();
        // $content->is_used_item = 'yes';
        // $content->type = 'product';
        // $content->searchType = 'topRated';
        // $content->per_page_record = '5';
        // $content->other_function = 'yes';
        // $content->lang_id = $request->lang_id;
        // $student_product_top_rated = $this->studentProductsFilter($content);
        
        $content = new Request();
        $content->is_used_item = 'yes';
        $content->type = 'product';
        $content->searchType = 'random';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_product_random = $this->studentProductsFilter($content);
        
        $content = new Request();
        $content->is_used_item = 'yes';
        $content->type = 'product';
        $content->searchType = 'latest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_product_latest = $this->studentProductsFilter($content);


        $content = new Request();
        $content->is_used_item = 'yes';
        $content->type = 'product';
        $content->searchType = 'popular';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_product_popular = $this->studentProductsFilter($content);
        
        
        $content = new Request();
        $content->type = 'service';
        $content->is_used_item = 'yes';
        $content->searchType = 'promotion';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_service_promotions = $this->studentServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->is_used_item = 'yes';
        $content->searchType = 'latest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_service_latest = $this->studentServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->is_used_item = 'yes';
        $content->searchType = 'popular';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_service_popular = $this->studentServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->is_used_item = 'yes';
        $content->searchType = 'topRated';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_service_top_rated = $this->studentServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->is_used_item = 'yes';
        $content->searchType = 'random';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_service_random = $this->studentServicesFilter($content);

        $content = new Request();
        $content->type = 'book';
        $content->is_used_item = 'yes';
        $content->searchType = 'promotion';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_book_promotions = $this->studentBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->is_used_item = 'yes';
        $content->searchType = 'latest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_book_latest = $this->studentBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->is_used_item = 'yes';
        $content->searchType = 'popular';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_book_popular = $this->studentBooksFilter($content);
        
        // $content = new Request();
        // $content->type = 'book';
        // $content->is_used_item = 'yes';
        // $content->searchType = 'topRated';
        // $content->per_page_record = '5';
        // $content->other_function = 'yes';
        // $content->lang_id = $request->lang_id;
        // $student_book_top_rated = $this->studentBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->is_used_item = 'yes';
        $content->searchType = 'random';
        $content->per_page_record = !empty($request->per_page_record) ? $request->per_page_record : '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_book_random = $this->studentBooksFilter($content);

        // $content = new Request();
        // $content->type = 'book';
        // $content->searchType = 'bestSelling';
        // $content->per_page_record = '5';
        // $content->other_function = 'yes';
        // $content->lang_id = $request->lang_id;
        // $student_book_best_selling = $this->studentBooksFilter($content);
        
        
        $returnObj = [
                'products' => [
                    'student_product_promotions'    => $student_product_promotions, 
                    //'student_product_best_selling'  => $student_product_best_selling, 
                    //'student_product_top_rated'     => $student_product_top_rated,
                    'student_product_random'        => $student_product_random, 
                    'student_product_latest'        => $student_product_latest,
                    'student_product_popular'       => $student_product_popular 
                ],
                'services' => [
                    'student_service_promotions'    => $student_service_promotions, 
                    'student_service_latest'        => $student_service_latest, 
                    'student_service_popular'       => $student_service_popular,
                    'student_service_top_rated'     => $student_service_top_rated, 
                    'student_service_random'        => $student_service_random,
                    'student_service_best_selling'  => $student_service_random
                ],
                'books' => [
                    'student_book_promotions'    => $student_book_promotions, 
                    'student_book_latest'        => $student_book_latest, 
                    'student_book_popular'       => $student_book_popular,
                    //'student_book_top_rated'     => $student_book_top_rated, 
                    'student_book_random'        => $student_book_random,
                    //'student_book_best_selling'  => $student_book_best_selling
                ]
            ];
        
        return response(prepareResult(false, $returnObj, getLangByLabelGroups('messages','messages_users_list')), config('http_response.success'));
    }

    public function similarProducts(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            $productsServicesBooks = ProductsServicesBook::find($request->product_id);
            if($productsServicesBooks)
            {
                $similarProducts = ProductsServicesBook::where('status', '2')->where('id','!=', $request->product_id)
                ->where('sub_category_slug', $productsServicesBooks->sub_category_slug)
                ->where('is_used_item', $productsServicesBooks->is_used_item)
                ->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
                ->orderBy('created_at','DESC')->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
                ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '1');
                }])
                ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '0');
                }]);

                if(@$productsServicesBooks->user->user_type_id==2)
                {
                    $similarProducts->where('is_sold', '0');
                }

                if(!empty($request->per_page_record))
                {
                    $similarProducts = $similarProducts->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
                }
                else
                {
                    $similarProducts = $similarProducts->get();
                }
                return response(prepareResult(false, $similarProducts, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
            }
            return response()->json(prepareResult(true, [], getLangByLabelGroups('contest_home_page','no_data_found')), config('http_response.not_found'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function companyBooksFilter(Request $request)
    {  
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            $type = 'book';
            $searchType = $request->searchType; 
            $products = ProductsServicesBook::select('products_services_books.*')
            //->where('products_services_books.user_id', '!=', Auth::id())
            ->where('products_services_books.status', 2)
            ->where('products_services_books.type', $type)
            ->where('products_services_books.is_published', 1)
            ->where('products_services_books.quantity','>' , 0)
            ->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
            ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
            ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }]);

            if($request->is_used_item!='both')
            {
                if($request->is_used_item=='yes')
                {
                    $products->where('products_services_books.is_used_item', '1')->where('products_services_books.is_sold', '0');
                }
                else
                {
                    $products->where('products_services_books.is_used_item', '0');
                }
                
            }

            if($searchType=='filter')
            {
                if(!empty($request->title))
                {
                    $products->where('products_services_books.title', 'LIKE', '%'.$request->title.'%');
                }
                if(!empty($request->search_title))
                {
                    $products->where('products_services_books.title', 'LIKE', '%'.$request->search_title.'%');
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
                
                if(!empty($request->min_price))
                {
                    $products->where('products_services_books.discounted_price', '>=', $request->min_price);
                }
                if(!empty($request->max_price))
                {
                    $products->where('products_services_books.discounted_price', '<=', $request->max_price);
                }
                if(!empty($request->sell_type))
                {
                    $products->where('products_services_books.sell_type', $request->sell_type);
                }
                if(!empty($request->city))
                {
                    $products->join('address_details', function ($join)  use ($request){
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
                        if (($key === 'bucket_group_attributes' || $key === 'bucket_group_attributes_2') && $key) {
                            $result = array_merge($result, $value);
                        }
                    }
                    $arrForSelecteds = [];
                    foreach ($result as $key => $value) {
                        if(@$value['selected'])
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
                     $products = ProductsServicesBook::select('products_services_books.*')
                                //->where('products_services_books.user_id', '!=', Auth::id())
                    ->where('products_services_books.status', '2')
                    ->where('products_services_books.type', $type)
                    ->where('products_services_books.is_published', '1')
                    ->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
                    ->where('products_services_books.quantity','>' ,'0')
                    ->withCount('orderItems')->orderBy('order_items_count','desc')
                    ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
                    if($request->is_used_item!='both')
                    {
                        if($request->is_used_item=='yes')
                        {
                            $products->where('products_services_books.is_used_item', '1')->where('products_services_books.is_sold', '0');
                        }
                        else
                        {
                            $products->where('products_services_books.is_used_item', '0');
                        }
                        
                    }
                }
            }
            elseif($searchType=='topRated')
            {
                $products->orderBy('products_services_books.avg_rating','DESC');
            }
            elseif($searchType=='random')
            {
                $products->orderBy('products_services_books.auto_id', 'ASC')->inRandomOrder();
            }
            elseif($searchType=='popular')
            {
                $products->where('products_services_books.most_popular', '1')
                ->where('products_services_books.most_popular_start_at','<=', date('Y-m-d'))
                ->where('products_services_books.most_popular_end_at','>=', date('Y-m-d'));

                if($products->count() <= 0)
                {
                    $products = ProductsServicesBook::select('products_services_books.*')
                    ->join('users', function ($join) {
                        $join->on('products_services_books.user_id', '=', 'users.id');
                    })
                    ->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
                    ->where('products_services_books.status', '2')
                    ->where('products_services_books.is_published', '1')
                    ->where('products_services_books.quantity','>', '0')
                    //->where('products_services_books.user_id', '!=', Auth::id())
                    ->where('users.user_type_id','3')
                    ->orderBy('products_services_books.view_count', 'DESC')->limit(10)
                    ->where('products_services_books.type','book')
                    ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
                }
            }
            if(!empty($request->per_page_record))
            {
                $productsData = $products->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $productsData = $products->get();
            }
            // $productsData['total_records'] = $products->count();
            if($request->other_function=='yes')
            {
                return $productsData;
            }
                //dd(DB::getQueryLog());
            return response(prepareResult(false, $productsData, getLangByLabelGroups('messages','messages_users_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function bookLandingPage(Request $request)
    {
        $content = new Request();
        $content->is_used_item = 'no';
        $content->type = 'book';
        $content->searchType = 'promotion';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_promotions = $this->companyBooksFilter($content);
        
        $content = new Request();
        $content->is_used_item = 'no';
        $content->type = 'book';
        $content->searchType = 'bestSelling';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_best_selling = $this->companyBooksFilter($content);
        
        $content = new Request();
        $content->is_used_item = 'no';
        $content->type = 'book';
        $content->searchType = 'topRated';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_top_rated = $this->companyBooksFilter($content);
        
        $content = new Request();
        $content->is_used_item = 'no';
        $content->type = 'book';
        $content->searchType = 'random';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_random = $this->companyBooksFilter($content);
        
        $content = new Request();
        $content->is_used_item = 'no';
        $content->type = 'book';
        $content->searchType = 'latest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_latest = $this->companyBooksFilter($content);

        $content = new Request();
        $content->is_used_item = 'no';
        $content->type = 'book';
        $content->searchType = 'popular';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_popular = $this->companyBooksFilter($content);
        
        $returnObj = [
                'books' => [
                    'company_book_promotions'    => $company_book_promotions, 
                    'company_book_best_selling'  => $company_book_best_selling, 
                    'company_book_top_rated'     => $company_book_top_rated,
                    'company_book_random'        => $company_book_random, 
                    'company_book_latest'        => $company_book_latest,
                    'company_book_popular'       => $company_book_popular 
                ]
            ];
        
        return response(prepareResult(false, $returnObj, getLangByLabelGroups('messages','messages_users_list')), config('http_response.success'));
    }

    public function studentBooksFilter(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            $searchType = $request->searchType; 
            $products = ProductsServicesBook::where('is_used_item', 1)
                ->where('type','book')
                //->where('user_id', '!=', Auth::id())
                ->where('status', '2')
                ->where('is_published', '1')
                ->where('quantity','>' ,'0')
                ->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
                ->where('products_services_books.is_sold', '0')
                ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.studentDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
                ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '1');
                }])
                ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '0');
                }]);
            if($searchType=='filter')
            {
                
            }
            elseif($searchType=='promotion')
            {
                $products->where('is_promoted', '1')
                ->where('promotion_start_at','<=', date('Y-m-d'))
                ->where('promotion_end_at','>=', date('Y-m-d'));
            }
            elseif($searchType=='latest')
            {
                $products->orderBy('created_at','DESC');
            }
            elseif($searchType=='bestSelling')
            {
                $products->where('top_selling', '1')
                ->where('top_selling_start_at','<=', date('Y-m-d'))
                ->where('top_selling_end_at','>=', date('Y-m-d'));
                if($products->count() <= 0)
                {
                    $products = ProductsServicesBook::where('is_used_item', '1')
                    ->where('type','book')
                    //->where('user_id', '!=', Auth::id())
                    ->where('status', '2')
                    ->where('is_published', '1')
                    ->where('quantity','>' ,'0')
                    ->where('products_services_books.is_sold', '0')
                    ->withCount('orderItems')->orderBy('order_items_count','desc')
                    ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.studentDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]); 
                }
            }
            elseif($searchType=='topRated')
            {
                $products->orderBy('avg_rating','DESC');
            }
            elseif($searchType=='random')
            {
                $products->orderBy('products_services_books.auto_id', 'ASC')->inRandomOrder();
            }
            elseif($searchType=='popular')
            {
                $products->where('products_services_books.most_popular', '1')
                ->where('products_services_books.most_popular_start_at','<=', date('Y-m-d'))
                ->where('products_services_books.most_popular_end_at','>=', date('Y-m-d'));

                if($products->count() <= 0)
                {
                    $products = ProductsServicesBook::select('products_services_books.*')
                    ->join('users', function ($join) {
                        $join->on('products_services_books.user_id', '=', 'users.id');
                    })
                    ->where('products_services_books.status', '2')
                    ->where('products_services_books.is_sold', '0')
                    ->where('products_services_books.is_published', '1')
                    ->where('products_services_books.quantity','>', '0')
                    //->where('products_services_books.user_id', '!=', Auth::id())
                    ->where('users.user_type_id','2')
                    ->orderBy('products_services_books.view_count', 'DESC')->limit(10)
                    ->where('products_services_books.type','book')
                    ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite')
                    ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '1');
                    }])
                    ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                        $q->select('id','category_master_id','title','slug')
                            ->where('language_id', $lang_id)
                            ->where('is_parent', '0');
                    }]);
                }
            }
            if(!empty($request->per_page_record))
            {
                $productsData = $products->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $productsData = $products->get();
            }
            // $productsData['total_records'] = $products->count();
            if($request->other_function=='yes')
            {
                return $productsData;
            }
            return response(prepareResult(false, $productsData, getLangByLabelGroups('messages','messages_users_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function studentBookLandingPage(Request $request)
    {
        $content = new Request();
        $content->is_used_item = 'yes';
        $content->type = 'book';
        $content->searchType = 'promotion';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_book_promotions = $this->studentBooksFilter($content);
        
        /*$content = new Request();
        $content->is_used_item = 'yes';
        $content->type = 'book';
        $content->searchType = 'bestSelling';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_book_best_selling = $this->studentBooksFilter($content);*/
        
        $/*content = new Request();
        $content->is_used_item = 'yes';
        $content->type = 'book';
        $content->searchType = 'topRated';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_book_top_rated = $this->studentBooksFilter($content);*/
        
        $content = new Request();
        $content->is_used_item = 'yes';
        $content->type = 'book';
        $content->searchType = 'random';
        $content->per_page_record = !empty($request->per_page_record) ? $request->per_page_record : '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_book_random = $this->studentBooksFilter($content);
        
        $content = new Request();
        $content->is_used_item = 'yes';
        $content->type = 'book';
        $content->searchType = 'latest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_book_latest = $this->studentBooksFilter($content);
        
        $returnObj = [
                'books' => [
                    'student_book_promotions'    => $student_book_promotions, 
                    //'student_book_best_selling'  => $student_book_best_selling, 
                    //'student_book_top_rated'     => $student_book_top_rated,
                    'student_book_random'        => $student_book_random, 
                    'student_book_latest'        => $student_book_latest,
                    'student_book_popular'       => $student_book_random 
                ]
            ];
        
        return response(prepareResult(false, $returnObj, getLangByLabelGroups('messages','messages_users_list')), config('http_response.success'));
    }

    public function markAsSold(Request $request,$id)
    {
        $lang_id = $this->lang_id;
        if(empty($lang_id))
        {
            $lang_id = 1;
        }

        $productsServicesBook = ProductsServicesBook::find($id);
        $productsServicesBook->update([
            'is_sold' => true,
            'sold_at_student_store' => $request->sold_at_student_store,
            'days_taken' => $request->days_taken
        ]);

        $productsServicesBook = ProductsServicesBook::with('categoryMaster','subCategory','user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','user.shippingConditions','addressDetail','productImages','productTags')
        ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '1');
        }])
        ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '0');
        }])
        ->find($id);
        $productsServicesBook['category_detail'] = CategoryDetail::where('category_master_id',$productsServicesBook->category_master_id)->where('language_id',$productsServicesBook->language_id)->first();




        // Notification Start

        $title = 'Item Out Of Stock';
        $body =  $productsServicesBook->title.' is out of stock.';
        $type = 'Product Out Of Stock';
        $user_type = 'buyer';
        if($productsServicesBook->type == 'book')
        {
            $module = 'book';
        }
        else
        {
            $module = 'product_service';
        }

        $favData = FavouriteProduct::where('products_services_book_id',$id)->get(['user_id']);


        $fav_users_id = [];
        foreach ($favData as $key => $user) {
            $fav_users_id[] = $user->user_id;
        }

        $fav_users = User::whereIn('id',$fav_users_id)->get();
        pushMultipleNotification($title,$body,$fav_users,$type,true,$user_type,$module,$productsServicesBook->id,'fav');


        $cartData = CartDetail::where('products_services_book_id',$id)->get(['user_id']);
        $cart_users_id = [];
        foreach ($cartData as $key => $user) {
            $cart_users_id[] = $user->user_id;
        }

        $cart_users = User::whereIn('id',$cart_users_id)->get();
        pushMultipleNotification($title,$body,$cart_users,$type,true,$user_type,$module,$productsServicesBook->id,'cart');

        // Notification End


        return response()->json(prepareResult(false, $productsServicesBook, getLangByLabelGroups('messages','messages_products_services_book_sold')), config('http_response.success'));
    }
}
