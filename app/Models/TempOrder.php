<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class TempOrder extends Model
{
    use HasFactory, Uuid;

    protected $fillable = ['auto_id', 'user_id','request_param'];
}
