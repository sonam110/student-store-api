<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\TagMaster;
use App\Models\ProductsServicesBook;

class TagRelation extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'tag_master_id',
        'products_services_book_id',
        // 'service_id',
        'job_id',
        'contest_id'
    ];


    public function tagMaster()
    {
        return $this->belongsTo(TagMaster::class, 'tag_master_id', 'id');
    }

    public function productsServicesBook()
    {
        return $this->belongsTo(ProductsServicesBook::class, 'products_services_book_id', 'id');
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class, 'contest_id', 'id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id', 'id');
    }
}
