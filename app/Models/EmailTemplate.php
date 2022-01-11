<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Language;

class EmailTemplate extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['language_id','template_for','from','subject','body','attachment_path','status'];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }
}
