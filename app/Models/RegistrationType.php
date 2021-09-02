<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RegistrationTypeDetail;

class RegistrationType extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'title',
        'slug',
        'status'
    ];

    public function registrationTypeDetails()
    {
        return $this->hasMany(RegistrationTypeDetail::class, 'registration_type_id', 'id');
    }
}
