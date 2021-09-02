<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\LabelGroup;

class Label extends Model
{
    use HasFactory, Uuid;


    public function labelGroup()
    {
        return $this->belongsTo(LabelGroup::class, 'label_group_id', 'id');
    }
}
