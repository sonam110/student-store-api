<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TransactionDetail;
use Auth;
use App\Models\UserPackageSubscription;
use App\Models\Language;

class TransactionDetailController extends Controller
{
    function __construct()
    {
        $this->lang_id = Language::first()->id;
        if(!empty(request()->lang_id))
        {
            $this->lang_id = request()->lang_id;
        }
    }

    public function index(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $transactionDetails = TransactionDetail::where('transaction_amount', '>',0)->orderBy('created_at','DESC')->with('order.user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','order.orderItems')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $transactionDetails = TransactionDetail::where('transaction_amount', '>',0)->orderBy('created_at','DESC')->with('order.user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','order.orderItems')->get();
            }
            return response(prepareResult(false, $transactionDetails, getLangByLabelGroups('messages','messages_transactionDetail_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function show(TransactionDetail $transactionDetail)
    {
        try
        {
            $transactionDetails = TransactionDetail::with('order.user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','order.orderItems')->find($transactionDetail->id);
            return response(prepareResult(false, $transactionDetails, getLangByLabelGroups('messages','messages_transactionDetail_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function filter(Request $request)
    {
        try
        {
            $lang_id = $this->lang_id;

            $searchType = $request->searchType; //filter, promotions, latest, closingSoon, random, criteria transactionDetail
            $transactionDetails = TransactionDetail::with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','transactionDetailTags:id,transactionDetail_id,title','addressDetail','categoryMaster','subCategory','isApplied','isFavourite','order.orderItems')
                ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '1');
                }])
                ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '0');
                }]);
            if($searchType=='filter')
            {
                if(!empty($request->category_master_id))
                {
                    $transactionDetails->where('category_master_id',$request->category_master_id);
                }
                if(!empty($request->sub_category_slug))
                {
                    $transactionDetails->where('sub_category_slug',$request->sub_category_slug);
                }

                if(!empty($request->transactionDetail_environment))
                {
                    $transactionDetails->where(function($query) use ($request) {
                        foreach ($request->transactionDetail_environment as $key => $transactionDetail_environment) {
                            if ($key === 0) {
                                $query->where('transactionDetail_environment', 'LIKE', '%'.$transactionDetail_environment.'%');
                                continue;
                            }
                            $query->orWhere('transactionDetail_environment', 'LIKE', '%'.$transactionDetail_environment.'%');
                        }
                    });
                }
                if(!empty($request->published_date))
                {
                    $transactionDetails->where('sp_transactionDetails.published_at', '<=',  date("Y-m-d", strtotime($request->published_date)))->orderBy('sp_transactionDetails.published_at','desc');
                }
                if(!empty($request->applying_date))
                {
                    $transactionDetails->where('sp_transactionDetails.application_end_date', '<=',  date("Y-m-d", strtotime($request->applying_date)))->orderBy('sp_transactionDetails.application_end_date','asc');
                }
                if(!empty($request->min_years_of_experience))
                {
                    $transactionDetails->where('years_of_experience', '>=', $request->min_years_of_experience);
                }
                if(!empty($request->max_years_of_experience))
                {
                    $transactionDetails->where('years_of_experience', '<=', $request->max_years_of_experience);
                }
                if(!empty($request->known_languages))
                {
                    $transactionDetails->where(function($query) use ($request) {
                        foreach ($request->known_languages as $key => $known_languages) {
                            if ($key === 0) {
                                $query->where('known_languages', 'LIKE', '%'.$known_languages.'%');
                                continue;
                            }
                            $query->orWhere('known_languages', 'LIKE', '%'.$known_languages.'%');
                        }
                    });
                }
                if(!empty($request->transactionDetail_type))
                {
                    $transactionDetails->where(function($query) use ($request) {
                        foreach ($request->transactionDetail_type as $key => $transactionDetail_type) {
                            if ($key === 0) {
                                $query->where('transactionDetail_type', 'LIKE', '%'.$transactionDetail_type.'%');
                                continue;
                            }
                            $query->orWhere('transactionDetail_type', 'LIKE', '%'.$transactionDetail_type.'%');
                        }
                    });
                }
                if(!empty($request->transactionDetail_tags))
                {
                    $transactionDetails->join('transactionDetail_tags', function ($join) {
                        $join->on('sp_transactionDetails.id', '=', 'transactionDetail_tags.transactionDetail_id');
                    });
                    $transactionDetails->where(function($query) use ($request) {
                        foreach ($request->transactionDetail_tags as $key => $transactionDetail_tags) {
                            if ($key === 0) {
                                $query->where('transactionDetail_tags.title', 'LIKE', '%'.$transactionDetail_tags.'%');
                                continue;
                            }
                            $query->orWhere('transactionDetail_tags.title', 'LIKE', '%'.$transactionDetail_tags.'%');
                        }
                    });
                }
                if(!empty($request->search_title))
                {
                    $transactionDetails->where('title', 'LIKE', '%'.$request->search_title.'%');
                }

                //future: distance filter implement
                /*if(!empty($request->distance))
                {
                    $transactionDetails->where('distance', $request->distance);
                }*/
                if(!empty($request->city))
                {
                    $transactionDetails->where('city', $request->city);
                }
                // $transactionDetails->where('application_start_date','<=', date('Y-m-d'))
                //     ->where('application_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='promotions')
            {
                $transactionDetails->where('is_promoted', '1')
                    ->where('promotion_start_date','<=', date('Y-m-d'))
                    ->where('promotion_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='latest')
            {
                $transactionDetails->orderBy('created_at','DESC')
                    ->where('application_start_date','<=', date('Y-m-d'))
                    ->where('application_end_date','>=', date('Y-m-d'));
            }
            elseif($searchType=='closingSoon')
            {
                $transactionDetails->whereBetween('application_end_date', [date('Y-m-d'), date('Y-m-d', strtotime("+2 days"))]);
            }
            elseif($searchType=='random')
            {
                $transactionDetails->where('application_start_date','<=', date('Y-m-d'))
                    ->where('application_end_date','>=', date('Y-m-d'))
                    ->inRandomOrder();
            }
            elseif($searchType=='criteria')
            {
                //TransactionDetail env, work-exp, city, title, skills, 
                $userCvDetail = UserCvDetail::where('user_id', Auth::id())->first();
                if(!$userCvDetail)
                {
                    return response()->json(prepareResult(true, 'CV not updated', getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
                }
                $transactionDetailAllArray = array();

                //TransactionDetail env
                if(!empty($userCvDetail->preferred_transactionDetail_env))
                {
                    $transactionDetailsIdsMatch = TransactionDetail::select('sp_transactionDetails.id')
                        ->where('is_published', '1')
                        ->where('application_start_date','<=', date('Y-m-d'))
                        ->where('application_end_date','>=', date('Y-m-d'));
                    $transactionDetail_environments = json_decode($userCvDetail->preferred_transactionDetail_env, true);
                    $transactionDetailsIdsMatch->where(function($query) use ($transactionDetail_environments) {
                        foreach ($transactionDetail_environments as $key => $transactionDetail_environment) {
                            if ($key === 0) {
                                $query->where('transactionDetail_environment', 'LIKE', '%'.$transactionDetail_environment.'%');
                                continue;
                            }
                            $query->orWhere('transactionDetail_environment', 'LIKE', '%'.$transactionDetail_environment.'%');
                        }
                    });
                }
                $transactionDetailEnvs = $transactionDetailsIdsMatch->get();

                foreach ($transactionDetailEnvs as $key => $transactionDetailId) {
                    $transactionDetailAllArray[] = $transactionDetailId->id;
                }

                //work-exp
                if(!empty($userCvDetail->total_experience))
                {
                    $transactionDetailsIdsMatch = TransactionDetail::select('sp_transactionDetails.id')
                        ->where('is_published', '1')
                        ->where('application_start_date','<=', date('Y-m-d'))
                        ->where('application_end_date','>=', date('Y-m-d'));

                    $transactionDetailsIdsMatch->where('years_of_experience', '<=', $userCvDetail->total_experience);
                    $transactionDetailTotalExps = $transactionDetailsIdsMatch->get();
                
                    foreach ($transactionDetailTotalExps as $key => $transactionDetailId) {
                        $transactionDetailAllArray[] = $transactionDetailId->id;
                    }
                }

                //city
                if($userCvDetail->user->defaultAddress)
                {
                    $transactionDetailsIdsMatch = TransactionDetail::select('sp_transactionDetails.id')
                        ->where('is_published', '1')
                        ->where('application_start_date','<=', date('Y-m-d'))
                        ->where('application_end_date','>=', date('Y-m-d'));

                    $cityName = $userCvDetail->user->defaultAddress->city;
                    $transactionDetailsIdsMatch->with(['addressDetail' => function($q) use ($cityName) {
                        $q->where('city', $cityName);
                    }]);

                    $transactionDetailCities = $transactionDetailsIdsMatch->get();
                
                    foreach ($transactionDetailCities as $key => $transactionDetailId) {
                        $transactionDetailAllArray[] = $transactionDetailId->id;
                    }
                }

                //skills
                if(!empty($userCvDetail->key_skills))
                {
                    $transactionDetailsIdsMatch = TransactionDetail::select('sp_transactionDetails.id')
                        ->where('is_published', '1')
                        ->where('application_start_date','<=', date('Y-m-d'))
                        ->where('application_end_date','>=', date('Y-m-d'));

                    $key_skills = json_decode($userCvDetail->key_skills, true);

                    $transactionDetailsIdsMatch->join('transactionDetail_tags', function ($join) {
                        $join->on('sp_transactionDetails.id', '=', 'transactionDetail_tags.transactionDetail_id');
                    });
                    $transactionDetailsIdsMatch->where(function($query) use ($key_skills) {
                        foreach ($key_skills as $key => $skill) {
                            if ($key === 0) {
                                $query->where('transactionDetail_tags.title', 'LIKE', '%'.$skill.'%');
                                continue;
                            }
                            $query->orWhere('transactionDetail_tags.title', 'LIKE', '%'.$skill.'%');
                        }
                    });

                    $transactionDetailSkills = $transactionDetailsIdsMatch->get();
                
                    foreach ($transactionDetailSkills as $key => $transactionDetailId) {
                        $transactionDetailAllArray[] = $transactionDetailId->id;
                    }
                }

                //title
                if(!empty($userCvDetail->title))
                {
                    $transactionDetailsIdsMatch = TransactionDetail::select('sp_transactionDetails.id')
                        ->where('is_published', '1')
                        ->where('application_start_date','<=', date('Y-m-d'))
                        ->where('application_end_date','>=', date('Y-m-d'));

                    $cvTitle = $userCvDetail->title;
                    $transactionDetailsIdsMatch->where(function($query) use ($cvTitle) {
                        $query->where('sp_transactionDetails.title', 'LIKE', '%'.$cvTitle.'%');
                    });

                    $transactionDetailTitles = $transactionDetailsIdsMatch->get();
                
                    foreach ($transactionDetailTitles as $key => $transactionDetailId) {
                        $transactionDetailAllArray[] = $transactionDetailId->id;
                    }
                }

                $actualArray = array();
                $allIds = array_count_values($transactionDetailAllArray);
                foreach ($allIds as $key => $value) {
                    if($value>2)
                    {
                        $actualArray[] = $key;
                    }
                }
                $transactionDetails = TransactionDetail::whereIn('id',$actualArray)
                        ->where('is_published', '1')
                        ->with('user:id,first_name,last_name,gender,dob,email,contact_number,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail','transactionDetailTags:id,transactionDetail_id,title','addressDetail','categoryMaster','subCategory')
                ->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '1');
                }])
                ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                    $q->select('id','category_master_id','title','slug')
                        ->where('language_id', $lang_id)
                        ->where('is_parent', '0');
                }]);
            }
            if(!empty($request->per_page_record))
            {
                $transactionDetailsData = $transactionDetails->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $transactionDetailsData = $transactionDetails->get();
            }
            if($request->other_function=='yes')
            {
                return $transactionDetailsData;
            }
            return response(prepareResult(false, $transactionDetailsData, getLangByLabelGroups('messages','messages_transactionDetail_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }


    public function destroy($transactionDetail_id)
    {
        try
        {
            $transactionDetail = TransactionDetail::find($transactionDetail_id);
            if($transactionDetail->transactionDetailApplications->count() > 0)
            {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','messages_transactionDetail_applicatons_exists')), config('http_response.success'));
            }
            $transactionDetail->delete();
            return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_transactionDetail_deleted')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}
