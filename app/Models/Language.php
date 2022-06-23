<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Language extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'value',
        'status',
        'is_default_language',
        'direction'
    ];
}
