<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;

class ReasonForAction extends Model
{
    use HasFactory, Uuid;

    protected $fillable = ['module_type_id','language_id','action','reason_for_action','status','text_field_enabled'];
}
