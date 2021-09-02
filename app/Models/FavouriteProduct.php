<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ProductsServicesBook;

class FavouriteProduct extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['product_service_book_id', 'user_id'];

    public function product()
    {
        return $this->belongsTo(ProductsServicesBook::class, 'products_services_book_id', 'id');
    }
    

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
