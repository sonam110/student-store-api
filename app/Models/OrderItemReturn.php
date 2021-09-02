<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemReturn extends Model
{
    use HasFactory, Uuid;

    protected $fillable = ['user_id','return_address_id','order_item_id','products_services_book_id','quantity','return_type','shipment_company_name','reason_of_return','images','amount_to_be_returned','return_card_holder_name','return_tracking_number','expected_return_date','date_of_return_completed','first_name','last_name','email','contact_number','latitude','longitude','country','state','city','full_address','return_status','reason_for_return_decline','return_code'];
}
