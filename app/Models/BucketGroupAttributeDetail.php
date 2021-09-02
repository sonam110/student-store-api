<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BucketGroupAttribute;
use App\Models\Language;

class BucketGroupAttributeDetail extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['bucket_group_attribute_id', 'language_id', 'name'];

    public function bucketGroupAttribute()
    {
        return $this->belongsTo(BucketGroupAttribute::class, 'bucket_group_attribute_id', 'id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }
}
