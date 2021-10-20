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
        $products = false;
        $getCat = CategoryMaster::select('vat')->find($this->data['category_master_id']);
        $discountAmount = 0;
        $discountValue = 0;
        if($row['discount_type']=='fixed_amount') {
            $discountAmount = $row['actual_price'] - $row['discount_value'];
            $discountValue = $row['discount_value'];
        } elseif($row['discount_type']=='percentage') {
            $discountAmount = $row['actual_price'] - (($row['actual_price'] * $row['discount_value'])/100);
            $discountValue = $row['discount_value'];
        }

        $tag = [];
        $tagVal = [];
        if(isset($row['tags']) && !empty($row['tags']))
        {
            foreach (explode(',', $row['tags']) as $key => $tags) 
            {
                $tag[] = $tags;
            }
            $tagVal = json_encode($tag);
        }

        $language = [];
        $languageVal = [];
        if(isset($row['service_languages']) && !empty($row['service_languages']))
        {
            foreach (explode(',', $row['service_languages']) as $key => $lang) 
            {
                $language[] = $lang;
            }
            $languageVal = json_encode($language);
        }

        //Product
        if($row['type']=='product' && $row['actual_price']> 0)
        {
            $products = new ProductsServicesBook;
            $products->user_id                   = $this->data['user_id'];
            $products->address_detail_id         = $this->data['address_detail_id'];
            $products->category_master_id        = $this->data['category_master_id'];
            $products->sub_category_slug         = $this->data['sub_category_slug'];
            $products->type                      = $row['type'];
            $products->brand                     = $row['brand_name'];
            $products->title                     = $row['product_name'];
            $products->slug                      = Str::slug($row['product_name']);
            $products->gtin_isbn                 = $row['gtin_number'];
            $products->sku                       = $row['sku'];
            $products->basic_price_wo_vat        = $row['actual_price'] - (($row['actual_price'] * $getCat->vat)/100);
            $products->price                     = $row['actual_price'];
            $products->is_on_offer               = ($row['is_on_offer']=='Yes') ? 1 : 0;
            $products->discount_type             = $row['discount_type'];
            $products->discount_value            = $discountValue;
            $products->discounted_price          = $discountAmount;
            $products->quantity                  = $row['quantity'];
            $products->short_summary             = Str::limit(strip_tags($row['product_description']), 250);
            $products->description               = $row['product_description'];
            $products->is_used_item              = $this->data['is_used_item'];
            $products->item_condition            = ($this->data['is_used_item'] == 1) ? @$row['item_condition'] : null;
            $products->delivery_type             = $row['delivery_type'];
            $products->tags                      = $tagVal;
            $products->meta_title                = $row['product_name'];
            $products->meta_keywords             = $row['tags'];
            $products->meta_description          = Str::limit(strip_tags($row['product_description']), 250);
            //$products->'attribute_detail'      = $row['attribute_details'];
            $products->save();
        }

        //Service
        elseif($row['type']=='service' && $row['actual_price']> 0)
        {
            $products = new ProductsServicesBook;
            $products->user_id                   = $this->data['user_id'];
            $products->address_detail_id         = $this->data['address_detail_id'];
            $products->category_master_id        = $this->data['category_master_id'];
            $products->sub_category_slug         = $this->data['sub_category_slug'];
            $products->type                      = $row['type'];
            $products->title                     = $row['service_name'];
            $products->slug                      = Str::slug($row['service_name']);
            $products->basic_price_wo_vat        = $row['actual_price'] - (($row['actual_price'] * $getCat->vat)/100);
            $products->price                     = $row['actual_price'];
            $products->is_on_offer               = ($row['is_on_offer']=='Yes') ? 1 : 0;
            $products->discount_type             = $row['discount_type'];
            $products->discount_value            = $discountValue;
            $products->discounted_price          = $discountAmount;
            $products->quantity                  = 1000;
            $products->short_summary             = Str::limit(strip_tags($row['service_description']), 250);
            $products->description               = $row['service_description'];
            $products->service_type              = $row['service_type'];
            $products->service_period_time       = $row['service_period_time'];
            $products->service_period_time_type  = $row['service_period_time_type'];
            $products->service_online_link       = $row['service_online_link'];
            $products->service_languages         = $languageVal;
            $products->tags                      = $tagVal;
            $products->meta_title                = $row['service_name'];
            $products->meta_keywords             = $row['tags'];
            $products->meta_description          = Str::limit(strip_tags($row['service_description']), 250);
            $products->save();
        }

        //Book
        elseif($row['type']=='service' && $row['actual_price']> 0)
        {
            $products = new ProductsServicesBook;
            $products->user_id                   = $this->data['user_id'];
            $products->address_detail_id         = $this->data['address_detail_id'];
            $products->category_master_id        = $this->data['category_master_id'];
            $products->sub_category_slug         = $this->data['sub_category_slug'];
            $products->type                      = $row['type'];
            $products->title                     = $row['service_name'];
            $products->slug                      = Str::slug($row['service_name']);
            $products->basic_price_wo_vat        = $row['actual_price'] - (($row['actual_price'] * $getCat->vat)/100);
            $products->price                     = $row['actual_price'];
            $products->is_on_offer               = ($row['is_on_offer']=='Yes') ? 1 : 0;
            $products->discount_type             = $row['discount_type'];
            $products->discount_value            = $discountValue;
            $products->discounted_price          = $discountAmount;
            $products->quantity                  = 1000;
            $products->short_summary             = Str::limit(strip_tags($row['service_description']), 250);
            $products->description               = $row['service_description'];
            $products->service_type              = $row['service_type'];
            $products->service_period_time       = $row['service_period_time'];
            $products->service_period_time_type  = $row['service_period_time_type'];
            $products->service_online_link       = $row['service_online_link'];
            $products->service_languages         = $languageVal;
            $products->tags                      = $tagVal;
            $products->meta_title                = $row['service_name'];
            $products->meta_keywords             = $row['tags'];
            $products->meta_description          = Str::limit(strip_tags($row['service_description']), 250);
            $products->save();
        }

        if($products)
        {
            if(isset($row['images']) && !empty($row['images']))
            {
                foreach (explode(',', $row['images']) as $key => $image) 
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
                    $allTypeTag->type                       = isset($row['type']);
                    $allTypeTag->save();
                }
            }
        }
        return;
    }
}
