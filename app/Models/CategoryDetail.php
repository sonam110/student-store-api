<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CategoryMaster;

class CategoryDetail extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'category_master_id',
        'language_id',
        'is_parent',
        'title',
        'slug',
        'description',
        'status'
    ];


    public function categoryMaster()
    {
        return $this->belongsTo(CategoryMaster::class, 'category_master_id', 'id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }
}
