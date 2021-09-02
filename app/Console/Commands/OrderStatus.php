<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderItem;
use App\Models\OrderTracking;
use DateTime;

class OrderStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
    	parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
    	$orderItems = OrderItem::where('item_status','delivered')->orWhere('item_status','shipped')->get();
    	foreach($orderItems as $orderItem) {
    		if($orderItem->item_status == 'shipped')
    		{
    			$edd = $orderItem->expected_delivery_date;
    			// $to = new DateTime($edd);
    			$to = date('Y-m-d',strtotime($edd));
    			// $to = \Carbon\Carbon::parse($edd)->format('Y-m-d h:i A');
    			$from = \Carbon\Carbon::now();
    			$diff_in_hours = \Carbon\Carbon::parse('edd')->diffInHours();
    			if($diff_in_hours > '24'){
    				$orderItem->update(['item_status'=>'delivered']);
    				$title = 'Order Delivered';
    				$body =  'Order for '.$orderItem->title.' has been Delivered.';
    				$type = 'Order Status';
    				$user = $orderItem->user;
    				pushNotification($title,$body,$user,$type,true,'buyer','product');
    				$seller = $orderItem->productsServicesBook->user;
    				pushNotification($title,$body,$seller,$type,true,'seller','product');

    				$orderTracking = new OrderTracking;
    				$orderTracking->order_item_id = $orderItem->id;
    				$orderTracking->status = 'delivered'; 
    				$orderTracking->comment = 'Automatically updated to delivered';
    				$orderTracking->type = 'delivery';
    				$orderTracking->save();
    			}

    		}
    		elseif($orderItem->item_status == 'delivered')
    		{
    			$to = $orderItem->updated_at;
    			$from = \Carbon\Carbon::now();
    			$diff_in_hours = \Carbon\Carbon::parse('edd')->diffInHours();
    			if($diff_in_hours > '24'){
    				$orderItem->update(['item_status'=>'delivered']);
    				$title = 'Order Completed';
    				$body =  'Order for '.$orderItem->title.' has been Completed.';
    				$type = 'Order Status';
    				$user = $orderItem->user;
    				pushNotification($title,$body,$user,$type,true,'buyer','product');
    				$seller = $orderItem->productsServicesBook->user;
    				pushNotification($title,$body,$seller,$type,true,'seller','product');

    				$orderTracking = new OrderTracking;
    				$orderTracking->order_item_id = $orderItem->id;
    				$orderTracking->status = 'completed'; 
    				$orderTracking->comment = 'Automatically updated to completed';
    				$orderTracking->type = 'delivery';
    				$orderTracking->save();
    			}
    		}

    	}
    	return 0;
    }
}
