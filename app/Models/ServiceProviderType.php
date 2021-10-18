<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ServiceProviderTypeDetail;

class ServiceProviderType extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'title',
        'value',
        'status'
    ];

    public function serviceProviderTypeDetails()
    {
        return $this->hasMany(ServiceProviderTypeDetail::class, 'service_provider_type_id', 'id');
    }
}
