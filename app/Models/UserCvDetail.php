<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\AddressDetail;
use App\Models\JobTag;

class UserCvDetail extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id', 'address_detail_id', 'title', 'languages_known', 'key_skills', 'preferred_job_env', 'other_description', 'is_published', 'published_at', 'cv_url', 'generated_cv_file', 'total_experience','cv_update_status'
    ];

    /*protected $casts = [
        'preferred_job_env' => 'array'
    ];*/

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function addressDetail()
    {
        return $this->belongsTo(AddressDetail::class,'address_detail_id','id');
    }

    public function jobTags()
    {
        return $this->hasMany(JobTag::class,'cv_id','id');
    }
}
