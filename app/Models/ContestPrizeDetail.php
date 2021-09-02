<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contest;

class ContestPrizeDetail extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'contest_id',
        'title',
        'position',
        'type',
        'description',
        'status'
    ];


    public function contest()
    {
        return $this->belongsTo(Contest::class, 'contest_id', 'id');
    }
}
