<?php

namespace App\Exports;

use App\Models\ProductsServicesBook;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Auth;
use mervick\aesEverywhere\AES256;

class ProductsExport implements FromCollection, WithHeadings
{
	use Exportable;

	public $requestData;

	public function __construct($requestData)
	{
		$this->requestData = $requestData;
	}
    /**
    * @return \Illuminate\Support\Collection
    */

    public function headings(): array {
    	return [
    		'SNO',
            'id',
            'user_name',
            'category_master',
            'title',
            'slug',
            'meta_description',
            'short_summary',
            'type',
            'sku',
            'basic_price_wo_vat',
            'price',
            'is_on_offer',
            'discount_type',
            'discount_value',
            'quantity',
            'description',
            'sell_type',
            'service_availability',
            'service_online_link',
            'service_type',
            'service_start_time',
            'service_end_time',
            'delivery_type',
            'available_to',
            'is_published',
            'published_at',
            'is_for_sale',
            'is_promoted',
            'promotion_start_at',
            'promotion_end_at',
            'view_count',
            'avg_rating',
            'status',
            'gtin_isbn',
            'discounted_price',
            'deposit_amount',
            'is_used_item',
            'item_condition',
            'brand',
            'most_popular',
            'is_reward_point_applicable',
            'reward_points',
            'created_at'
    	];
    }

    public function collection()
    {
    	$products = ProductsServicesBook::orderBy('created_at','desc');
    	if(!empty($this->requestData['ids']))
    	{
    		$products = $products->whereIn('id',$this->requestData['ids']);
    	}
    	if(!empty($this->requestData['type']))
    	{
    		$products = $products->where('type',$this->requestData['type']);
    	}

        if($this->requestData['auth_applicable'] == true)
        {
            $products = $products->where('user_id',Auth::id());
        }
    	$products = $products->with('productImages')->get();

    	// return $products;

    	return $products->map(function ($data, $key) {
            $images = null;
            foreach ($$data->productImages as $key => $image) {
                $images = $image->image_path.',';
            }
    		return [
    			'SNO'             				=> $key+1,
    			'id'      						=> $data->id,
    			'user_name'						=> AES256::decrypt($data->user->first_name, env('ENCRYPTION_KEY')),
    			'category_master'				=> $data->categoryMaster->title,
    			'title'							=> $data->title,
    			'slug'							=> $data->slug,
    			'meta_description'				=> $data->meta_description,
    			'short_summary'					=> $data->short_summary,
    			'type'							=> $data->type,
    			'sku'							=> $data->sku,
                'basic_price_wo_vat'            => $data->basic_price_wo_vat,
    			'price'							=> $data->price,
    			'is_on_offer'					=> $data->is_on_offer,
    			'discount_type'					=> $data->discount_type,
    			'discount_value'				=> $data->discount_value,
    			'quantity'						=> $data->quantity,
    			'description'					=> $data->description,
    			'sell_type'						=> $data->sell_type,
    			'service_availability'			=> $data->service_availability,
    			'service_online_link'			=> $data->service_online_link,
    			'service_type'					=> $data->service_type,
    			'service_start_time'			=> $data->service_start_time,
    			'service_end_time'				=> $data->service_end_time,
    			'delivery_type'					=> $data->delivery_type,
    			'available_to'					=> $data->available_to,
    			'is_published'					=> $data->is_published,
    			'published_at'					=> $data->published_at,
    			'is_for_sale'					=> $data->is_for_sale,
    			'is_promoted'					=> $data->is_promoted,
    			'promotion_start_at'			=> $data->promotion_start_at,
    			'promotion_end_at'				=> $data->promotion_end_at,
    			'view_count'					=> $data->view_count,
    			'avg_rating'					=> $data->avg_rating,
    			'status'						=> $data->status,
    			'gtin_isbn'						=> $data->gtin_isbn,
    			'discounted_price'				=> $data->discounted_price,
    			'deposit_amount'				=> $data->deposit_amount,
    			'is_used_item'					=> $data->is_used_item,
    			'item_condition'				=> $data->item_condition,
    			'brand'							=> $data->brand,
    			'most_popular'					=> $data->most_popular,
    			'is_reward_point_applicable'	=> $data->is_reward_point_applicable,
                'reward_points'                 => $data->reward_points,
    			'images' => $images,
    			'created_at'      				=> $data->created_at,
    		];
    	});
    }
}
