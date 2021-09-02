<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Contest;

class ContestApplication extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id','contest_id','contest_type','contest_title','application_status','subscription_status','application_remark','subscription_remark','document','reason_id_for_rejection','reason_for_rejection','reason_id_for_cancellation','reason_for_cancellation','cancelled_by','winner'
    ];


    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class, 'contest_id', 'id');
    }
}
