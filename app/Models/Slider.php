<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;
use App\Models\Language;

class Slider extends Model
{
    use HasFactory,Uuid;

    protected $fillable = ['language_id','image_path','status'];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }
}
