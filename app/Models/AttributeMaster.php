<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CategoryMaster;
use App\Models\BucketGroup;

class AttributeMaster extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        // 'category_master_id',
        'category_master_slug',
        'bucket_group_id'
    ];

    // public function categoryMaster()
    // {
    //     return $this->belongsTo(CategoryMaster::class, 'category_master_id', 'id');
    // }

    public function categoryMaster()
    {
        return $this->belongsTo(CategoryMaster::class, 'category_master_slug', 'slug');
    }

    public function bucketGroup()
    {
        return $this->belongsTo(BucketGroup::class, 'bucket_group_id', 'id');
    }
}
