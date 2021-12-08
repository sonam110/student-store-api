<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CategoryMaster;
use App\Models\AddressDetail;
use App\Models\ContestCancellationRange;
use App\Models\ContestApplication;
use App\Models\RatingAndFeedback;
use Auth;

class Contest extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id','address_detail_id','category_master_id','sub_category_slug','title','slug','description','type','contest_type','event_type','cover_image_path','cover_image_thumb_path','sponsor_detail','start_date','end_date','start_time','end_time','application_start_date','application_end_date','max_participants','no_of_winners','winner_prizes','mode','meeting_link','address','education_level','educational_institition','age_restriction','min_age','max_age','others','condition_description','condition_file_path','jury_members','is_free','subscription_fees','use_cancellation_policy','provide_participation_certificate','is_on_offer','discount_type','discount_value','is_published','published_at','is_deleted','required_file_upload','is_reward_point_applicable','reward_points','status','condition_for_joining','user_type','available_for','file_title','target_country','target_city','service_provider_type_id','registration_type_id','is_min_participants','min_participants','discounted_price','reason_id_for_rejection','reason_for_rejection','reason_id_for_cancellation','reason_for_cancellation','meta_title','meta_keywords','meta_description','basic_price_wo_vat'
    ];


    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function serviceProviderType()
    {
        return $this->belongsTo(ServiceProviderType::class,'service_provider_type_id','id');
    }

    public function registrationType()
    {
        return $this->belongsTo(RegistrationType::class,'registration_type_id','id');
    }

    public function categoryMaster()
    {
        return $this->belongsTo(CategoryMaster::class, 'category_master_id', 'id');
    }

    public function addressDetail()
    {
        return $this->belongsTo(AddressDetail::class, 'address_detail_id', 'id');
    }

    public function subCategory()
    {
        return $this->belongsTo(CategoryMaster::class, 'sub_category_slug', 'slug');
    }

    public function cancellationRanges()
    {
        return $this->hasMany(ContestCancellationRange::class, 'contest_id', 'id')->orderBy('auto_id','asc');
    }
    public function contestApplications()
    {
        return $this->hasMany(ContestApplication::class, 'contest_id', 'id')->where('application_status', '!=', 'canceled');
    }

    public function isApplied()
    {
        return $this->hasOne(ContestApplication::class,'contest_id','id')->where('user_id',Auth::id())->select(['id','contest_id','user_id'])->where('application_status', '!=', 'canceled');
    }

    // public function isAppliedNotCanceled()
    // {
    //     return $this->hasOne(ContestApplication::class,'contest_id','id')->where('user_id',Auth::id())->where('application_status','!=','canceled')->select(['id','contest_id','user_id']);
    // }

    

    public function contestWinners()
    {
        return $this->hasMany(ContestWinner::class, 'contest_id', 'id')->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path');
    }

    public function ratings()
    {
        return $this->hasMany(RatingAndFeedback::class,'contest_id','id');
    }

    public function ratingAndFeedback()
    {
        return $this->hasOne(RatingAndFeedback::class,'contest_id','id')->where('from_user',Auth::id());
    }
}
