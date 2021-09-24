<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductsServicesBook;

class ProductImage extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['products_services_book_id','image_path','thumb_image_path','cover'];

    public function user()
    {
        return $this->belongsTo(ProductsServicesBook::class,'products_services_book_id','id');
    }
}
