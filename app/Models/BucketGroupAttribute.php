<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BucketGroup;
use App\Models\BucketGroupAttributeDetail;

class BucketGroupAttribute extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['bucket_group_id', 'name', 'type', 'is_multiple'];

    public function bucketGroup()
    {
        return $this->belongsTo(BucketGroup::class, 'bucket_group_id', 'id');
    }

    public function bucketGroupAttributeDetails()
    {
        return $this->hasMany(BucketGroupAttributeDetail::class, 'bucket_group_attribute_id', 'id');
    }

    public function attributeDetails()
    {
        return $this->hasOne(BucketGroupAttributeDetail::class, 'bucket_group_attribute_id');
    }
}
