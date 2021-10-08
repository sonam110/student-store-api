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
        $products = ProductsServicesBook::create([
            'user_id'              		=> Auth::id(),
            'address_detail_id'    		=> $this->data['address_detail_id'],
            'category_master_id'   		=> $this->data['category_master_id'],
            'sub_category_slug'      	=> $this->data['sub_category_slug'],
            'type'                 		=> $row['type'],
            'brand'                		=> $row['brand'],
            'sku'                  		=> $row['sku'],
            'gtin_isbn'            		=> $row['gtin_isbn'],
            'title'                		=> $row['title'],
            'slug'                		=> Str::slug($row['title']),
            'basic_price_wo_vat'        => $row['price'] - (($row['price'] * $getCat->vat)/100),
            'price'                		=> $row['price'],
            'discounted_price'     		=> $row['discounted_price'],
            'is_on_offer'          		=> $row['is_on_offer'],
            'discount_type'        		=> $row['discount_type'],
            'discount_value'       		=> $row['discount_value'],
            'quantity'             		=> $row['quantity'],
            'short_summary'        		=> $row['short_summary'],
            'description'          		=> $row['description'],
            // 'attribute_details'    		=> $row['attribute_details'],
            'meta_description'     		=> $row['meta_description'],
            'sell_type'            		=> $row['sell_type'],
            'deposit_amount'       		=> $row['deposit_amount'],
            'is_used_item'         		=> $row['is_used_item'],
            'item_condition'       		=> $row['item_condition'],
            // 'author'               		=> $row['author'],
            // 'published_year'       		=> $row['published_year'],
            // 'publisher'            		=> $row['publisher'],
            // 'language'             		=> $row['language'],
            // 'no_of_pages'          		=> $row['no_of_pages'],
            // 'suitable_age'         		=> $row['suitable_age'],
            // 'book_cover'           		=> $row['book_cover'],
            // 'dimension_length'     		=> $row['dimension_length'],
            // 'dimension_width'      		=> $row['dimension_width'],
            // 'dimension_height'     		=> $row['dimension_height'],
            // 'weight'               		=> $row['weight'],
            // 'service_type'         		=> $row['service_type'],
            // 'service_period_time'      	=> $row['service_period_time'],
            // 'service_period_time_type' 	=> $row['service_period_time_type'],
            // 'service_online_link'      	=> $row['service_online_link'],
            // 'service_languages'        	=> $row['service_languages'],

            'delivery_type'        		=> $row['delivery_type'],
            'tags'                     	=> json_encode($row['tags'])
        ]);

        // foreach ($row['images'] as $key => $image) 
        // {
        //     $productImage = new ProductImage;
        //     $productImage->products_services_book_id   = $products->id;
        //     $productImage->image_path                  = $image['file_name'];
        //     $productImage->thumb_image_path            = env('CDN_DOC_THUMB_URL').basename($image['file_name']);
        //     $productImage->cover                       = $image['cover'];
        //     $productImage->save();
        // }

        if($products->tags)
        {
        	foreach (explode(',',$products->tags) as $key => $tag) 
        	{
        	    $allTypeTag = new ProductTag;
        	    $allTypeTag->products_services_book_id  = $products->id;
        	    $allTypeTag->user_id                  	= Auth::id();
        	    $allTypeTag->title                    	= $tag;
        	    $allTypeTag->type                     	= $row['type'];
        	    $allTypeTag->save();
        	}
        }

        
        return;
    }
}
