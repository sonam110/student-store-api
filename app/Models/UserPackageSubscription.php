<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Package;

class UserPackageSubscription extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id','package_id','package_valid_till','subscription_status','remark','job_ads','publications_day','duration','cvs_view','employees_per_job_ad','no_of_boost','boost_no_of_days','price','start_up_fee','subscription','commission_per_sale','number_of_product','number_of_service','number_of_book','number_of_contest','number_of_event','notice_month','locations','organization','attendees','range_of_age','cost_for_each_attendee','top_up_fee','module','type_of_package','most_popular','most_popular_no_of_days','top_selling','top_selling_no_of_days','used_no_of_boost','used_most_popular','used_top_selling','used_job_ads','used_cvs_view','used_number_of_product','used_number_of_service','used_number_of_book','used_number_of_contest','used_number_of_event'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }
}
