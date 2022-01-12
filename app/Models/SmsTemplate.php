<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Language;

class SmsTemplate extends Model
{
    use HasFactory, Uuid;

    protected $fillable = ['auto_id','language_id','template_for','message','status'];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }
}
