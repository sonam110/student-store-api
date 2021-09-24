<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserType;
use App\Models\AddressDetail;
use App\Models\Language;
use App\Models\AppSetting;
use App\Models\RewardPointSetting;
use App\Models\RegistrationType;
use App\Models\RegistrationTypeDetail;
use App\Models\ServiceProviderType;
use App\Models\ServiceProviderTypeDetail;
use DB;
use Str;
class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('languages')->truncate();
        $language = new Language;
        $language->title                = 'English';
        $language->value                = 'en';
        $language->status               = true;
        $language->save();

    	DB::table('reward_point_settings')->truncate();
		$rewardPointSetting = new RewardPointSetting;
		$rewardPointSetting->reward_points             	= "10";
		$rewardPointSetting->equivalent_currency_value 	= "1";
		$rewardPointSetting->applicable_from         	= date('Y-m-d');
		$rewardPointSetting->applicable_to         		= date('Y-m-d',strtotime('+1 year'));
        $rewardPointSetting->min_range                  = "10-50";
        $rewardPointSetting->min_value                  = "2";
        $rewardPointSetting->mid_range                  = "51-100";
        $rewardPointSetting->mid_value                  = "5";
        $rewardPointSetting->max_range                  = "101-5000";
        $rewardPointSetting->max_value                  = "10";
		$rewardPointSetting->status      				= true;
		$rewardPointSetting->save();

    	DB::table('app_settings')->truncate();
    	$appSetting             				= new AppSetting();
    	$appSetting->reward_point_setting_id	= $rewardPointSetting->id;
    	$appSetting->app_name					= "Student Store";
    	$appSetting->description				= "";
        $appSetting->logo_path                  = env('CDN_DOC_URL').'uploads/logo.png';
    	$appSetting->logo_thumb_path			= env('CDN_DOC_THUMB_URL').'logo.png';
    	$appSetting->copyright_text				= "";
    	$appSetting->fb_ur						= "";
    	$appSetting->twitter_url				= "";
    	$appSetting->insta_url					= "";
    	$appSetting->linked_url					= "";
    	$appSetting->support_email				= 'info@studentstore.com';
    	$appSetting->support_contact_number 	= '9876543212';
    	$appSetting->save();


        DB::table('user_types')->truncate();
        $userType = new UserType;
        $userType->title                = 'Admin';
        $userType->description          = 'Can perform every action';
        $userType->save();

        $userType = new UserType;
        $userType->title                = 'Student';
        $userType->description          = 'Limited access';
        $userType->save();

        $userType = new UserType;
        $userType->title                = 'Service Provider';
        $userType->description          = 'Limited access';
        $userType->save();

    	DB::table('users')->truncate();
    	$user 							= new User();
    	$user->user_type_id      		= '1';
    	$user->language_id      		= '1';
    	$user->first_name          		= 'Student-Store-Admin';
    	$user->last_name          		= '';
    	$user->email         			= 'admin@gmail.com';
    	$user->email_verified_at 		= date('Y-m-d H:i:s');
    	$user->password      			= \Hash::make('12345678');   
        $user->profile_pic_path         = env('CDN_DOC_URL').'uploads/noimage.jpg';     
    	$user->profile_pic_thumb_path	= env('CDN_DOC_THUMB_URL').'noimage.jpg'; 	
        $user->gender       			= 'male';
        $user->dob       				= '1990-03-20';
    	$user->contact_number     		= '9876543210';
    	$user->is_verified 				= true;
		$user->is_agreed_on_terms 		= true;
		$user->is_prime_user 			= true;
		$user->is_deleted 				= false;
		$user->status 					= true;
		$user->last_login 				= now();
    	$user->save();

    	$userAddressDetail = new AddressDetail;
    	$userAddressDetail->user_id 		= $user->id;
    	$userAddressDetail->latitude 		= '34';
    	$userAddressDetail->longitude 		= '87';
    	$userAddressDetail->country 		= 'Sweden';
    	$userAddressDetail->state 			= 'Stockholm';
    	$userAddressDetail->city 			= 'Stockholm';
    	$userAddressDetail->full_address 	= '123 street no. 99y';
    	$userAddressDetail->address_type 	= 'home';
    	$userAddressDetail->is_default 		= true;
    	$userAddressDetail->status 			= 1;
    	$userAddressDetail->save();

        //registration_types
        DB::table('registration_types')->truncate();
        $regType = 'Printing and Packaging, Art, Marketing, Real Estate, Business, Educational, Health, Retail, Sports, Tourism & Entertainment Sector, Electronics, Work';

        $serviceproviders[0] = 'Printing & Packaging,Printing,Packaging';
        $serviceproviders[1] = 'Graphics & Design';
        $serviceproviders[2] = 'Digital Marketing';
        $serviceproviders[3] = 'Real Estate Agents';
        $serviceproviders[4] = 'Business and Management';
        $serviceproviders[5] = 'School, University, Library, educational services, Programming & Tech';
        $serviceproviders[6] = 'Dental Clinics,Physiotherapy Center,Nursing';
        $serviceproviders[7] = 'Clothes, Stationery, Mobile Phones, Electronics, Furniture, Personal Care, Food & market, Vehicle, Toys and sports';
        $serviceproviders[8] = 'Gym,Health & fitness';
        $serviceproviders[9] = 'Concerts, Gaming, Tourism, Entertainment';

        
        foreach (explode(',', $regType) as $key => $registration) {
            $registrationType = new RegistrationType;
            $registrationType->title    = $registration;
            $registrationType->slug     = Str::slug($registration);
            $registrationType->status   = true;
            $registrationType->save();

            $registrationTypeDetail = new RegistrationTypeDetail;
            $registrationTypeDetail->language_id    = 1;
            $registrationTypeDetail->registration_type_id    = $registrationType->id;
            $registrationTypeDetail->title    = $registration;
            $registrationTypeDetail->slug     = Str::slug($registration);
            $registrationTypeDetail->status   = true;
            $registrationTypeDetail->save();

            foreach (explode(',', @$serviceproviders[$key]) as $newKey => $spVal) {
                if(!empty($spVal))
                {
                    $sp = new ServiceProviderType;
                    $sp->registration_type_id = $registrationType->id;
                    $sp->title = $spVal;
                    $sp->slug = Str::slug($spVal);
                    $sp->status = 1;
                    $sp->save();
                    if($sp)
                    {
                        $spDetail = new ServiceProviderTypeDetail;
                        $spDetail->language_id = 1;
                        $spDetail->service_provider_type_id = $sp->id;
                        $spDetail->title = $spVal;
                        $spDetail->slug = Str::slug($spVal);
                        $spDetail->status = 1;
                        $spDetail->save();
                    }
                }
            }   

        }
    }
}
