<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\ServiceProviderTypeDetail;
use App\Models\RegistrationType;

class ServiceProviderType extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'auto_id',
        'registration_type_id',
        'title',
        'slug',
        'status'
    ];

    public function serviceProviderTypeDetails()
    {
        return $this->hasMany(ServiceProviderTypeDetail::class, 'service_provider_type_id', 'id');
    }

    public function registrationType()
    {
        return $this->belongsTo(RegistrationType::class, 'registration_type_id', 'id');
    }
}
