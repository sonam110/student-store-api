<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Language;

class Page extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['title','slug','description','image_path','status','language_id'];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }
}
