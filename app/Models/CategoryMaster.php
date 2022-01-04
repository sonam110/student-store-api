<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ModuleType;
use App\Models\CategoryMaster;
use App\Models\CategoryDetail;
use App\Models\Brand;
use App\Models\Language;

class CategoryMaster extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'module_type_id',
        'category_master_id',
        'title',
        'slug',
        'status',
        'vat'
    ];


    public function moduleType()
    {
        return $this->belongsTo(ModuleType::class, 'module_type_id', 'id');
    }

    public function categoryMaster()
    {
        return $this->belongsTo(CategoryMaster::class, 'category_master_id', 'id');
    }

    public function categoryDetails()
    {
        return $this->hasMany(CategoryDetail::class, 'category_master_id', 'id');
    }

    public function categoryDetail()
    {
        return $this->hasOne(CategoryDetail::class, 'category_master_id', 'id');
    }

    public function categoryParent()
    {
        return $this->hasOne(CategoryDetail::class, 'category_master_id', 'id');
    }

    public function getSubCatLangBySlug()
    {
        return $this->hasOne(CategoryDetail::class, 'slug', 'slug');
    }

    public function SubCategoryDetail()
    {
        return $this->hasOne(CategoryDetail::class, 'slug', 'slug');
    }

    public function subcategories()
    {
        return $this->hasMany(static::class, 'category_master_id', 'id');
    }

    public function brands()
    {
        return $this->hasMany(CategoryMaster::class, 'category_master_id','id');
    }

    public function categoryLanguageDetails()
    {
        return $this->hasMany(CategoryDetail::class, 'slug', 'slug');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }    
}
