<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ProductsServicesBook;

class ProductTag extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['products_services_book_id' , 'type', 'user_id', 'title'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function productsServicesBook()
    {
        return $this->belongsTo(ProductsServicesBook::class,'products_services_book_id','id');
    }
}
