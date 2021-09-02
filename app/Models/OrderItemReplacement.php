<?php

namespace App\Models;

use App\Traits\Uuid;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemReplacement extends Model
{
    use HasFactory, Uuid;
    protected $fillable = ['user_id','replacement_address_id','order_item_id','products_services_book_id','quantity','replacement_type','shipment_company_name','date_of_replacement_initiated','reason_of_replacement','images','replacement_tracking_number','expected_replacement_date','date_of_replacement_completed','first_name','last_name','email','contact_number','latitude','longitude','country','state','city','full_address','replacement_status','reason_for_replacement_decline','replacement_code'];
}
