<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\FavouriteJob;
use App\Models\JobTag;
use App\Models\JobApplication;
use App\Models\AddressDetail;
use App\Models\CategoryMaster;
use Auth;

class Job extends Model
{
    use HasFactory, Uuid;

    use SoftDeletes;

    protected $table = 'sp_jobs';

    protected $fillable = [
        'user_id', 'language_id', 'address_detail_id', 'title', 'slug', 'meta_description', 'short_summary', 'job_type', 'job_nature', 'job_hours', 'job_environment', 'years_of_experience', 'known_languages', 'description', 'duties_and_responsibilities', 'nice_to_have_skills', 'job_start_date', 'application_start_date', 'application_end_date', 'job_status', 'is_deleted', 'is_published', 'published_at', 'is_promoted', 'promotion_start_date', 'promotion_end_date', 'view_count','category_master_id','sub_category_slug','meta_title','meta_keywords'];

    //protected $table = 'jobs_student_store';


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function favouriteJobs()
    {
        return $this->hasMany(FavouriteJob::class, 'job_id', 'id');
    }

    public function jobTags()
    {
        return $this->hasMany(JobTag::class,'job_id','id')->where('title','!=',null);
    }

    public function jobNiceToHaveTags()
    {
        return $this->hasMany(JobTag::class,'job_id','id')->where('nice_to_have','!=',null);
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class, 'job_id', 'id');
    }

    public function acceptedJobApplications()
    {
        return $this->hasMany(JobApplication::class, 'job_id', 'id')->where('application_status','accepted');
    }

    public function addressDetail()
    {
        return $this->belongsTo(AddressDetail::class,'address_detail_id','id');
    }

    public function categoryMaster()
    {
        return $this->belongsTo(CategoryMaster::class, 'category_master_id', 'id');
    }

    public function subCategory()
    {
        return $this->belongsTo(CategoryMaster::class, 'sub_category_slug', 'slug');
    }

    public function isFavourite()
    {
        return $this->hasOne(FavouriteJob::class,'job_id','id')->where('sa_id',Auth::id())->where('sp_id',null)->select(['id','job_id','sa_id']);
    }

    public function isApplied()
    {
        return $this->hasOne(JobApplication::class,'job_id','id')->where('user_id',Auth::id())->select(['id','job_id','user_id']);
    }
}
