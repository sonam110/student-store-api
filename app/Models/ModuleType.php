<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ModuleType extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'status'
    ];
}
