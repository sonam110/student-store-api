<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contest;
use App\Models\Language;

class ContestDetail extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'contest_id',
        'language_id',
        'title',
        'slug',
        'short_summary',
        'description',
        'condition_description',
        'status'
    ];


    public function language()
{
    return $this->belongsTo(Language::class, 'language_id', 'id');
}

public function contest()
{
    return $this->belongsTo(Contest::class, 'contest_id', 'id');
}
}
