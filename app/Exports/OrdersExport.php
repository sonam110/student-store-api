<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\OrderItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Auth;
use mervick\aesEverywhere\AES256;

class OrdersExport implements FromCollection, WithHeadings
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
    		'order_number',
            'order_status',
            // 'sub_total',
            // 'item_discount',
            // 'vat',
            // 'shipping_charge',
            // 'total',
            // 'promo_code',
            // 'promo_code_discount',
            'grand_total',
            // 'remark',
            'first_name',
            'last_name',
            'email',
            'contact_number',
            'country',
            'state',
            'city',
            'full_address',
            'payable_amount',
            'order_for',
            'Order Item id',
            'product_type',
            'contest_type',
            'title',
            'price',
            'quantity',
            'discount',
            'item_status',
            'payment_status',
    		'Created At'
    	];
    }

    public function collection()
    {
    	$orders = Order::join('order_items', function ($join) {
                $join->on('orders.id', '=', 'order_items.order_id');
            })->orderBy('orders.created_at','desc');
         
        
    	if(!empty($this->requestData['ids']))
        {
            $orders = $orders->whereIn('orders.id',$this->requestData['ids']);
        }
        if(!empty($this->requestData['product_type']))
        {
            $orders = $orders->where('order_items.product_type',$this->requestData['product_type']);
        }
        if(!empty($this->requestData['contest_type']))
        {
            $orders = $orders->where('order_items.contest_type',$this->requestData['contest_type']);
        }

        if($this->requestData['auth_applicable'] == true)
        {
            if($this->requestData['order_for'] == 'contest')
            {
                $orders = $orders->join('contest_applications', function ($join) {
                    $join->on('order_items.contest_application_id', '=', 'contest_applications.id');
                })
                ->join('contests', function ($join) {
                    $join->on('contest_applications.contest_id', '=', 'contests.id');
                })->where('orders.order_for','contest')
                ->where('contests.user_id',Auth::id());
            }
            else
            {
                $orders = $orders->join('products_services_books', function ($join) {
                    $join->on('order_items.products_services_book_id', '=', 'products_services_books.id');
                })->where('orders.order_for','product')
                ->where('products_services_books.user_id',Auth::id());;
            }
        }
        else
        {
            if(!empty($this->requestData['order_for']))
            {
                $orders = $orders->where('orders.order_for',$this->requestData['order_for']);
            }
        }
    	$orders = $orders->get();

    	// return $orders;

    	return $orders->map(function ($data, $key) {
    		return [
    			'SNO'             				=> $key+1,
    			'order_number'					=> $data->order_number,
    			'order_status'					=> $data->order_status,
    			// 'sub_total'						=> $data->sub_total,
    			// 'item_discount'					=> $data->item_discount,
    			// 'vat'							=> $data->vat,
    			// 'shipping_charge'				=> $data->shipping_charge,
    			// 'total'							=> $data->total,
    			// 'promo_code'					=> $data->promo_code,
    			// 'promo_code_discount'			=> $data->promo_code_discount,
    			'grand_total'					=> $data->grand_total,
    			// 'remark'						=> $data->remark,
    			'first_name'					=> AES256::decrypt($data->first_name, env('ENCRYPTION_KEY')),
    			'last_name'						=> AES256::decrypt($data->last_name, env('ENCRYPTION_KEY')),
    			'email'							=> AES256::decrypt($data->email, env('ENCRYPTION_KEY')),
    			'contact_number'				=> AES256::decrypt($data->contact_number, env('ENCRYPTION_KEY')),
    			'country'						=> $data->country,
    			'state'							=> $data->state,
    			'city'							=> $data->city,
    			'full_address'					=> $data->full_address,
    			'payable_amount'				=> $data->payable_amount,
    			'order_for'						=> $data->order_for,
    			'product_type'					=> $data->product_type,
    			'contest_type'					=> $data->contest_type,
    			'title'							=> $data->title,
    			'price'							=> $data->price,
    			'quantity'						=> $data->quantity,
    			'discount'						=> $data->discount,
                'item_status'                   => $data->item_status,
    			'payment_status'				=> $data->payment_status,
              	'Created At'      				=> $data->created_at,
    		];
    	});
    }
}
