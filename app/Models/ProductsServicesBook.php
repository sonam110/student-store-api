<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\AddressDetail;
use App\Models\CategoryMaster;
use App\Models\ProductImage;
use App\Models\ProductTag;
use App\Models\OrderItem;
use App\Models\RatingAndFeedback;
use App\Models\FavouriteProduct;
use App\Models\CartDetail;
use Auth;


class ProductsServicesBook extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id', 'language_id', 'category_master_id', 'address_detail_id', 'title', 'slug', 'meta_description', 'short_summary', 'type', 'sku', 'price', 'is_on_offer', 'discount_type', 'discount_value', 'quantity', 'description', 'attribute_details', 'condition', 'sell_type', 'service_availability', 'service_online_link', 'service_type', 'service_start_time', 'service_end_time', 'delivery_type', 'available_to', 'is_published', 'published_at', 'is_for_sale', 'sale_start_at', 'sale_end_at', 'is_promoted', 'promotion_start_at', 'promotion_end_at', 'is_deleted', 'view_count', 'avg_rating', 'status','sub_category_slug','tags','gtin_isbn','discounted_price','deposit_amount','is_used_item','item_condition','brand','most_popular','most_popular_start_at','most_popular_end_at','most_popular_no_of_days','top_selling','top_selling_start_at','top_selling_end_at','is_reward_point_applicable','reward_points','is_sold','sold_at_student_store','days_taken','meta_title','meta_keywords'
    ];


    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function categoryMaster()
    {
        return $this->belongsTo(CategoryMaster::class, 'category_master_id', 'id');
    }

    public function subCategory()
    {
        return $this->belongsTo(CategoryMaster::class, 'sub_category_slug', 'slug');
    }

    public function addressDetail()
    {
        return $this->belongsTo(AddressDetail::class, 'address_detail_id', 'id');
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class,'products_services_book_id','id');
    }

    public function coverImage()
    {
        return $this->hasOne(ProductImage::class,'products_services_book_id','id')->where('cover',true);
    }

    public function productTags()
    {
        return $this->hasMany(ProductTag::class,'products_services_book_id','id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class,'products_services_book_id','id');
    }
    public function ratings()
    {
        return $this->hasMany(RatingAndFeedback::class,'products_services_book_id','id');
    }

    public function isFavourite()
    {
        return $this->hasOne(FavouriteProduct::class,'products_services_book_id','id')->where('user_id',Auth::id())->select(['id','products_services_book_id','user_id']);
    }

    public function inCart()
    {
        return $this->hasOne(CartDetail::class,'products_services_book_id','id')->where('user_id',Auth::id())->select(['id','products_services_book_id','user_id']);
    }
}
