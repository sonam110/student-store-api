<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Language;
use App\Models\ServiceProviderType;

class ServiceProviderTypeDetail extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'language_id',
        'service_provider_type_id',
        'title',
        'slug',
        'description',
        'status'
    ];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }

    public function serviceProviderType()
    {
        return $this->belongsTo(ServiceProviderType::class, 'service_provider_type_id', 'id');
    }
}
