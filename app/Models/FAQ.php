<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Language;
use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['language_id','module_type_id','question','answer'];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }
}
