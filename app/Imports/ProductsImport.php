<?php

namespace App\Imports;

use App\Models\ProductsServicesBook;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use DB;
use Hash;
use Auth;
use App\Models\ProductImage;
use App\Models\ProductTag;
use App\Models\CategoryMaster;
use App\Models\User;
use Str;

class ProductsImport implements ToModel,WithHeadingRow
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
        $getUserInfo = User::select('user_type_id')->find($this->data['user_id']);
        $products = false;
        $getCat = CategoryMaster::select('vat')->find($this->data['category_master_id']);
        $user_id = $this->data['user_id'];
        $vat_percentage = $getCat->vat;
        $type = $row['type_required'];
        $discount_value = 0;
        $discountAmount = 0;
        $is_on_offer = 0;
        $discount_type = 0;
        if(@$row['discount_in_percentage_not_required']>0)
        {
            $is_on_offer = 1;
            $discount_type = 1;
            $discount_value = $row['discount_in_percentage_not_required'];
        }

        $getCommVal = updateCommissions($row['original_price_required'], $is_on_offer, $discount_type, $discount_value, $vat_percentage, $user_id, $type);

        $tag = [];
        $tagVal = [];
        if(isset($row['tags_required']) && !empty($row['tags_required']))
        {
            foreach (explode(',', $row['tags_required']) as $key => $tags) 
            {
                $tag[] = $tags;
            }
            $tagVal = json_encode($tag);
        }

        $language = [];
        $languageVal = [];
        if(isset($row['languages_required']) && !empty($row['languages_required']))
        {
            foreach (explode(',', $row['languages_required']) as $key => $lang) 
            {
                $language[] = $lang;
            }
            $languageVal = json_encode($language);
        }

        //Product
        if(@$row['type_required']=='product' && $row['original_price_required']> 0)
        {
            $products = new ProductsServicesBook;
            $products->user_id                   = $this->data['user_id'];
            $products->address_detail_id         = $this->data['address_detail_id'];
            $products->category_master_id        = $this->data['category_master_id'];
            $products->sub_category_slug         = $this->data['sub_category_slug'];
            $products->type                      = $row['type_required'];
            $products->brand                     = $row['brand_name_required'];
            $products->title                     = $row['product_name_required'];
            $products->slug                      = Str::slug($row['product_name_required']);
            if($getUserInfo->user_type_id=='2')
            {
                $products->gtin_isbn                 = $row['gtin_number_not_required'];
                $products->sku                       = $row['sku_not_required'];
                $products->is_used_item              = 1;
                $products->item_condition            = $row['item_condition_required'];
            }
            else
            {
                $products->gtin_isbn                 = $row['gtin_number_required'];
                $products->sku                       = $row['sku_required'];
            }
            
            $products->basic_price_wo_vat        = $row['original_price_required'];
            $products->price                     = $getCommVal['totalAmount'];
            $products->is_on_offer               = $is_on_offer;
            $products->discount_type             = $discount_type;
            $products->discount_value            = $discount_value;
            $products->discounted_price          = $getCommVal['discounted_price'];

            $products->vat_percentage = $vat_percentage;
            $products->vat_amount = $getCommVal['vat_amount'];
            $products->ss_commission_percent = $getCommVal['ss_commission_percent'];
            $products->ss_commission_amount = $getCommVal['ss_commission_amount'];
            $products->cc_commission_percent_all = $getCommVal['totalCCPercent'];
            $products->cc_commission_amount_all = $getCommVal['totalCCAmount'];

            $products->quantity                  = $row['quantity_required'];
            $products->short_summary             = Str::limit(strip_tags($row['product_description_required']), 250);
            $products->description               = $row['product_description_required'];
            
            $products->delivery_type             = $row['delivery_type_required'];
            $products->tags                      = $tagVal;
            $products->meta_title                = $row['product_name_required'];
            $products->meta_keywords             = $row['tags_required'];
            $products->meta_description          = Str::limit(strip_tags($row['product_description_required']), 250);
            //$products->'attribute_detail'      = $row['attribute_details'];
            $products->save();
        }

        //Service
        elseif(@$row['type_required']=='service' && $row['original_price_required']> 0)
        {
            $products = new ProductsServicesBook;
            $products->user_id                   = $this->data['user_id'];
            $products->address_detail_id         = $this->data['address_detail_id'];
            $products->category_master_id        = $this->data['category_master_id'];
            $products->sub_category_slug         = $this->data['sub_category_slug'];
            $products->type                      = $row['type_required'];
            $products->title                     = $row['service_name_required'];
            $products->slug                      = Str::slug($row['service_name_required']);
            $products->basic_price_wo_vat        = $row['original_price_required'];
            $products->price                     = $getCommVal['totalAmount'];
            $products->is_on_offer               = $is_on_offer;
            $products->discount_type             = $discount_type;
            $products->discount_value            = $discount_value;
            $products->discounted_price          = $getCommVal['discounted_price'];

            $products->vat_percentage = $vat_percentage;
            $products->vat_amount = $getCommVal['vat_amount'];
            $products->ss_commission_percent = $getCommVal['ss_commission_percent'];
            $products->ss_commission_amount = $getCommVal['ss_commission_amount'];
            $products->cc_commission_percent_all = $getCommVal['totalCCPercent'];
            $products->cc_commission_amount_all = $getCommVal['totalCCAmount'];

            $products->quantity                  = 1000;
            $products->short_summary             = Str::limit(strip_tags($row['service_description_required']), 250);
            $products->description               = $row['service_description_required'];
            $products->service_type              = $row['service_type_required'];
            $products->service_period_time       = $row['service_period_time_required'];
            $products->service_period_time_type  = $row['service_period_time_type_required'];
            $products->service_online_link       = $row['service_online_link_not_required'];
            $products->service_languages         = $languageVal;
            $products->tags                      = $tagVal;
            $products->meta_title                = $row['service_name_required'];
            $products->meta_keywords             = $row['tags_required'];
            $products->meta_description          = Str::limit(strip_tags($row['service_description_required']), 250);
            $products->save();
        }

        //Book
        elseif(@$row['type_required']=='book' && $row['original_price_required']> 0)
        {
            $products = new ProductsServicesBook;
            $products->user_id                   = $this->data['user_id'];
            $products->address_detail_id         = $this->data['address_detail_id'];
            $products->category_master_id        = $this->data['category_master_id'];
            $products->sub_category_slug         = $this->data['sub_category_slug'];
            $products->type                      = $row['type_required'];
            $products->title                     = $row['book_name_required'];
            $products->slug                      = Str::slug($row['book_name_required']);

            if($getUserInfo->user_type_id=='2')
            {
                $products->gtin_isbn                 = $row['isbn_number_not_required'];
                $products->sku                       = $row['sku_not_required'];
                $products->is_used_item              = 1;
                $products->item_condition            = $row['item_condition_required'];
            }
            else
            {
                $products->gtin_isbn                 = $row['isbn_number_required'];
                $products->sku                       = $row['sku_required'];
            }

            
            $products->basic_price_wo_vat        = $row['original_price_required'];
            $products->price                     = $getCommVal['totalAmount'];
            $products->is_on_offer               = $is_on_offer;
            $products->discount_type             = $discount_type;
            $products->discount_value            = $discount_value;
            $products->discounted_price          = $getCommVal['discounted_price'];

            $products->vat_percentage = $vat_percentage;
            $products->vat_amount = $getCommVal['vat_amount'];
            $products->ss_commission_percent = $getCommVal['ss_commission_percent'];
            $products->ss_commission_amount = $getCommVal['ss_commission_amount'];
            $products->cc_commission_percent_all = $getCommVal['totalCCPercent'];
            $products->cc_commission_amount_all = $getCommVal['totalCCAmount'];
            
            $products->quantity                  = $row['quantity_required'];
            $products->short_summary             = Str::limit(strip_tags($row['book_description_required']), 250);
            $products->description               = $row['book_description_required'];
            
            $products->deposit_amount            = @$row['deposit_amount_conditional'];
            $products->delivery_type             = $row['delivery_type_required'];
            $products->sell_type                 = $row['sell_type_required'];
            $products->author                    = $row['author_required'];
            $products->published_year            = $row['published_year_required'];
            $products->publisher                 = $row['publisher_required'];
            $products->no_of_pages               = $row['no_of_pages_required'];
            $products->suitable_age              = $row['suitable_age_required'];
            $products->dimension_length          = $row['dimension_length_required'];
            $products->dimension_width           = $row['dimension_width_required'];
            $products->dimension_height          = $row['dimension_height_required'];
            $products->weight                    = $row['weight_required'];
            $products->service_languages         = $languageVal;
            $products->tags                      = $tagVal;
            $products->meta_title                = $row['book_name_required'];
            $products->meta_keywords             = $row['tags_required'];
            $products->meta_description          = Str::limit(strip_tags($row['book_description_required']), 250);
            $products->save();
        }

        if($products)
        {
            if(isset($row['images_required']) && !empty($row['images_required']))
            {
                foreach (explode(',', $row['images_required']) as $key => $image) 
                {
                    $cover = 0;
                    if($key==0)
                    {
                        $cover = 1;
                    }
                    $productImage = new ProductImage;
                    $productImage->products_services_book_id   = $products->id;
                    $productImage->image_path                  = $image;
                    $productImage->thumb_image_path            = $image;
                    $productImage->cover                       = $cover;
                    $productImage->save();
                }
            }

            if(!empty($products->tags))
            {
                foreach (explode(',',$products->tags) as $key => $tag) 
                {
                    $allTypeTag = new ProductTag;
                    $allTypeTag->products_services_book_id  = $products->id;
                    $allTypeTag->user_id                    = $this->data['user_id'];
                    $allTypeTag->title                      = $tag;
                    $allTypeTag->type                       = isset($row['type_required']);
                    $allTypeTag->save();
                }
            }
        }
        return;
    }
}
