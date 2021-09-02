<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductsServicesBook;
use App\Models\Contest;
use App\Models\Job;

class Abuse extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['auto_id','user_id','products_services_book_id','contest_id','job_id','reason_id_for_abuse','reason_for_abuse','status'];

    public function product()
    {
        return $this->belongsTo(ProductsServicesBook::class,'products_services_book_id','id');
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class,'contest_id','id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class,'job_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
