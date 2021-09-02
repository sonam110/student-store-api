<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\RegistrationType;

class RegistrationTypeDetail extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'language_id',
        'registration_type_id',
        'title',
        'slug',
        'description',
        'status'
    ];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }

    public function registrationType()
    {
        return $this->belongsTo(RegistrationType::class, 'registration_type_id', 'id');
    }
}
