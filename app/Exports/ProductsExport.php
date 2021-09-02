<?php

namespace App\Exports;

use App\Models\ProductsServicesBook;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Auth;

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
    		// 'sub_category',
    		'title',
    		'slug',
    		'meta_description',
    		'short_summary',
    		'type',
    		'sku',
    		'price',
    		'is_on_offer',
    		// 'discount_type',
    		// 'discount_value',
    		'quantity',
    		'description',
          	// 'attribute_details',
          	// 'condition',
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
    		'sale_start_at',
    		'sale_end_at',
    		'is_promoted',
    		'promotion_start_at',
    		'promotion_end_at',
    		'view_count',
    		'avg_rating',
    		'status',
    		'sub_category_slug',
    		'tags',
    		'gtin_isbn',
    		'discounted_price',
    		'deposit_amount',
    		'is_used_item',
    		'item_condition',
    		'brand',
    		'most_popular',
    		'most_popular_start_at',
    		'most_popular_end_at',
    		'top_selling',
    		'top_selling_start_at',
    		'top_selling_end_at',
    		'is_reward_point_applicable',
    		'reward_points',
    		'is_sold',
    		'sold_at_student_store',
    		'days_taken',
    		'Created at'
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
    	$products = $products->get();

    	// return $products;

    	return $products->map(function ($data, $key) {
    		return [
    			'SNO'             				=> $key+1,
    			'id'      						=> $data->id,
    			'user_name'						=> $data->user->first_name.' '.$data->user->last_name,
    			'category_master'				=> $data->categoryMaster->title,
    			// 'sub_category'					=> $data->subCategory->title,
    			'title'							=> $data->title,
    			'slug'							=> $data->slug,
    			'meta_description'				=> $data->meta_description,
    			'short_summary'					=> $data->short_summary,
    			'type'							=> $data->type,
    			'sku'							=> $data->sku,
    			'price'							=> $data->price,
    			'is_on_offer'					=> $data->is_on_offer,
    			// 'discount_type'					=> $data->discount_type,
    			// 'discount_value'				=> $data->discount_value,
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
    			'sale_start_at'					=> $data->sale_start_at,
    			'sale_end_at'					=> $data->sale_end_at,
    			'is_promoted'					=> $data->is_promoted,
    			'promotion_start_at'			=> $data->promotion_start_at,
    			'promotion_end_at'				=> $data->promotion_end_at,
    			'view_count'					=> $data->view_count,
    			'avg_rating'					=> $data->avg_rating,
    			'status'						=> $data->status,
    			'sub_category_slug'				=> $data->sub_category_slug,
    			'tags'							=> $data->tags,
    			'gtin_isbn'						=> $data->gtin_isbn,
    			'discounted_price'				=> $data->discounted_price,
    			'deposit_amount'				=> $data->deposit_amount,
    			'is_used_item'					=> $data->is_used_item,
    			'item_condition'				=> $data->item_condition,
    			'brand'							=> $data->brand,
    			'most_popular'					=> $data->most_popular,
    			'most_popular_start_at'			=> $data->most_popular_start_at,
    			'most_popular_end_at'			=> $data->most_popular_end_at,
    			'top_selling'					=> $data->top_selling,
    			'top_selling_start_at'			=> $data->top_selling_start_at,
    			'top_selling_end_at'			=> $data->top_selling_end_at,
    			'is_reward_point_applicable'	=> $data->is_reward_point_applicable,
    			'reward_points'					=> $data->reward_points,
    			'is_sold'						=> $data->is_sold,
    			'sold_at_student_store'			=> $data->sold_at_student_store,
    			'days_taken'					=> $data->days_taken,
              	'Created at'      				=>  $data->created_at,
    		];
    	});
    }

    // public function collection()
    // {
    //     $findId = SendSms::select('id')->where('uuid', $this->uuid)->first();
    //     if($findId)
    //     {
    //         $reportData =  ShortLink::where('send_sms_id', $findId->id)->with('linkClickLogs')->get();
    //         return $array = $reportData->map(function ($data, $key) {
    //             $relation = $data->linkClickLogs;
    //             return $relation->map(function ($ndata, $nkey) {
    //                 return [
    //                   'SNO'             => $nkey+1,
    //                   'Mobile'          => $ndata->mobile,
    //                   'IP address'      => $ndata->ip,
    //                   'Browser Name'    => $ndata->browserName,
    //                   'Browser Family'  => $ndata->browserFamily,
    //                   'Browser Version' => $ndata->browserVersion,
    //                   'Browser Engine'  => $ndata->browserEngine,
    //                   'Platform Name'   => $ndata->platformName,
    //                   'Created at'      => $ndata->created_at,
    //                 ];
    //             });


    //         });
    //     }
    //     else
    //     {
    //         return;
    //     }
    // }
}
