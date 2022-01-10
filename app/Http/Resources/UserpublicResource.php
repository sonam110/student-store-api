<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\FavouriteJob;
use App\Models\UserPackageSubscription;
use Auth;
use App\Models\RatingAndFeedback;

class UserpublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $educationDetails = [];
        $workExperiences = [];
        $cvDetail = [];

        if($this->user_type_id  == 2)
        {
            return [
                    'id'                                 => $this->id,
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
                    'status'                             => $this->status,
                    'student_detail'                     => $this->studentDetailPublic,
                    'user_packages'                      => array_filter(array(  
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Job')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(), 
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Product')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(),
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Service')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(),
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Book')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(), 
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Contest')->where('subscription_status', 1)->orderBy('created_at','DESC')->first()
                                                    )),
                    
                    'ratings'                            => RatingAndFeedback::where('to_user',$this->id)->limit(3)->with('customer:id,first_name,last_name,gender,email,contact_number,profile_pic_path,profile_pic_thumb_path')->get(),
                    'ratings_count'                      => $this->ratings->count(),
                    'default_address'                    => new AddressDetailResource($this->defaultAddress),
                    'user_products'                     => $this->products,
                    'user_services'                     => $this->services,
                    'user_books'                        => $this->books,
                    
                ];
        }
        else
        {
            return [
                    'id'                            => $this->id,
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
                    'short_intro'                   => $this->short_intro,
                    'status'                        => $this->status,
                    'show_email'                    => $this->show_email,
                    'show_contact_number'           => $this->show_contact_number,
                    'service_provider_detail'       => $this->serviceProviderDetail,
                    'user_packages'                      => array_filter(array(  
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Job')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(), 
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Product')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(),
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Service')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(),
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Book')->where('subscription_status', 1)->orderBy('created_at','DESC')->first(), 
                                                            UserPackageSubscription::where('user_id',$this->id)->where('module','Contest')->where('subscription_status', 1)->orderBy('created_at','DESC')->first()
                                                    )),
                    
                    'ratings'                       => RatingAndFeedback::where('to_user',$this->id)->limit(3)->with('customer')->get(),
                    'ratings_count'                 => $this->ratings->count(),
                    'default_address'                    => new AddressDetailResource($this->defaultAddress),
                    'user_products'                     => $this->products,
                    'user_services'                     => $this->services,
                    'user_books'                        => $this->books,
                ];
        }
        
    }
}
