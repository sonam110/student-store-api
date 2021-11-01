<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ProductsServicesBook;

class CartDetail extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id','products_services_book_id','sku','price','discount','quantity','item_status','sub_total','attribute_data'
    ];


    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function product()
    {
        return $this->belongsTo(ProductsServicesBook::class, 'products_services_book_id', 'id');
    }
}
