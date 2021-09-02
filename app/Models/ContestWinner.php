<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Contest;
use App\Models\ContestPrizedetail;

class ContestWinner extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id','contest_id','contest_application_id','prize','contest_prize_detail','remark'
    ];


    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class, 'contest_id', 'id');
    }

    public function contestPrizedetail()
    {
        return $this->belongsTo(ContestPrizedetail::class, 'contest_prize_detail_id', 'id');
    }
}
