<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductsServicesBook;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;
use App\Models\Language;
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
use App\Models\User;
use App\Models\UserCvDetail;
use App\Models\Job;
use App\Models\JobApplication; 
use App\Models\JobTag;
use App\Http\Resources\JobResource;
use App\Http\Resources\JobApplicationResource;
use App\Models\FavouriteJob;
use App\Models\Contest;
use App\Models\ContestApplication; 
use App\Http\Resources\UserpublicResource;
use App\Models\ServiceProviderDetail; 

class LandingPageController extends Controller
{
    function __construct()
    {
        $this->lang_id = Language::select('id')->first()->id;
        if(!empty(request()->lang_id))
        {
            $this->lang_id = request()->lang_id;
        }
    }

    public function initialScreen()
    {
        $pspids = [];
        $jspids = [];
        $productsSpIds = ProductsServicesBook::distinct()->inRandomOrder()->limit(6)->get(['user_id']);
        foreach ($productsSpIds as $key => $value) {
            $pspids[] = $value->user_id;
        }
        $productSps = ServiceProviderDetail::whereIn('user_id',$pspids)->with('user:id')->get(['id','user_id','company_name','company_logo_path','company_logo_thumb_path']);

        $jobsSpIds = Job::where('user_id','!=', Auth::id())->where('job_status', '1')->distinct()->inRandomOrder()->limit(6)->get(['user_id']);
        foreach ($jobsSpIds as $key => $value) {
            $jspids[] = $value->user_id;
        }
        $jobSps = ServiceProviderDetail::whereIn('user_id',$jspids)->with('user:id')->get(['id','user_id','company_name','company_logo_path','company_logo_thumb_path']);

        $contests = Contest::where('is_published', '1')
                            // ->where('application_start_date','<=', date('Y-m-d'))
                            ->where('application_end_date','>=', date('Y-m-d'))
                            ->where('status', 'verified')
                            // ->where('user_id','!=', Auth::id())
                            ->limit(2)
                            ->inRandomOrder()
                            ->get(['id','title','subscription_fees','start_date','cover_image_path','cover_image_thumb_path']);

        $books = ProductsServicesBook::where('type','book')
                                    ->where('is_published', '1')
                                    ->where('status',2)
                                    ->where('quantity','>' ,'0')
                                    // ->where('user_id','!=', Auth::id())
                                    ->limit(3)
                                    ->inRandomOrder()
                                    ->with('coverImage')
                                    ->get(['id','title']);

        return response(prepareResult(false, ['product_sps'=>$productSps, 'job_sps'=>$jobSps, 'contests'=>$contests, 'books'=>$books], getLangByLabelGroups('messages','messages_initial_screen')), config('http_response.success'));

    }

    public function userDetail($user_id)
    {   
        $user = User::find($user_id);
        if($user)
        {
            return response()->json(prepareResult(false, new UserpublicResource($user), getLangByLabelGroups('messages','message_user_list')), config('http_response.success'));
        }    
        return response(prepareResult(true, 'Data Not Found.', 'Data Not Found.'), config('http_response.not_found'));
    }


    public function products(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            $productsServicesBooks = ProductsServicesBook::select('products_services_books.*')
                                        ->join('users', function ($join) {
                                            $join->on('products_services_books.user_id', '=', 'users.id');
                                        })
                                        ->where('products_services_books.status', '2')
                                        ->where('products_services_books.is_published', '1')
                                        ->orderBy('products_services_books.created_at','DESC')
                                        ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite','addressDetail')
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
            if($request->type=='product' || $request->type=='book')
            {
                $productsServicesBooks = $productsServicesBooks->where('quantity', '>', 0);
            }
            if($request->type)
            {
                $productsServicesBooks = $productsServicesBooks->where('type',$request->type);
            }
            if($request->user_id)
            {
                $productsServicesBooks = $productsServicesBooks->where('user_id',$request->user_id);
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
            return response(prepareResult(false, $productsServicesBooks, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
            
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function productDetail(Request $request,$id)
    {
        $lang_id = $this->lang_id;
        if(empty($lang_id))
        {
            $lang_id = Language::select('id')->first()->id;
        }

        $productsServicesBook = ProductsServicesBook::find($id);
        if(!$productsServicesBook)
        {
            return response(prepareResult(true, 'Data Not Found.', 'Data Not Found.'), config('http_response.not_found'));
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
        $productsServicesBook = ProductsServicesBook::with('categoryMaster', 'subCategory','user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path,email,contact_number,show_email,show_contact_number','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','productImages','productTags')
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
        ->withCount('ratings')->find($id);
        $productsServicesBook['favourite_products_services_book'] = $favouriteProductsServicesBook;
        $productsServicesBook['favourite_id'] = $favouriteId;
        $productsServicesBook['in_cart'] = $in_cart;
        $productsServicesBook['cart_id'] = $cartId;
        $productsServicesBook['is_chat_initiated'] = $is_chat_initiated;
        $productsServicesBook['contact_list_id'] = $contactListId;
        $productsServicesBook['language_id'] = $productsServicesBook->language;
        $productsServicesBook['address_detail'] = $productsServicesBook->addressDetail;
        $productsServicesBook['category_detail'] = CategoryDetail::where('category_master_id',$productsServicesBook->category_master_id)->where('language_id',$productsServicesBook->language_id)->first();
        return response()->json(prepareResult(false, $productsServicesBook, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
    }


    public function getServiceProviders(Request $request)
    {
        $users = User::select('id','first_name','last_name','profile_pic_path','profile_pic_thumb_path')->where('user_type_id', '3')
                        ->with('serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path')
                        ->inRandomOrder();

        if(!empty($request->per_page_record))
        {
            $users = $users->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
        }
        else
        {
            $users = $users->get();
        }
        return response(prepareResult(false, $users, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
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
            $similarProducts = ProductsServicesBook::select('id','user_id', 'category_master_id', 'address_detail_id', 'title', 'slug', 'short_summary', 'type', 'price', 'is_on_offer', 'discount_type', 'discount_value','sell_type', 'service_online_link', 'service_type','service_period_time','service_period_time_type','service_languages', 'delivery_type', 'avg_rating', 'status','discounted_price','deposit_amount','is_used_item','sub_category_slug')
            ->where('id','!=', $request->product_id)
            ->orderBy('created_at','DESC')
            ->where('status', '2')
            ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite','addressDetail')
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
            if($productsServicesBooks)
            {
                $similarProducts->where('sub_category_slug', $productsServicesBooks->sub_category_slug);
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
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function companyProductsFilter(Request $request)
    {
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
            $products = ProductsServicesBook::select('products_services_books.id','products_services_books.user_id', 'products_services_books.category_master_id', 'products_services_books.address_detail_id', 'products_services_books.title', 'products_services_books.slug', 'products_services_books.brand', 'products_services_books.short_summary', 'products_services_books.type', 'products_services_books.price', 'products_services_books.is_on_offer', 'products_services_books.discount_type', 'products_services_books.discount_value','products_services_books.sell_type', 'products_services_books.service_online_link', 'products_services_books.service_type','products_services_books.service_period_time','products_services_books.service_period_time_type','products_services_books.service_languages', 'products_services_books.delivery_type', 'products_services_books.avg_rating', 'products_services_books.status','products_services_books.discounted_price','products_services_books.deposit_amount','products_services_books.is_used_item','products_services_books.sub_category_slug')
            ->where('products_services_books.status', '2')
            ->where('products_services_books.quantity','>' ,'0')
            ->where('products_services_books.type', $type)
            ->where('products_services_books.is_published', '1')
            ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite','addressDetail')

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
                    $products->where('products_services_books.is_used_item', '1');
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
                    $min_price = $request->min_price;
                    
                    $products->whereRaw("(CASE WHEN products_services_books.is_on_offer = 1 THEN products_services_books.discounted_price >= $min_price ELSE products_services_books.price >= $min_price END)");
                    
                }
                if(!empty($request->max_price))
                {
                    $max_price = $request->max_price;
                    $products->whereRaw("(CASE WHEN products_services_books.is_on_offer = 1 THEN products_services_books.discounted_price <= $max_price ELSE products_services_books.price <= $max_price END)");
                }
                
                // if(!empty($request->min_price))
                // {
                //     $products->where('products_services_books.discounted_price', '>=', $request->min_price);
                // }
                // if(!empty($request->max_price))
                // {
                //     $products->where('products_services_books.discounted_price', '<=', $request->max_price);
                // }
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
                $products->orderBy('products_services_books.id','DESC');
            }
            elseif($searchType=='bestSelling')
            {
                $products->where('products_services_books.top_selling', '1')
                ->where('products_services_books.top_selling_start_at','<=', date('Y-m-d'))
                ->where('products_services_books.top_selling_end_at','>=', date('Y-m-d'));
                if($products->count() <= 0)
                {
                     $products = ProductsServicesBook::select('id','user_id', 'category_master_id', 'address_detail_id', 'title', 'slug', 'short_summary', 'type', 'price', 'is_on_offer', 'discount_type', 'discount_value','sell_type', 'service_online_link', 'service_type','service_period_time','service_period_time_type','service_languages', 'delivery_type', 'avg_rating', 'status','discounted_price','deposit_amount','is_used_item','sub_category_slug')
                                // ->where('products_services_books.user_id', '!=', Auth::id())
                                ->where('products_services_books.status', '2')
                                ->where('products_services_books.type', $type)
                                ->where('products_services_books.quantity','>' ,'0')
                                ->where('products_services_books.is_published', '1')
                                ->withCount('orderItems')->orderBy('order_items_count','desc')
                                ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite','addressDetail')
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
                            $products->where('products_services_books.is_used_item', '1');
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
                $products->orderBy('avg_rating','DESC');
            }
            elseif($searchType=='random')
            {
                $products->inRandomOrder();
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
            $products = ProductsServicesBook::select('products_services_books.id','products_services_books.user_id', 'products_services_books.category_master_id', 'products_services_books.address_detail_id', 'products_services_books.title', 'products_services_books.slug', 'products_services_books.short_summary', 'products_services_books.type', 'products_services_books.price', 'products_services_books.is_on_offer', 'products_services_books.discount_type', 'products_services_books.discount_value','products_services_books.sell_type', 'products_services_books.service_online_link', 'products_services_books.service_type','products_services_books.service_period_time','products_services_books.service_period_time_type','products_services_books.service_languages', 'products_services_books.delivery_type', 'products_services_books.avg_rating', 'products_services_books.status','products_services_books.discounted_price','products_services_books.deposit_amount','products_services_books.is_used_item','products_services_books.sub_category_slug')
            ->join('users', function ($join) {
                $join->on('products_services_books.user_id', '=', 'users.id');
            })
            ->where('products_services_books.status', '2')
            ->where('products_services_books.is_published', '1')
            // ->where('products_services_books.user_id', '!=', Auth::id())
            ->where('users.user_type_id','3')
            ->where('products_services_books.type','service')
            ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite','addressDetail')
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
            elseif($searchType=='products_services_books.latest')
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
                    $products = ProductsServicesBook::select('products_services_books.id','products_services_books.user_id', 'products_services_books.category_master_id', 'products_services_books.address_detail_id', 'products_services_books.title', 'products_services_books.slug', 'products_services_books.short_summary', 'products_services_books.type', 'products_services_books.price', 'products_services_books.is_on_offer', 'products_services_books.discount_type', 'products_services_books.discount_value','products_services_books.sell_type', 'products_services_books.service_online_link', 'products_services_books.service_type','products_services_books.service_period_time','products_services_books.service_period_time_type','products_services_books.service_languages', 'products_services_books.delivery_type', 'products_services_books.avg_rating', 'products_services_books.status','products_services_books.discounted_price','products_services_books.deposit_amount','products_services_books.is_used_item','products_services_books.sub_category_slug')
                    ->join('users', function ($join) {
                        $join->on('products_services_books.user_id', '=', 'users.id');
                    })
                    ->where('products_services_books.status', '2')
                    ->where('products_services_books.is_published', '1')
                    ->where('products_services_books.quantity','>', '0')
                    // ->where('products_services_books.user_id', '!=', Auth::id())
                    ->where('users.user_type_id','3')
                    ->orderBy('products_services_books.view_count', 'DESC')->limit(10)
                    ->where('products_services_books.type','service')
                    ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite','addressDetail')
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
            $products = ProductsServicesBook::select('products_services_books.id','products_services_books.user_id', 'products_services_books.category_master_id', 'products_services_books.address_detail_id', 'products_services_books.title', 'products_services_books.slug', 'products_services_books.short_summary', 'products_services_books.type', 'products_services_books.price', 'products_services_books.is_on_offer', 'products_services_books.discount_type', 'products_services_books.discount_value','products_services_books.sell_type', 'products_services_books.service_online_link', 'products_services_books.service_type','products_services_books.service_period_time','products_services_books.service_period_time_type','products_services_books.service_languages', 'products_services_books.delivery_type', 'products_services_books.avg_rating', 'products_services_books.status','products_services_books.discounted_price','products_services_books.deposit_amount','products_services_books.is_used_item','products_services_books.sub_category_slug')
            ->where('is_used_item', '1')
            ->where('quantity','>' ,'0')
                                ->where('type','product')
                                // ->where('user_id', '!=', Auth::id())
                                ->where('status', '2')
                                ->where('is_published', '1')
                                ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite','addressDetail')
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
                $products->orderBy('products_services_books.created_at','DESC');
            }
            elseif($searchType=='bestSelling')
            {
                $products->where('top_selling', '1')
                ->where('top_selling_start_at','<=', date('Y-m-d'))
                ->where('top_selling_end_at','>=', date('Y-m-d'));
                if($products->count() <= 0)
                {
                    $products = ProductsServicesBook::select('id','user_id', 'category_master_id', 'address_detail_id', 'title', 'slug', 'short_summary', 'type', 'price', 'is_on_offer', 'discount_type', 'discount_value','sell_type', 'service_online_link', 'service_type','service_period_time','service_period_time_type','service_languages', 'delivery_type', 'avg_rating', 'status','discounted_price','deposit_amount','is_used_item','sub_category_slug')
                                ->where('is_used_item', '1')
                                ->where('type','product')
                                // ->where('user_id', '!=', Auth::id())
                                ->where('status', '2')
                                ->where('is_published', '1')
                                ->where('quantity','>' ,'0')
                                ->withCount('orderItems')->orderBy('order_items_count','desc')
                                ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite','addressDetail')
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
                $products->inRandomOrder();
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
            $products = ProductsServicesBook::select('products_services_books.id','products_services_books.user_id', 'products_services_books.category_master_id', 'products_services_books.address_detail_id', 'products_services_books.title', 'products_services_books.slug', 'products_services_books.short_summary', 'products_services_books.type', 'products_services_books.price', 'products_services_books.is_on_offer', 'products_services_books.discount_type', 'products_services_books.discount_value','products_services_books.sell_type', 'products_services_books.service_online_link', 'products_services_books.service_type','products_services_books.service_period_time','products_services_books.service_period_time_type','products_services_books.service_languages', 'products_services_books.delivery_type', 'products_services_books.avg_rating', 'products_services_books.status','products_services_books.discounted_price','products_services_books.deposit_amount','products_services_books.is_used_item','products_services_books.sub_category_slug')
            ->join('users', function ($join) {
                $join->on('products_services_books.user_id', '=', 'users.id');
            })
            // ->where('products_services_books.user_id', '!=', Auth::id())
            ->where('products_services_books.status', '2')
            ->where('products_services_books.is_published', '1')
            ->where('users.user_type_id','2')
            ->where('products_services_books.type','service')
            ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite','addressDetail')
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
                    $products = ProductsServicesBook::select('products_services_books.id','products_services_books.user_id', 'products_services_books.category_master_id', 'products_services_books.address_detail_id', 'products_services_books.title', 'products_services_books.slug', 'products_services_books.short_summary', 'products_services_books.type', 'products_services_books.price', 'products_services_books.is_on_offer', 'products_services_books.discount_type', 'products_services_books.discount_value','products_services_books.sell_type', 'products_services_books.service_online_link', 'products_services_books.service_type','products_services_books.service_period_time','products_services_books.service_period_time_type','products_services_books.service_languages', 'products_services_books.delivery_type', 'products_services_books.avg_rating', 'products_services_books.status','products_services_books.discounted_price','products_services_books.deposit_amount','products_services_books.is_used_item','products_services_books.sub_category_slug')
                    ->join('users', function ($join) {
                        $join->on('products_services_books.user_id', '=', 'users.id');
                    })
                    // ->where('products_services_books.user_id', '!=', Auth::id())
                    ->where('products_services_books.status', '2')
                    ->where('products_services_books.is_published', '1')
                    ->where('products_services_books.quantity','>', '0')
                    ->where('users.user_type_id','2')
                    ->where('products_services_books.type','service')
                    ->orderBy('products_services_books.view_count', 'DESC')->limit(10)
                    ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite','addressDetail')
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
        $content->searchType = 'promotion';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_service_promotions = $this->companyServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->searchType = 'latest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_service_latest = $this->companyServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->searchType = 'popular';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_service_popular = $this->companyServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->searchType = 'topRated';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_service_top_rated = $this->companyServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->searchType = 'random';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_service_random = $this->companyServicesFilter($content);


        $content = new Request();
        $content->type = 'book';
        $content->searchType = 'promotion';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_promotions = $this->companyBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->searchType = 'latest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_latest = $this->companyBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->searchType = 'popular';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_popular = $this->companyBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->searchType = 'topRated';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_top_rated = $this->companyBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->searchType = 'random';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $company_book_random = $this->companyBooksFilter($content);
        
        
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
                    'company_book_best_selling'  => $company_book_random
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
        
        $content = new Request();
        $content->is_used_item = 'yes';
        $content->type = 'product';
        $content->searchType = 'bestSelling';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_product_best_selling = $this->studentProductsFilter($content);
        
        $content = new Request();
        $content->is_used_item = 'yes';
        $content->type = 'product';
        $content->searchType = 'topRated';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_product_top_rated = $this->studentProductsFilter($content);
        
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
        $content->searchType = 'promotion';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_service_promotions = $this->studentServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->searchType = 'latest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_service_latest = $this->studentServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->searchType = 'popular';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_service_popular = $this->studentServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->searchType = 'topRated';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_service_top_rated = $this->studentServicesFilter($content);
        
        $content = new Request();
        $content->type = 'service';
        $content->searchType = 'random';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_service_random = $this->studentServicesFilter($content);

        $content = new Request();
        $content->type = 'book';
        $content->searchType = 'promotion';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_book_promotions = $this->studentBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->searchType = 'latest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_book_latest = $this->studentBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->searchType = 'popular';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_book_popular = $this->studentBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->searchType = 'topRated';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_book_top_rated = $this->studentBooksFilter($content);
        
        $content = new Request();
        $content->type = 'book';
        $content->searchType = 'random';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $student_book_random = $this->studentBooksFilter($content);
        
        
        $returnObj = [
                'products' => [
                    'student_product_promotions'    => $student_product_promotions, 
                    'student_product_best_selling'  => $student_product_best_selling, 
                    'student_product_top_rated'     => $student_product_top_rated,
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
                    'student_book_top_rated'     => $student_book_top_rated, 
                    'student_book_random'        => $student_book_random,
                    'student_book_best_selling'  => $student_book_random
                ]
            ];
        
        return response(prepareResult(false, $returnObj, getLangByLabelGroups('messages','messages_users_list')), config('http_response.success'));
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
            $products = ProductsServicesBook::select('products_services_books.id','products_services_books.user_id', 'products_services_books.category_master_id', 'products_services_books.address_detail_id', 'products_services_books.title', 'products_services_books.slug', 'products_services_books.short_summary', 'products_services_books.type', 'products_services_books.price', 'products_services_books.is_on_offer', 'products_services_books.discount_type', 'products_services_books.discount_value','products_services_books.sell_type', 'products_services_books.service_online_link', 'products_services_books.service_type','products_services_books.service_period_time','products_services_books.service_period_time_type','products_services_books.service_languages', 'products_services_books.delivery_type', 'products_services_books.avg_rating', 'products_services_books.status','products_services_books.discounted_price','products_services_books.deposit_amount','products_services_books.is_used_item','products_services_books.sub_category_slug')
            // ->where('products_services_books.user_id', '!=', Auth::id())
            ->where('products_services_books.status', '2')
            ->where('products_services_books.quantity','>' ,'0')
            ->where('products_services_books.type', $type)
            ->where('products_services_books.is_published', '1')
            ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite','addressDetail')
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
                    $products->where('products_services_books.is_used_item', '1');
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
                $products->orderBy('products_services_books.id','DESC');
            }
            elseif($searchType=='bestSelling')
            {
                $products->where('products_services_books.top_selling', '1')
                ->where('products_services_books.top_selling_start_at','<=', date('Y-m-d'))
                ->where('products_services_books.top_selling_end_at','>=', date('Y-m-d'));
                if($products->count() <= 0)
                {
                     $products = ProductsServicesBook::select('id','user_id', 'category_master_id', 'address_detail_id', 'title', 'slug', 'short_summary', 'type', 'price', 'is_on_offer', 'discount_type', 'discount_value','sell_type', 'service_online_link', 'service_type','service_period_time','service_period_time_type','service_languages', 'delivery_type', 'avg_rating', 'status','discounted_price','deposit_amount','is_used_item','sub_category_slug')
                                // ->where('products_services_books.user_id', '!=', Auth::id())
                                ->where('products_services_books.status', '2')
                                ->where('products_services_books.type', $type)
                                ->where('products_services_books.is_published', '1')
                                ->where('products_services_books.quantity','>' ,'0')
                                ->withCount('orderItems')->orderBy('order_items_count','desc')
                                ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite','addressDetail')
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
                            $products->where('products_services_books.is_used_item', '1');
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
                $products->orderBy('avg_rating','DESC');
            }
            elseif($searchType=='random')
            {
                $products->inRandomOrder();
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
            $products = ProductsServicesBook::select('products_services_books.id','products_services_books.user_id', 'products_services_books.category_master_id', 'products_services_books.address_detail_id', 'products_services_books.title', 'products_services_books.slug', 'products_services_books.short_summary', 'products_services_books.type', 'products_services_books.price', 'products_services_books.is_on_offer', 'products_services_books.discount_type', 'products_services_books.discount_value','products_services_books.sell_type', 'products_services_books.service_online_link', 'products_services_books.service_type','products_services_books.service_period_time','products_services_books.service_period_time_type','products_services_books.service_languages', 'products_services_books.delivery_type', 'products_services_books.avg_rating', 'products_services_books.status','products_services_books.discounted_price','products_services_books.deposit_amount','products_services_books.is_used_item','products_services_books.sub_category_slug')
            ->where('is_used_item', '1')
            ->where('quantity','>' ,'0')
                                ->where('type','book')
                                // ->where('user_id', '!=', Auth::id())
                                ->where('status', '2')
                                ->where('is_published', '1')
                                ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite','addressDetail')
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
                    $products = ProductsServicesBook::select('id','user_id', 'category_master_id', 'address_detail_id', 'title', 'slug', 'short_summary', 'type', 'price', 'is_on_offer', 'discount_type', 'discount_value','sell_type', 'service_online_link', 'service_type','service_period_time','service_period_time_type','service_languages', 'delivery_type', 'avg_rating', 'status','discounted_price','deposit_amount','is_used_item','sub_category_slug')
                                ->where('is_used_item', '1')
                                ->where('type','book')
                                // ->where('user_id', '!=', Auth::id())
                                ->where('status', '2')
                                ->where('is_published', '1')
                                ->where('quantity','>' ,'0')
                                ->withCount('orderItems')->orderBy('order_items_count','desc')
                                ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','categoryMaster','subCategory','coverImage','productTags','inCart','isFavourite','addressDetail')
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
                $products->inRandomOrder();
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

    public function getJobs(Request $request)
    {
        $lang_id = $this->lang_id;
        if(empty($lang_id))
        {
            $lang_id = Language::select('id')->first()->id;
        }

        $jobs = Job::where('is_published', '1')
                        ->where('job_status', '1')
                        ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','jobTags:id,job_id,title','addressDetail','isApplied','isFavourite','categoryMaster','subCategory')
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
                        ->where('application_start_date','<=', date('Y-m-d'))
                        ->where('application_end_date','>=', date('Y-m-d'))
                        ->inRandomOrder();

        if(!empty($request->per_page_record))
        {
            $jobs = $jobs->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
        }
        else
        {
            $jobs = $jobs->get();
        }
        return response(prepareResult(false, $jobs, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
    }

    public function jobDetail(Request $request, $id)
    {
        $lang_id = $this->lang_id;
        if(empty($lang_id))
        {
            $lang_id = Language::select('id')->first()->id;
        }

        $job = Job::find($id);
        if(!$job)
        {
            return response(prepareResult(true, 'Data Not Found.', 'Data Not Found.'), config('http_response.not_found'));
        }
        if($fav = FavouriteJob::where('job_id',$job->id)->where('sa_id',Auth::id())->first())
        {
            $favouriteJob = true;
            $favouriteId = $fav->id;
        }
        else
        {
            $favouriteJob = false;
            $favouriteId = null;
        }
        if(JobApplication::where('job_id',$job->id)->where('user_id',Auth::id())->first())
        {
            $applied = true;
        }
        else
        {
            $applied = false;
        }
        $job = Job::with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path,show_email,show_contact_number','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path,avg_rating','jobTags:id,job_id,title','addressDetail','categoryMaster','subCategory')
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
        ->find($job->id);
        $job['favourite_job'] = $favouriteJob;
        $job['favourite_id'] = $favouriteId;
        $job['is_applied'] = $applied;
        $job['likes_count'] = FavouriteJob::where('job_id',$job->id)->count();
        return response()->json(prepareResult(false, $job, getLangByLabelGroups('messages','messages_job_list')), config('http_response.success'));
    }


    public function jobFilter(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            $searchType = $request->searchType; //filter, promotions, latest, closingSoon, random, criteria job
            $jobs = Job::select('sp_jobs.id','sp_jobs.user_id', 'sp_jobs.address_detail_id', 'sp_jobs.title', 'sp_jobs.slug', 'sp_jobs.short_summary', 'sp_jobs.job_type', 'sp_jobs.job_nature', 'sp_jobs.years_of_experience', 'sp_jobs.job_environment', 'sp_jobs.category_master_id','sp_jobs.sub_category_slug','sp_jobs.job_hours')
                    ->where('sp_jobs.is_published', '1')
                    ->where('sp_jobs.job_status', '1')
                    ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','jobTags:id,job_id,title','isApplied','isFavourite','addressDetail','categoryMaster','subCategory')
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
                if(!empty($request->category_master_id))
                {
                    $jobs->where('sp_jobs.category_master_id',$request->category_master_id);
                }
                if(!empty($request->sub_category_slug))
                {
                    $jobs->where('sp_jobs.sub_category_slug',$request->sub_category_slug);
                }
                if(!empty($request->job_environment))
                {
                    $jobs->where(function($query) use ($request) {
                        foreach ($request->job_environment as $key => $job_environment) {
                            if ($key === 0) {
                                $query->where('sp_jobs.job_environment', 'LIKE', '%'.$job_environment.'%');
                                continue;
                            }
                            $query->orWhere('sp_jobs.job_environment', 'LIKE', '%'.$job_environment.'%');
                        }
                    });
                }
                if(!empty($request->published_date))
                {
                    $jobs->where('sp_jobs.published_at', '<=',  date("Y-m-d", strtotime($request->published_date)))->orderBy('sp_jobs.published_at','desc');
                }
                if(!empty($request->applying_date))
                {
                    $jobs->where('sp_jobs.application_end_date', '<=',  date("Y-m-d", strtotime($request->applying_date)))->orderBy('sp_jobs.application_end_date','asc');
                }
                if(!empty($request->min_years_of_experience))
                {
                    $jobs->where('sp_jobs.years_of_experience', '>=', $request->min_years_of_experience);
                }
                if(!empty($request->max_years_of_experience))
                {
                    $jobs->where('sp_jobs.years_of_experience', '<=', $request->max_years_of_experience);
                }
                if(!empty($request->known_languages))
                {
                    $jobs->where(function($query) use ($request) {
                        foreach ($request->known_languages as $key => $known_languages) {
                            if ($key === 0) {
                                $query->where('sp_jobs.known_languages', 'LIKE', '%'.$known_languages.'%');
                                continue;
                            }
                            $query->orWhere('sp_jobs.known_languages', 'LIKE', '%'.$known_languages.'%');
                        }
                    });
                }
                if(!empty($request->job_type))
                {
                    $jobs->where(function($query) use ($request) {
                        foreach ($request->job_type as $key => $job_type) {
                            if ($key === 0) {
                                $query->where('sp_jobs.job_type', 'LIKE', '%'.$job_type.'%');
                                continue;
                            }
                            $query->orWhere('sp_jobs.job_type', 'LIKE', '%'.$job_type.'%');
                        }
                    });
                }
                if(!empty($request->job_tags))
                {
                    $jobs->join('job_tags', function ($join) {
                        $join->on('sp_jobs.id', '=', 'job_tags.job_id');
                    });
                    $jobs->groupBy('sp_jobs.id')->where(function($query) use ($request) {
                        foreach ($request->job_tags as $key => $job_tags) {
                            if ($key === 0) {
                                $query->where('job_tags.title', 'LIKE', '%'.$job_tags.'%');
                                continue;
                            }
                            $query->orWhere('job_tags.title', 'LIKE', '%'.$job_tags.'%');
                        }
                    });
                }
                if(!empty($request->search_title))
                {
                    $jobs->where('sp_jobs.title', 'LIKE', '%'.$request->search_title.'%');
                }

                //future: distance filter implement
                /*if(!empty($request->distance))
                {
                    $jobs->where('distance', $request->distance);
                }*/
                if(!empty($request->city))
                {
                    $jobs->join('address_details', function ($join) use ($request) {
                        $join->on('sp_jobs.address_detail_id', '=', 'address_details.id')
                        ->where(function($query) use ($request) {
                            foreach ($request->city as $key => $city) {
                                if ($key === 0) {
                                    $query->where('address_details.full_address', 'LIKE', '%'.$city.'%');
                                    continue;
                                }
                                $query->orWhere('address_details.full_address', 'LIKE', '%'.$city.'%');
                            }
                        });
                        //->whereIn('city', $request->city);
                    });
                }
                // $jobs->where('sp_jobs.application_start_date','<=', date('Y-m-d'))
                    // ->where('sp_jobs.application_end_date','>=', date('Y-m-d'));

                    $jobs->where('sp_jobs.application_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='promotions')
            {
                $jobs->where('sp_jobs.is_promoted', '1')
                    ->where('sp_jobs.promotion_start_date','<=', date('Y-m-d'))
                    ->where('sp_jobs.promotion_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='latest')
            {
                $jobs->orderBy('sp_jobs.created_at','DESC')
                    // ->where('sp_jobs.application_start_date','<=', date('Y-m-d'))
                    ->where('sp_jobs.application_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='closingSoon')
            {
                $jobs->whereBetween('sp_jobs.application_end_date', [date('Y-m-d'), date('Y-m-d', strtotime("+2 days"))]);
            }
            elseif($searchType=='random')
            {
                // $jobs->where('sp_jobs.application_start_date','<=', date('Y-m-d'))
                    // ->where('sp_jobs.application_end_date','>=', date('Y-m-d'))
                    // ->inRandomOrder();
                $jobs->where('sp_jobs.application_end_date','>=', date('Y-m-d'))
                    ->inRandomOrder();
            }
            elseif($searchType=='criteria')
            {
                //Job env, work-exp, city, title, skills, 
                $userCvDetail = UserCvDetail::where('user_id', Auth::id())->first();
                if(!$userCvDetail)
                {
                    return response()->json(prepareResult(true, 'CV not updated', getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
                }
                $jobAllArray = array();

                //Job env
                if(!empty($userCvDetail->preferred_job_env))
                {
                    $jobsIdsMatch = Job::select('sp_jobs.id')
                        ->where('sp_jobs.is_published', '1')
                        // ->where('sp_jobs.application_start_date','<=', date('Y-m-d'))
                        ->where('sp_jobs.application_end_date','>=', date('Y-m-d'));
                    $job_environments = json_decode($userCvDetail->preferred_job_env, true);
                    $jobsIdsMatch->where(function($query) use ($job_environments) {
                        foreach ($job_environments as $key => $job_environment) {
                            if ($key === 0) {
                                $query->where('sp_jobs.job_environment', 'LIKE', '%'.$job_environment.'%');
                                continue;
                            }
                            $query->orWhere('sp_jobs.job_environment', 'LIKE', '%'.$job_environment.'%');
                        }
                    });
                }
                $jobEnvs = $jobsIdsMatch->get();

                foreach ($jobEnvs as $key => $jobId) {
                    $jobAllArray[] = $jobId->id;
                }

                //work-exp
                if(!empty($userCvDetail->total_experience))
                {
                    $jobsIdsMatch = Job::select('sp_jobs.id')
                        ->where('sp_jobs.is_published', '1')
                        // ->where('sp_jobs.application_start_date','<=', date('Y-m-d'))
                        ->where('sp_jobs.application_end_date','>=', date('Y-m-d'));

                    $jobsIdsMatch->where('years_of_experience', '<=', $userCvDetail->total_experience);
                    $jobTotalExps = $jobsIdsMatch->get();
                
                    foreach ($jobTotalExps as $key => $jobId) {
                        $jobAllArray[] = $jobId->id;
                    }
                }

                //city
                if($userCvDetail->user->defaultAddress)
                {
                    $jobsIdsMatch = Job::select('sp_jobs.id')
                        ->where('sp_jobs.is_published', '1')
                        // ->where('sp_jobs.application_start_date','<=', date('Y-m-d'))
                        ->where('sp_jobs.application_end_date','>=', date('Y-m-d'));

                    $cityName = $userCvDetail->user->defaultAddress->city;
                    $jobsIdsMatch->with(['addressDetail' => function($q) use ($cityName) {
                        $q->where('sp_jobs.city', $cityName);
                    }]);

                    $jobCities = $jobsIdsMatch->get();
                
                    foreach ($jobCities as $key => $jobId) {
                        $jobAllArray[] = $jobId->id;
                    }
                }

                //skills
                if(!empty($userCvDetail->key_skills))
                {
                    $jobsIdsMatch = Job::select('sp_jobs.id')
                        ->where('sp_jobs.is_published', '1')
                        // ->where('sp_jobs.application_start_date','<=', date('Y-m-d'))
                        ->where('sp_jobs.application_end_date','>=', date('Y-m-d'));

                    $key_skills = json_decode($userCvDetail->key_skills, true);

                    $jobsIdsMatch->join('job_tags', function ($join) {
                        $join->on('sp_jobs.id', '=', 'job_tags.job_id');
                    });
                    $jobsIdsMatch->where(function($query) use ($key_skills) {
                        foreach ($key_skills as $key => $skill) {
                            if ($key === 0) {
                                $query->where('job_tags.title', 'LIKE', '%'.$skill.'%');
                                continue;
                            }
                            $query->orWhere('job_tags.title', 'LIKE', '%'.$skill.'%');
                        }
                    });

                    $jobSkills = $jobsIdsMatch->get();
                
                    foreach ($jobSkills as $key => $jobId) {
                        $jobAllArray[] = $jobId->id;
                    }
                }

                //title
                if(!empty($userCvDetail->title))
                {
                    $jobsIdsMatch = Job::select('sp_jobs.id')
                        ->where('sp_jobs.is_published', '1')
                        // ->where('sp_jobs.application_start_date','<=', date('Y-m-d'))
                        ->where('sp_jobs.application_end_date','>=', date('Y-m-d'));

                    $cvTitle = $userCvDetail->title;
                    $jobsIdsMatch->where(function($query) use ($cvTitle) {
                        $query->where('sp_jobs.title', 'LIKE', '%'.$cvTitle.'%');
                    });

                    $jobTitles = $jobsIdsMatch->get();
                
                    foreach ($jobTitles as $key => $jobId) {
                        $jobAllArray[] = $jobId->id;
                    }
                }

                $actualArray = array();
                $allIds = array_count_values($jobAllArray);
                foreach ($allIds as $key => $value) {
                    if($value>2)
                    {
                        $actualArray[] = $key;
                    }
                }
                $jobs = Job::select('sp_jobs.*')
                        ->whereIn('sp_jobs.id',$actualArray)
                        ->where('sp_jobs.is_published', '1')
                        ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','jobTags:id,job_id,title');
            }
            if(!empty($request->per_page_record))
            {
                $jobsData = $jobs->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $jobsData = $jobs->get();
            }
            if($request->other_function=='yes')
            {
                return $jobsData;
            }
            return response(prepareResult(false, $jobsData, getLangByLabelGroups('messages','messages_job_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function jobLandingPage(Request $request)
    {
        $content = new Request();
        $content->searchType = 'promotions';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $jobs_promotions = $this->jobFilter($content);
        
        $content = new Request();
        $content->searchType = 'latest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $jobs_latest = $this->jobFilter($content);
        
        $content = new Request();
        $content->searchType = 'closingSoon';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $jobs_closing_soon = $this->jobFilter($content);
        
        $content = new Request();
        $content->searchType = 'random';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $jobs_random = $this->jobFilter($content);
        
        
        
        $returnObj = [
                'jobs' => [
                    'jobs_promotions'       => $jobs_promotions, 
                    'jobs_closing_soon'     => $jobs_closing_soon,
                    'jobs_random'           => $jobs_random, 
                    'jobs_latest'           => $jobs_latest,
                ]
            ];
        
        return response(prepareResult(false, $returnObj, getLangByLabelGroups('messages','messages_jobs_list')), config('http_response.success'));
    }


    public function getContests(Request $request)
    {
        $lang_id = $this->lang_id;
        if(empty($lang_id))
        {
            $lang_id = Language::select('id')->first()->id;
        }

        $contests = Contest::where('is_published', '1')
                        ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','cancellationRanges','isApplied','categoryMaster','subCategory')
                        ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                            $q->select('id','category_master_id','title','slug')
                                ->where('language_id', $lang_id)
                                ->where('is_parent', '1');
                        }])
                        ->withCount('contestApplications')
                        ->where('status', 'verified')
                        ->where('application_start_date','<=', date('Y-m-d'))
                        ->where('application_end_date','>=', date('Y-m-d'))
                        ->inRandomOrder();

        if(!empty($request->per_page_record))
        {
            $contests = $contests->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
        }
        else
        {
            $contests = $contests->get();
        }
        return response(prepareResult(false, $contests, getLangByLabelGroups('messages','messages_products_services_book_list')), config('http_response.success'));
    }

    public function contestDetail(Request $request, $id)
    {
        $lang_id = $this->lang_id;
        if(empty($lang_id))
        {
            $lang_id = Language::select('id')->first()->id;
        }

        $contest = Contest::find($id);
        if($contest)
        {
            if(ContestApplication::where('application_status','!=','canceled')->where('contest_id',$contest->id)->where('user_id',Auth::id())->first())
            {
                $applied = true;
            }
            else
            {
                $applied = false;
            }
            $contest = Contest::with('user:id,first_name,last_name,profile_pic_path,show_email,show_contact_number','cancellationRanges','categoryMaster','subCategory','user.defaultAddress','user.serviceProviderDetail:id,user_id,company_logo_path,company_logo_thumb_path')
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
            ->with(['serviceProviderType.serviceProviderTypeDetail' => function($q) use ($lang_id) {
                $q->select('id','service_provider_type_id','title','slug')
                    ->where('language_id', $lang_id);
            }])
            ->with(['registrationType.registrationTypeDetail' => function($q) use ($lang_id) {
                $q->select('id','registration_type_id','title','slug')
                    ->where('language_id', $lang_id);
            }])
            ->withCount('contestApplications')
            ->find($contest->id);
            $contest['is_applied'] = $applied;
            $contest['latitude'] = $contest->addressDetail? $contest->addressDetail->latitude:null;
            $contest['longitude'] = $contest->addressDetail?$contest->addressDetail->longitude:null;
            return response()->json(prepareResult(false, $contest, getLangByLabelGroups('messages','messages_contest_list')), config('http_response.success'));
        }
        return response()->json(prepareResult(true, 'Record Not found', getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    }


    public function contestFilter(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;
            if(empty($lang_id))
            {
                $lang_id = Language::select('id')->first()->id;
            }

            $searchType = $request->searchType; //filter, promotions, latest, closingSoon, random, criteria contest
            
            $user_type_id = '3';
            if($request->user_type == 'student')
            {
                $user_type_id = '2';
            }

            $contests = Contest::select('contests.*')
                    ->join('users', function ($join) use ($user_type_id) {
                        $join->on('contests.user_id', 'users.id')
                        ->where('users.user_type_id', $user_type_id);
                    })
                    // ->where('contests.user_id', '!=', Auth::id())
                    ->where('contests.type', $request->type)
                    ->where('contests.is_published', '1')
                    ->where('contests.status', 'verified')
                    ->where('application_start_date','<=', date('Y-m-d'))
                    ->where('application_end_date','>=', date('Y-m-d'))
                    ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','categoryMaster','subCategory','cancellationRanges','isApplied','categoryMaster','subCategory')
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
            if($searchType=='only_category_filter')
            {
                if(!empty($request->category_master_id))
                {
                    $contests->where('category_master_id',$request->category_master_id);
                }
            }
            elseif($searchType=='filter')
            {
                if(!empty($request->category_master_id))
                {
                    $contests->where('category_master_id',$request->category_master_id);
                }
                if(!empty($request->sub_category_slug))
                {
                    $contests->where('sub_category_slug',$request->sub_category_slug);
                }

                if(!empty($request->contest_type))
                {
                    $contests->where('contest_type', 'LIKE', '%'.$request->contest_type.'%');
                }
                if(!empty($request->event_type))
                {
                    $contests->where('event_type', 'LIKE', '%'.$request->event_type.'%');
                }
                if(!empty($request->mode))
                {
                    $contests->where('mode', $request->mode);
                }
                if(!empty($request->published_date))
                {
                    $contests->where('contests.published_at', '<=',  date("Y-m-d", strtotime($request->published_date)))->orderBy('contests.published_at','desc');
                }
                if(!empty($request->applying_date))
                {
                    $contests->where('contests.application_end_date', '<=',  date("Y-m-d", strtotime($request->applying_date)))->orderBy('contests.application_end_date','asc');
                }

                if(!empty($request->start_date))
                {
                    $contests->where('start_date', '>=', date('Y-m-d', strtotime($request->start_date)));
                }
                if(!empty($request->end_date))
                {
                    $contests->where('start_date', '<=', date('Y-m-d', strtotime($request->end_date)));
                }

                if(!empty($request->free_subscription))
                {
                    $contests->where('is_free', 1);
                }

                if(!empty($request->free_cancellation))
                {
                    $contests->where('use_cancellation_policy' , 0);
                }

                if(!empty($request->available_for))
                {
                    $contests->where('available_for', $request->available_for);
                }

                if(!empty($request->search_title))
                {
                    $contests->where('title', 'LIKE', '%'.$request->search_title.'%');
                }

                //future: distance filter implement
                /*if(!empty($request->distance))
                {
                    $contests->where('distance', $request->distance);
                }*/
                if(!empty($request->city))
                {
                    $contests->where('target_city','LIKE', '%'.$request->city.'%');
                }
                if(!empty($request->user_id))
                {
                    $contests->where('user_id', $request->user_id);
                }
                $contests->where('application_start_date','<=', date('Y-m-d'))
                    ->where('application_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='promotions')
            {
                // $contests->where('is_promoted', '1')
                //     ->where('promotion_start_date','<=', date('Y-m-d'))
                //     ->where('promotion_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='most-popular')
            {
                $contests->where('application_start_date','<=', date('Y-m-d'))
                    ->where('application_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='latest')
            {
                $contests->orderBy('created_at','DESC')
                    ->where('application_start_date','<=', date('Y-m-d'))
                    ->where('application_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='closingSoon')
            {
                $contests->whereBetween('application_end_date', [date('Y-m-d', strtotime("-1 days")), date('Y-m-d', strtotime("+2 days"))]);
            }
            elseif($searchType=='random')
            {
                $contests->where('application_start_date','<=', date('Y-m-d'))
                    ->where('application_end_date','>=', date('Y-m-d'))
                    ->inRandomOrder();
            }

            if(!empty($request->per_page_record))
            {
                $contestsData = $contests->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $contestsData = $contests->get();
            }
            if($request->other_function=='yes')
            {
                return $contestsData;
            }
            return response(prepareResult(false, $contestsData, getLangByLabelGroups('messages','messages_contest_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function contestLandingPage(Request $request)
    {
        $content = new Request();
        $content->searchType = 'promotions';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_promotions = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'most-popular';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_most_popular = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'latest';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_latest = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'closingSoon';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_closing_soon = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'random';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_random = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'promotions';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_promotions = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'most-popular';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_most_popular = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'latest';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_latest = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'closingSoon';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_closing_soon = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'random';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_random = $this->contestFilter($content);
        
        $returnObj = [
                'contests' => [
                    'contests_promotions'       => $contests_promotions, 
                    'contests_closing_soon'     => $contests_closing_soon,
                    'contests_random'           => $contests_random, 
                    'contests_latest'           => $contests_latest,
                    'contests_most_popular'     => $contests_most_popular, 
                ],
                'events' => [
                    'events_promotions'     => $events_promotions, 
                    'events_closing_soon'   => $events_closing_soon,
                    'events_random'         => $events_random, 
                    'events_latest'         => $events_latest,
                    'events_most_popular'   => $events_most_popular, 
                ]
            ];
        
        return response(prepareResult(false, $returnObj, getLangByLabelGroups('messages','messages_contests_list')), config('http_response.success'));
    }

    public function studentContestLandingPage(Request $request)
    {
        $content = new Request();
        $content->searchType = 'promotions';
        $content->user_type = 'student';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_promotions = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'most-popular';
        $content->user_type = 'student';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_most_popular = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'latest';
        $content->user_type = 'student';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_latest = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'closingSoon';
        $content->user_type = 'student';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_closing_soon = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'random';
        $content->user_type = 'student';
        $content->type = 'contest';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $contests_random = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'promotions';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_promotions = $this->contestFilter($content);

        $content = new Request();
        $content->searchType = 'most-popular';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_most_popular = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'latest';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_latest = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'closingSoon';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_closing_soon = $this->contestFilter($content);
        
        $content = new Request();
        $content->searchType = 'random';
        $content->user_type = 'student';
        $content->type = 'event';
        $content->per_page_record = '5';
        $content->other_function = 'yes';
        $content->lang_id = $request->lang_id;
        $events_random = $this->contestFilter($content);
        
        $returnObj = [

                'contests' => [
                    'contests_promotions'       => $contests_promotions, 
                    'contests_closing_soon'     => $contests_closing_soon,
                    'contests_random'           => $contests_random, 
                    'contests_latest'           => $contests_latest,
                    'contests_most_popular'     => $contests_most_popular, 
                ],
                'events' => [
                    'events_promotions'     => $events_promotions, 
                    'events_closing_soon'   => $events_closing_soon,
                    'events_random'         => $events_random, 
                    'events_latest'         => $events_latest,
                    'events_most_popular'   => $events_most_popular, 
                ]
            ];
        
        return response(prepareResult(false, $returnObj, getLangByLabelGroups('messages','messages_contests_list')), config('http_response.success'));
    }

}
