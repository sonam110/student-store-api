<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContestCancellationRange extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'contest_id','from','to','deduct_percentage_value'
    ];
}
