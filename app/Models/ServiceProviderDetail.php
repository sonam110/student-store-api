<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\RegistrationType;
use App\Models\ServiceProviderType;
use App\Models\RegistrationTypeDetail;
use App\Models\ServiceProviderTypeDetail;

class ServiceProviderDetail extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id','registration_type_id','service_provider_type_id','company_name','organization_number','about_company','company_website_url','company_logo_path', 'company_logo_thumb_path','vat_number','vat_registration_file_path','year_of_establishment','avg_rating','status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function registrationType()
    {
        return $this->belongsTo(RegistrationType::class, 'registration_type_id', 'id');
    }

    public function registrationTypeDetail()
    {
        return $this->belongsTo(RegistrationTypeDetail::class, 'registration_type_id', 'registration_type_id');
    }

    public function serviceProviderType()
    {
        return $this->belongsTo(ServiceProviderType::class, 'service_provider_type_id', 'id');
    }

    public function serviceProviderTypeDetail()
    {
        return $this->belongsTo(ServiceProviderTypeDetail::class, 'service_provider_type_id', 'service_provider_type_id');
    }
}
