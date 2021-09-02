<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Job;
use App\Models\UserCvDetail;

class JobTag extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['cv_id', 'job_id', 'user_id', 'title', 'nice_to_have'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class,'job_id','id');
    }

    public function userCvDetail()
    {
        return $this->belongsTo(UserCvDetail::class,'cv_id','id');
    }
}
