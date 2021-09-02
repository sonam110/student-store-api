<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Package extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'module', 'package_for', 'type_of_package', 'slug', 'job_ads', 'publications_day', 'duration', 'cvs_view', 'employees_per_job_ad', 'no_of_boost', 'boost_no_of_days', 'price', 'start_up_fee', 'subscription', 'commission_per_sale', 'number_of_product', 'number_of_service', 'notice_month', 'locations', 'range_of_age', 'organization', 'attendees', 'cost_for_each_attendee', 'top_up_fee', 'is_published', 'published_at','most_popular','most_popular_no_of_days','most_popular_no_of_days','top_selling','top_selling_no_of_days'
    ];
}
