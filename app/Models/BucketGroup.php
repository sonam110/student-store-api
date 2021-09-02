<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BucketGroupDetail;
use App\Models\BucketGroupAttribute;
use App\Models\AttributeMaster;

class BucketGroup extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['group_name', 'slug', 'type', 'text_type', 'is_multiple'];

    public function bucketGroupDetails()
    {
        return $this->hasMany(BucketGroupDetail::class, 'bucket_group_id', 'id');
    }

    public function bucketGroupAttributes()
    {
        return $this->hasMany(BucketGroupAttribute::class, 'bucket_group_id', 'id');
    }

    public function attributes()
    {
        return $this->hasMany(AttributeMaster::class, 'bucket_group_id', 'id');
    }
}
