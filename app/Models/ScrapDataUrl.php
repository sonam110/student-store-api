<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScrapDataUrl extends Model
{
    use HasFactory;

    protected $fillable = ['category', 'subcategory', 'vat', 'url', 'read_at'];
}
