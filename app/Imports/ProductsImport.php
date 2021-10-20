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
        $getCat = CategoryMaster::select('vat')->find($this->data['category_master_id']);
        $discountAmount = 0;
        if($row['discount_type']=='fixed_amount') {
            $discountAmount = $row['actual_price'] - $row['discount_value'];
        } elseif($row['discount_type']=='percentage') {
            $discountAmount = $row['actual_price'] - (($row['actual_price'] * $row['discount_value'])/100);
        }

        if($row['type']=='product') && $row['actual_price']> 0)
        {
            $products = ProductsServicesBook::create([
                'user_id'                   => $this->data['user_id'],
                'address_detail_id'         => $this->data['address_detail_id'],
                'category_master_id'        => $this->data['category_master_id'],
                'sub_category_slug'         => $this->data['sub_category_slug'],
                'type'                      => $row['type'],
                'brand'                     => $row['brand_name'],
                'title'                     => $row['product_name'],
                'slug'                      => Str::slug($row['product_name']),
                'gtin_isbn'                 => $row['gtin_number'],
                'sku'                       => $row['sku'],
                'basic_price_wo_vat'        => $row['actual_price'] - (($row['actual_price'] * $getCat->vat)/100),
                'price'                     => $row['actual_price'],
                'is_on_offer'               => ($row['is_on_offer']=='Yes') ? '1' : '0',
                'discount_type'             => $row['discount_type'],
                'discount_value'            => $row['discount_value'],
                'discounted_price'          => $discountAmount,
                'quantity'                  => ($row['type']=='service') ? 1000 : $row['quantity'],
                'short_summary'             => $row['short_summary'],
                'description'               => $row['description'],
                // 'attribute_details'          => $row['attribute_details'],
                'meta_description'          => $row['meta_description'],
                'sell_type'                 => $row['sell_type'],
                'deposit_amount'            => $row['deposit_amount'],
                'is_used_item'              => (Auth::user()->user_type_id==2) ? 1 : 0,
                'item_condition'            => $row['item_condition'],
                // 'author'                     => $row['author'],
                // 'published_year'             => $row['published_year'],
                // 'publisher'                  => $row['publisher'],
                // 'language'                   => $row['language'],
                // 'no_of_pages'                => $row['no_of_pages'],
                // 'suitable_age'               => $row['suitable_age'],
                // 'book_cover'                 => $row['book_cover'],
                // 'dimension_length'           => $row['dimension_length'],
                // 'dimension_width'            => $row['dimension_width'],
                // 'dimension_height'           => $row['dimension_height'],
                // 'weight'                     => $row['weight'],
                // 'service_type'               => $row['service_type'],
                // 'service_period_time'        => $row['service_period_time'],
                // 'service_period_time_type'   => $row['service_period_time_type'],
                // 'service_online_link'        => $row['service_online_link'],
                // 'service_languages'          => $row['service_languages'],

                'delivery_type'             => $row['delivery_type'],
                'tags'                      => json_encode($row['tags'])
            ]);

            foreach ($row['images'] as $key => $image) 
            {
                $productImage = new ProductImage;
                $productImage->products_services_book_id   = $products->id;
                $productImage->image_path                  = $image['file_name'];
                $productImage->thumb_image_path            = $image['file_name'];
                $productImage->cover                       = $image['cover'];
                $productImage->save();
            }

            if($products->tags)
            {
                foreach (explode(',',$products->tags) as $key => $tag) 
                {
                    $allTypeTag = new ProductTag;
                    $allTypeTag->products_services_book_id  = $products->id;
                    $allTypeTag->user_id                    = Auth::id();
                    $allTypeTag->title                      = $tag;
                    $allTypeTag->type                       = $row['type'];
                    $allTypeTag->save();
                }
            }
        }
        return;
    }
}
