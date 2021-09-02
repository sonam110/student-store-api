<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BucketGroup;
use App\Models\Language;

class BucketGroupDetail extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['bucket_group_id', 'language_id', 'name'];

    public function bucketGroup()
    {
        return $this->belongsTo(BucketGroup::class, 'bucket_group_id', 'id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }
}
