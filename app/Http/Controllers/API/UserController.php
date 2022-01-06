<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use App\Models\Package;
use App\Models\OrderItem;
use App\Models\UserPackageSubscription;
use Auth;

class UserController extends Controller
{
	public function index(Request $request)
	{
		try
		{
			if(!empty($request->per_page_record))
			{
			    $users = User::where('user_type_id','!=', '1')->orderBy('created_at','DESC')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
			    $users = User::where('user_type_id','!=', '1')->orderBy('created_at','DESC')->get();
			}
			return response(prepareResult(false, UserResource::collection($users), getLangByLabelGroups('messages','message_user_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function userByType($user_type_id)
	{
		try
		{
			$users = User::where('user_type_id',$user_type_id )->orderBy('created_at','DESC')->get();
			return response(prepareResult(false, UserResource::collection($users), getLangByLabelGroups('messages','message_user_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function show(User $user)
	{
		// if(!$user)
		// {
		// 	return response()->json(prepareResult(true, ['user doesnt exist.'], getLangByLabelGroups('messages','message_user_doesnt_exist')), config('http_response.internal_server_error'));
		// }		
		return response()->json(prepareResult(false, new UserResource($user), getLangByLabelGroups('messages','message_user_list')), config('http_response.success'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\User  $user
	 * @return \Illuminate\Http\Response
	 */
	
	public function update(Request $request,User $user)
	{
		$validation = \Validator::make($request->all(),[ 
			'first_name'        => 'required|max:55',
			'email'             => 'email|required|unique:users,email,'.$user->id,
			'contact_number'    => 'numeric|required|unique:users,contact_number,'.$user->id,
		]);

		if ($validation->fails()) {
			return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
		}
		if(!empty($request->dob))
		{
			$dob = date("Y-m-d", strtotime($request->dob));
		}
		else
		{
			$dob = null;
		}

		$user->first_name           = $request->first_name;
		$user->last_name            = $request->last_name;
		$user->email                = $request->email;
		$user->contact_number       = $request->contact_number;
		$user->password             = bcrypt($request->password);
		$user->gender               = $request->gender;
		$user->dob                  = $dob;
		$user->profile_pic_path     = $request->profile_pic_path;
		$user->profile_pic_thumb_path     = env('CDN_DOC_THUMB_URL').basename($request->profile_pic_path);
		$user->user_type_id         = $request->user_type_id;
		$user->language_id          = $request->language_id;
		// $user->is_verified          = true;
		// $user->is_agreed_on_terms   = true;
		// $user->is_prime_user        = true;
		// $user->is_deleted           = false;
		// $user->status               = true;
		$user->last_login           = now();
		
		if($user->save())
		{
			return response(prepareResult(false, $user, getLangByLabelGroups('messages','message_user_updated')), config('http_response.created'));
		}
		else
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

   
	public function destroy(User $user)
	{
		$user->delete();
		return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_user_deleted')), config('http_response.success'));
	}

	public function statusUpdate(Request $request, $id)
	{
		$user = User::find($id);
		if($user->update(['status',$request->status]))
		{
			return response()->json(prepareResult(false, $user, getLangByLabelGroups('messages','message_user_status_updated')), config('http_response.success'));
		}
		else
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function serviceProvidersFilter(Request $request)
	{
	    try
	    {
	        $searchType = $request->searchType; 
	        $users = User::where('id', '!=', Auth::id())
	                        ->where('user_type_id','3')
	                        ->orderBy('created_at','DESC')
	                        ->with('serviceProviderDetail','addressDetail');
	        
	        if($searchType=='latest')
	        {
	            $users->orderBy('created_at','DESC');
	        }
	        elseif($searchType=='topRated')
	        {
	             $users->orderBy('created_at','DESC');
	        }
	        if(!empty($request->per_page_record))
	        {
	            $usersData = $users->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
	        }
	        else
	        {
	            $usersData = $users->get();
	        }
	        return response(prepareResult(false, $usersData, getLangByLabelGroups('messages','messages_users_list')), config('http_response.success'));
	    }
	    catch (\Throwable $exception) 
	    {
	        return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
	    }
	}

	public function userPackageSubscriptionOrder($id)
    {
    	try
		{
			$userPackageSubscription = UserPackageSubscription::select('id','package_id','user_id','payby')->find($id);
			// return $userPackageSubscription;
			$userPackageSubscription['order_item_id'] = OrderItem::where('package_id',$userPackageSubscription->package_id)->where('user_id',$userPackageSubscription->user_id)->orderBy('created_at','desc')->first()->id;
			$userPackageSubscription['order_id'] = OrderItem::where('package_id',$userPackageSubscription->package_id)->where('user_id',$userPackageSubscription->user_id)->orderBy('created_at','desc')->first()->order_id; 
			return response(prepareResult(false, $userPackageSubscription, getLangByLabelGroups('messages','message_order_number_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		} 
    }
}
