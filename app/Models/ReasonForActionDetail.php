<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;
use App\Models\Language;
use App\Models\ReasonForAction;

class ReasonForActionDetail extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'language_id',
        'reason_for_action_id',
        'title',
        'slug'
    ];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }

    public function reasonForAction()
    {
        return $this->belongsTo(ReasonForAction::class, 'reason_for_action_id', 'id');
    }
}
