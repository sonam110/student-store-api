<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttributeMaster;
use App\Models\Language;

class AttributeDetail extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'attribute_master_id',
        'language_id',
        'title',
        'slug',
        'description',
        'status'
    ];


    public function attributeMaster()
    {
        return $this->belongsTo(AttributeMaster::class, 'attribute_master_id', 'id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }
}
