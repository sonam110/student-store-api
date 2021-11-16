<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use App\Models\ReasonForActionDetail;

class ReasonForAction extends Model
{
    use HasFactory, Uuid;

    protected $fillable = ['module_type_id','action','reason_for_action','status','text_field_enabled'];

    public function reasonForActionDetails()
    {
        return $this->hasMany(ReasonForActionDetail::class, 'reason_for_action_id', 'id');
    }
}
