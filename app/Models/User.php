<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Language;
use App\Models\UserType;
use App\Models\StudentDetail;
use App\Models\ServiceProviderDetail;
use App\Models\AddressDetail;
use App\Models\UserDeviceInfo;
use App\Models\PaymentCardDetail;
use App\Models\UserPackageSubscription;
use App\Models\ContactUs;
use App\Models\Job;
use App\Models\FavouriteJob;
use App\Models\JobTag;
use App\Models\JobApplication;
use App\Models\UserCvDetail;
use App\Models\UserEducationDetail;
use App\Models\UserWorkExperience;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\RatingAndFeedback;
use App\Models\Notification;
use App\Models\OauthAccessToken;
use App\Models\ShippingCondition;
use App\Models\ProductsServicesBook;
use App\Models\Contest;
use App\Models\CoolCompanyAssignment;
use App\Models\CoolCompanyFreelancer;
use App\Models\VendorFundTransfer;
use App\Models\ChatList;
use App\Models\CartDetail;
use App\Models\RewardCreditLog;
use mervick\aesEverywhere\AES256;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Uuid, Billable;
    use HasApiTokens;

    const QR_NUMBER_PREFIX      = null;
    const QR_NUMBER_SEPRATOR    = null;
    const QR_NUMBER_START       = 100000000;

    protected $fillable = [
        'language_id','user_type_id','first_name','last_name','gender','dob','email','contact_number','password','email_verified_at','is_email_verified','is_contact_number_verified','profile_pic_path','profile_pic_thumb_path','short_intro','qr_code_img_path','qr_code_number','qr_code_valid_till','reward_points','cp_first_name','cp_last_name','cp_email','cp_contact_number','cp_gender','is_minor','guardian_first_name','guardian_last_name','guardian_email','guardian_contact_number','is_guardian_email_verified','is_guardian_contact_number_verified','is_verified','is_agreed_on_terms','is_prime_user','is_deleted','status','last_login','guardian_password','social_security_number','show_email','show_contact_number','bank_account_type','bank_name','bank_account_num','bank_identifier_code','stripe_account_id','stripe_status', 'stripe_create_timestamp', 'stripe_customer_id','klarna_customer_token'
    ];

    protected $hidden = [
        'password',
        'guardian_password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getFirstNameAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getLastNameAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getGenderAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getDobAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getEmailAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getContactNumberAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getCpFirstNameAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getCpLastNameAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getCpEmailAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getCpContactNumberAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getCpGenderAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getGuardianFirstNameAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getGuardianLastNameAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getGuardianEmailAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getGuardianContactNumberAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }
    public function getSocialSecurityNumberAttribute($value)
    {
        return (!empty($value)) ? AES256::encrypt($value, env('ENCRYPTION_KEY')) : NULL;
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }

    public function userType()
    {
        return $this->belongsTo(UserType::class, 'user_type_id', 'id');
    }

    public function studentDetail()
    {
        return $this->hasOne(StudentDetail::class, 'user_id', 'id');
    }

    public function studentDetailPublic()
    {
        return $this->hasOne(StudentDetail::class, 'user_id', 'id')->select('id','user_id','avg_rating');
    }

    public function serviceProviderDetail()
    {
        return $this->hasOne(ServiceProviderDetail::class, 'user_id', 'id')->with('registrationTypeDetail','serviceProviderTypeDetail');
    }

    public function serviceProviderDetailOnly()
    {
        return $this->hasOne(ServiceProviderDetail::class, 'user_id', 'id');
    }

    public function addressDetails()
    {
        return $this->hasMany(AddressDetail::class, 'user_id', 'id')->where('is_deleted',0);
    }

    public function defaultAddress()
    {
        return $this->hasOne(AddressDetail::class, 'user_id', 'id')->where('is_deleted',0)->where('is_default', 1);
    }

    public function addressDetail()
    {
        return $this->hasOne(AddressDetail::class, 'user_id', 'id')->where('is_default', 1);
    }

    public function userDeviceInfos()
    {
        return $this->hasMany(UserDeviceInfo::class, 'user_id', 'id');
    }

    public function paymentCardDetails()
    {
        return $this->hasMany(PaymentCardDetail::class, 'user_id', 'id');
    }

    public function shippingConditions()
    {
        return $this->hasMany(ShippingCondition::class, 'user_id', 'id')->orderBy('created_at','desc');
    }

    public function defaultPaymentCard()
    {
        return $this->hasOne(PaymentCardDetail::class, 'user_id', 'id')->where('is_default', 1);
    }

    public function userPackageSubscriptions()
    {
        return $this->hasMany(UserPackageSubscription::class, 'user_id', 'id');
    }

    public function contactUs()
    {
        return $this->hasMany(ContactUs::class, 'user_id', 'id');
    }

    

    public function spFavouriteJobs()
    {
        return $this->hasMany(FavouriteJob::class, 'sp_id', 'id');
    }

    public function saFavouriteJobs()
    {
        return $this->hasMany(FavouriteJob::class, 'sa_id', 'id');
    }

    public function jobTags()
    {
        return $this->hasMany(JobTag::class, 'user_id', 'id');
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class, 'user_id', 'id');
    }

    public function userCvDetail()
    {
        return $this->hasOne(UserCvDetail::class, 'user_id', 'id');
    }

    public function userEducationDetails()
    {
        return $this->hasMany(UserEducationDetail::class, 'user_id', 'id');
    }

    public function userWorkExperiences()
    {
        return $this->hasMany(UserWorkExperience::class, 'user_id', 'id');
    }

    


    /// dublicate

    public function cvDetail()
    {
        return $this->hasOne(UserCvDetail::class, 'user_id', 'id');
    }

    public function educations()
    {
        return $this->hasMany(UserEducationDetail::class, 'user_id', 'id');
    }

    public function experiences()
    {
        return $this->hasMany(UserWorkExperience::class, 'user_id', 'id');
    }


    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'user_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function ratings()
    {
        return $this->hasMany(RatingAndFeedback::class, 'to_user', 'id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'id');
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'id')->where('read_status',0);
    }

    public function unreadChats()
    {
        return $this->hasMany(ChatList::class, 'receiver_id', 'id')->where('status','unread');
    }
    

    public function AauthAcessToken(){
        return $this->hasMany(OauthAccessToken::class,'user_id','id');
    }

    public function products()
    {
        return $this->hasMany(ProductsServicesBook::class, 'user_id', 'id')->where('type','product');
    }

    public function services()
    {
        return $this->hasMany(ProductsServicesBook::class, 'user_id', 'id')->where('type','service');
    }

    public function books()
    {
        return $this->hasMany(ProductsServicesBook::class, 'user_id', 'id')->where('type','book');
    }

    public function contests()
    {
        return $this->hasMany(Contest::class, 'user_id', 'id')->where('type','contest');
    }

    public function events()
    {
        return $this->hasMany(Contest::class, 'user_id', 'id')->where('type','event');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'user_id', 'id');
    }

    public function cartDetails()
    {
        return $this->hasMany(CartDetail::class, 'user_id', 'id');
    }

    public function coolCompanyFreelancer()
    {
        return $this->hasOne(CoolCompanyFreelancer::class,'user_id','id')->with('coolCompanyAssignment');
    }
    
    public function coolCompanyAssignment()
    {
        return $this->hasMany(CoolCompanyAssignment::class,'user_id','id');
    }

    public function vendorFundTransfers()
    {
        return $this->hasMany(VendorFundTransfer::class,'user_id','id');
    }

    public function rewardCreditLog()
    {
        return $this->hasMany(RewardCreditLog::class, 'user_id', 'id');
    }
}