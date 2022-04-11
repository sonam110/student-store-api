<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\FavouriteJob;
use App\Models\UserPackageSubscription;
use Auth;
use App\Models\RatingAndFeedback;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $educationDetails = [];
        $workExperiences = [];
        $cvDetail = [];
        if($this->userEducationDetails)
        {
            if($this->userEducationDetails->count() > 0)
            {
                $educationDetails = UserEducationDetailResource::collection($this->userEducationDetails);
            }
        }

        if($this->userWorkExperiences->count() > 0)
        {
            $workExperiences = UserWorkExperienceResource::collection($this->userWorkExperiences);
        }

        if($fav = FavouriteJob::where('sp_id',Auth::id())->where('sa_id',$this->id)->first())
        {
            $favouriteUser = true;
            $favouriteId = $fav->id;
        }
        else
        {
            $favouriteUser = false;
            $favouriteId = null;
        }
        dd(auth()->user());
        if(auth()->user()->user_type_id==1 && auth()->guard('api')->check())
        {
            $products = $this->products()->select('*')->limit(5)->get();
            $services = $this->services()->select('*')->limit(5)->get();
            $books = $this->books()->select('*')->limit(5)->get();
            $contests = $this->contests()->select('*')->limit(5)->get();
            $events = $this->events()->select('*')->limit(5)->get();
            $jobs = $this->jobs()->select('*')->limit(5)->get();
        }
        else
        {
            $products = [];
            $services = [];
            $books = [];
            $contests = [];
            $events = [];
            $jobs = [];
        }

        //if($this->userCvDetail->count() > 0)
        //{
            //$cvDetail = new UserCvDetailResource($this->userCvDetail);
        //}
        // return $this->userType->title;
        // return parent::toArray($request);
        if($this->user_type_id  == 2)
        {
            return [
                    'id'                                 => $this->id,
                    'language'                           => new LanguageResource($this->language),
                    'user_type'                          => new UserTypeResource($this->userType),
                    'user_type_id'                       => $this->user_type_id,
                    'first_name'                         => $this->first_name,
                    'last_name'                          => $this->last_name,
                    'gender'                             => $this->gender,
                    'dob'                                => $this->dob,
                    'email'                              => $this->email,
                    'contact_number'                     => $this->contact_number,
                    'profile_pic_path'                   => $this->profile_pic_path,
                    'profile_pic_thumb_path'        => $this->profile_pic_thumb_path,
                    'qr_code_img_path'                   => $this->qr_code_img_path,
                    'qr_code_number'                     => $this->qr_code_number,
                    'qr_code_valid_till'                 => $this->qr_code_valid_till,
                    'bank_account_num'                  => $this->bank_account_num,
                    'bank_identifier_code'              => $this->bank_identifier_code,
                    'bank_name'                         => $this->bank_name,
                    'bank_account_type'                 => $this->bank_account_type,
                    'stripe_account_id'                 => $this->stripe_account_id,
                    'stripe_status'                     => $this->stripe_status,
                    'stripe_customer_id'                => $this->stripe_customer_id,
                    'klarna_customer_token'             => $this->klarna_customer_token,
                    'short_intro'                        => $this->short_intro,
                    'status'                             => $this->status,
                    'show_email'                         => $this->show_email,
                    'show_contact_number'                => $this->show_contact_number,
                    'is_email_verified'                  => $this->is_email_verified,
                    'is_contact_number_verified'         => $this->is_contact_number_verified,
                    'is_minor'                           => $this->is_minor,
                    'social_security_number'             => $this->social_security_number,
                    'guardian_first_name'                => $this->guardian_first_name,
                    'guardian_last_name'                 => $this->guardian_last_name,
                    'guardian_email'                     => $this->guardian_email,
                    'guardian_contact_number'            => $this->guardian_contact_number,
                    'is_guardian_email_verified'         => $this->is_guardian_email_verified,
                    'is_guardian_contact_number_verified'=> $this->is_guardian_contact_number_verified,
                    'default_address'                    => new AddressDetailResource($this->defaultAddress),
                    'payment_card_details'               => $this->defaultPaymentCard,
                    'cv_detail'                          => new UserCvDetailResource($this->userCvDetail),
                    'experiences'                        => $workExperiences,
                    'educations'                         => $educationDetails,
                    'student_detail'                     => $this->studentDetail,
                    'favourite_user'                     => $favouriteUser,
                    'favourite_id'                       => $favouriteId,
                    'likes_count'                        => FavouriteJob::where('sa_id',$this->id)->where('job_id',null)->count(),
                    'reward_points'                      => $this->reward_points,
                    'ratings'                            => RatingAndFeedback::where('to_user',$this->id)->limit(3)->with('customer')->get(),
                    'ratings_count'                      => $this->ratings->count(),
                    'user_packages'                      => array_filter(array(  
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Job')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(), 
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Product')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(),
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Service')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(),
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Book')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(), 
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Contest')->where('subscription_status', 1)->orderBy('created_at','DESC')->first()
                                                    )),
                    'user_products'                     => $products,
                    'user_services'                     => $services,
                    'user_books'                        => $books,
                    'user_contests'                     => $contests,
                    'user_events'                       => $events,
                    'user_jobs'                         => $jobs,
                    'unread_notification'               => $this->unreadNotifications->count(),
                    'cool_company_freelancer'           => $this->coolCompanyFreelancer,
                    'cart_count'                        => $this->cartDetails->count()
                   
                ];
        }
        else
        {
            return [
                    'id'                            => $this->id,
                    'language'                      => new LanguageResource($this->language),
                    'user_type'                     => new UserTypeResource($this->userType),
                    'user_type_id'                  => $this->user_type_id,
                    'first_name'                    => $this->first_name,
                    'last_name'                     => $this->last_name,
                    'gender'                        => $this->gender,
                    'dob'                           => $this->dob,
                    'email'                         => $this->email,
                    'contact_number'                => $this->contact_number,
                    'profile_pic_path'              => $this->profile_pic_path,
                    'profile_pic_thumb_path'        => $this->profile_pic_thumb_path,
                    'qr_code_img_path'              => $this->qr_code_img_path,
                    'qr_code_number'                => $this->qr_code_number,
                    'qr_code_valid_till'            => $this->qr_code_valid_till,
                    'bank_account_num'              => $this->bank_account_num,
                    'bank_identifier_code'          => $this->bank_identifier_code,
                    'bank_name'                     => $this->bank_name,
                    'bank_account_type'             => $this->bank_account_type,
                    'stripe_account_id'             => $this->stripe_account_id,
                    'stripe_status'                 => $this->stripe_status,
                    'stripe_customer_id'            => $this->stripe_customer_id,
                    'klarna_customer_token'         => $this->klarna_customer_token,
                    'short_intro'                   => $this->short_intro,
                    'status'                        => $this->status,
                    'show_email'                    => $this->show_email,
                    'show_contact_number'           => $this->show_contact_number,
                    'is_email_verified'             => $this->is_email_verified,
                    'is_contact_number_verified'    => $this->is_contact_number_verified,
                    'cp_first_name'                 => $this->cp_first_name,
                    'cp_last_name'                  => $this->cp_last_name,
                    'cp_gender'                     => $this->cp_gender,
                    'cp_email'                      => $this->cp_email,
                    'cp_contact_number'             => $this->cp_contact_number,
                    'social_security_number'        => $this->social_security_number,
                    'default_address'               => new AddressDetailResource($this->defaultAddress),
                    'payment_card_details'          => $this->defaultPaymentCard,
                    'service_provider_detail'       => $this->serviceProviderDetail,
                    'ratings'                       => RatingAndFeedback::where('to_user',$this->id)->limit(3)->with('customer')->get(),
                    'ratings_count'                 => $this->ratings->count(),
                    'user_packages'                 => array_filter(array(  
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Job')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(), 
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Product')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(),
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Service')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(),
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Book')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(), 
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Contest')->where('subscription_status', 1)->orderBy('created_at','DESC')->first()
                                                    )),
                    'user_products'                     => $products,
                    'user_services'                     => $services,
                    'user_books'                        => $books,
                    'user_contests'                     => $contests,
                    'user_events'                       => $events,
                    'user_jobs'                         => $jobs,
                    'unread_notification'               => $this->unreadNotifications->count(),
                    'cool_company_freelancer'           => $this->coolCompanyFreelancer,
                    'cart_count'                        => $this->cartDetails->count()
                ];
        }
        
    }
}
