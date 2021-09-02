<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Job;
use App\Models\UserCvDetail;

class JobApplication extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id', 'job_id', 'user_cv_detail_id', 'job_title', 'application_status', 'job_start_date', 'job_end_date', 'application_remark', 'attachment_url'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id', 'id');
    }

    public function userCvDetail()
    {
        return $this->belongsTo(UserCvDetail::class, 'user_cv_detail_id', 'id');
    }
}
