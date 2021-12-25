<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

class CountryStateCityController extends Controller
{
    public function countries(Request $request)
    {
        $getCountry = Country::where('status', '1');
        if($request->only_sweden == true)
        {
            $getCountry->where('name','Sweden');
        }
        elseif($request->only_saudi == true)
        {
            $getCountry->where('name','Saudi Arabia');
        }
        elseif($request->only_turkey == true)
        {
            $getCountry->where('name','Turkey');
        }
        if(!empty($request->per_page_record))
        {
            $getCountry = $getCountry->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
        }
        else
        {
            $getCountry = $getCountry->get();
        }
        
        return response()->json(prepareResult(false, $getCountry, getLangByLabelGroups('messages','message_country_list')), config('http_response.success'));
    }

    public function states(Request $request,$countryID)
    {
        if(!empty($request->per_page_record))
        {
            $getState = State::where('country_id', $countryID)->where('status', '1')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
        }
        else
        {
            $getState = State::where('country_id', $countryID)->where('status', '1')->get();
        }
        return response()->json(prepareResult(false, $getState, getLangByLabelGroups('messages','message_state_list')), config('http_response.success'));
    }

    public function cities(Request $request,$stateId)
    {
        if(!empty($request->per_page_record))
        {
            $getCity = City::where('state_id', $stateId)->where('status', '1')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
        }
        else
        {
            $getCity = City::where('state_id', $stateId)->where('status', '1')->get();
        }
        return response()->json(prepareResult(false, $getCity, getLangByLabelGroups('messages','message_state_list')), config('http_response.success'));
    }

    public function countryCities($countryId)
    {
        $cities = Country::find($countryId)->cities;
        return response()->json(prepareResult(false, $cities, getLangByLabelGroups('messages','message_state_list')), config('http_response.success'));
    }

    public function citiesByCountryName($name)
    {
        $cities = Country::where('name',$name)->first()->cities;
        return response()->json(prepareResult(false, $cities, getLangByLabelGroups('messages','message_state_list')), config('http_response.success'));
    }
}
