<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CategoryMaster;

class Brand extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['category_master_id', 'name', 'logo', 'status'];

    public function categoryMaster()
    {
        return $this->belongsTo(CategoryMaster::class, 'category_master_id','id');
    }
}
