<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Label;

class LabelGroup extends Model
{
    use HasFactory, Uuid;


    public function labels()
    {
        return $this->hasMany(Label::class, 'label_group_id', 'id');
    }

    public function returnLabelNames()
    {
        return $this->hasOne(Label::class, 'label_group_id', 'id');
    }
}
