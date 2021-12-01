<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\AddressDetail;

class CountryStateCityController extends Controller
{
    public function countries(Request $request)
    {
        if($request->only_sweden == true)
        {
            $getCountry = Country::where('status', '1')->where('name','Sweden')->get();
        }
        else
        {
            $getCountry = Country::where('status', '1')->get();
        }
        
        return response()->json(prepareResult(false, $getCountry, getLangByLabelGroups('messages','message_country_list')), config('http_response.success'));
    }

    public function states($countryID)
    {
        $getState = State::where('country_id', $countryID)->where('status', '1')->get();
        return response()->json(prepareResult(false, $getState, getLangByLabelGroups('messages','message_state_list')), config('http_response.success'));
    }

    public function cities($stateId)
    {
        $getCity = City::where('state_id', $stateId)->where('status', '1')->get();
        return response()->json(prepareResult(false, $getCity, getLangByLabelGroups('messages','message_state_list')), config('http_response.success'));
    }

    public function countryCities($countryId)
    {
        $cities = Country::find($countryId)->cities;
        return response()->json(prepareResult(false, $cities, getLangByLabelGroups('messages','message_state_list')), config('http_response.success'));
    }

    public function citiesByCountryName($name)
    {
        $country = Country::where('name',$name)->first();
        if($country)
        {
            return response()->json(prepareResult(false, $country->cities, getLangByLabelGroups('messages','message_state_list')), config('http_response.success'));
        }
        // $cities = AddressDetail::where('city','!=',null)->groupBy('city')->get(['city']);
        return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    }
}
