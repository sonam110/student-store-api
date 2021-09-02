<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\UserCvDetail;

class UserWorkExperience extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id',
        'user_cv_detail_id',
        'title',
        'employer_name',
        'activities_and_responsibilities',
        'currently_working',
        'from_date',
        'to_date',
        'is_from_sweden',
        'country',
        'state',
        'city',
        'status'
    ];


    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function userCvDetail()
    {
        return $this->belongsTo(UserCvDetail::class, 'user_cv_detail_id', 'id');
    }
}
