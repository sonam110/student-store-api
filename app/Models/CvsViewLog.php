<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\UserCvDetail;
use App\Models\Job;

class CvsViewLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','user_cv_detail_id', 'applicant_id', 'job_id' ,'user_package_subscription_id','valid_till'];

    public function company()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'applicant_id', 'id');
    }

    public function cvDetail()
    {
        return $this->belongsTo(UserCvDetail::class, 'user_cv_detail_id', 'id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id', 'id');
    }
}
