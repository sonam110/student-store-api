<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ContactList;
use App\Models\ChatList;
use App\Models\User;
use App\Models\ProductsServicesBook;
use App\Models\Job;
use App\Models\Contest;
use App\Models\NotificationTemplate;
use Auth;

class MessageController extends Controller
{

    public function contactList(Request $request)
    {
        if($request->module == 'contest')
        {
            $contactLists = ContactList::where('contest_id','!=',null)
                                ->where(function($query){
                                    $query->where('seller_id',Auth::id())
                                          ->orWhere('buyer_id',Auth::id());
                                })
                                ->orderBy('created_at','DESC')
                                ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','contest:id,title,user_id,cover_image_path,cover_image_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','contest.user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','contest.user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path')
                                ->withCount('unreadMessages')
                                ->get();
            // if($request->type == 'seller')
            // {
            //     $contactLists = ContactList::where('contest_id','!=',null)
            //                     ->where('seller_id',Auth::id())
            //                     ->orderBy('created_at','DESC')
            //                     ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','contest:id,title')
            //                     ->withCount('unreadMessages')
            //                     ->get();
            // }
            // else
            // {
            //     $contactLists = ContactList::where('contest_id','!=',null)
            //                     ->where('buyer_id',Auth::id())
            //                     ->orderBy('created_at','DESC')
            //                     ->with('contest:id,title','contest.coverImage')
            //                     ->withCount('unreadMessages')
            //                     ->get();

            // }
        }
        elseif($request->module == 'job')
        {
            $contactLists = ContactList::where('job_id','!=',null)
                                ->where(function($query){
                                    $query->where('seller_id',Auth::id())
                                          ->orWhere('buyer_id',Auth::id());
                                })
                                ->orderBy('created_at','DESC')
                                ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','job:id,user_idtitle,cover_image_path,cover_image_thumb_path','job.user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path')
                                ->withCount('unreadMessages')
                                ->get();
        }
        else
        {
            if($request->type == 'seller')
            {
                $contactLists = ContactList::where('products_services_book_id','!=',null)
                                ->where('seller_id',Auth::id())
                                ->orderBy('created_at','DESC')
                                ->with('user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','productsServicesBook:id,title')
                                ->withCount('unreadMessages')
                                ->get();
            }
            else
            {
                $contactLists = ContactList::where('products_services_book_id','!=',null)                              
                                ->where('buyer_id',Auth::id())
                                ->orderBy('created_at','DESC')
                                ->with('productsServicesBook:id,title,user_id','productsServicesBook.coverImage','productsServicesBook.user:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','productsServicesBook.user.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path')
                                ->withCount('unreadMessages')
                                ->get();

            }
        }
    	
    	
    	return response()->json(prepareResult(false, $contactLists, getLangByLabelGroups('messages','message_list')), config('http_response.created'));
    }

    public function chatList($contact_list_id)
    {
    	
    	ChatList::where('contact_list_id',$contact_list_id)->where('receiver_id',Auth::id())->update(['status'=>'read']);
    	$chatLists = ChatList::where('contact_list_id',$contact_list_id)->orderBy('created_at','asc')->with('sender:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','sender.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path','receiver:id,first_name,last_name,profile_pic_path,profile_pic_thumb_path','receiver.serviceProviderDetail:id,user_id,company_name,company_logo_path,company_logo_thumb_path')->get();
    	
    	return response()->json(prepareResult(false, $chatLists, getLangByLabelGroups('messages','message_list')), config('http_response.created'));
    }

    public function saveMessage(Request $request)
    {
    	$validation = \Validator::make($request->all(),[ 
            'message' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }
        try
        {
            $seller_id = '';
            if(!empty($request->products_services_book_id))
            {
                $seller_id = ProductsServicesBook::find($request->products_services_book_id)->user_id;
                $module = 'product';
            }
            elseif(!empty($request->job_id))
            {
                $seller_id = Job::find($request->job_id)->user_id;
                $module = 'job';
            }
            else
            {
                $seller_id = Contest::find($request->contest_id)->user_id;
                $module = 'contest';
            }

            if(Auth::id() == $seller_id)
            {
                $user_type = 'buyer';
            }
            else
            {
                $user_type = 'seller';
            }

            if($request->contact_list_id == null)
            {
                $contactList    = new ContactList;
                $contactList->products_services_book_id  = $request->products_services_book_id;
                $contactList->job_id        = $request->job_id;
                $contactList->contest_id    = $request->contest_id;
                $contactList->buyer_id      = Auth::id();
                $contactList->seller_id     = $seller_id;
                $contactList->save();

                $receiver_id = $seller_id;
            }
            else
            {
                $contactList = ContactList::find($request->contact_list_id);
                if(Auth::id() == $contactList->seller_id)
                {
                    $receiver_id = $contactList->buyer_id;
                }
                else
                {
                    $receiver_id = $contactList->seller_id;
                }
                
            }

            $chatList    = new ChatList;
            $chatList->contact_list_id  = $contactList->id;
            $chatList->sender_id        = Auth::id();
            $chatList->receiver_id      = $receiver_id;
            $chatList->message          = $request->message;
            $chatList->save();

            // Notification Start

            $user = User::find($receiver_id);
            $type = 'Chat message';

            $notificationTemplate = NotificationTemplate::where('template_for','new_message')->where('language_id',$user->language_id)->first();
            if(empty($notificationTemplate))
            {
                $notificationTemplate = NotificationTemplate::where('template_for','new_message')->first();
            }

            $body = $notificationTemplate->body;

            $arrayVal = [
                '{{message}}' => $request->message,
            ];
            

            $title = $notificationTemplate->title;
            $body = strReplaceAssoc($arrayVal, $body);

            pushNotification($title,$body,$user,$type,false,$user_type,$module,$chatList->id,'messages');

            // Notification End

            return response()->json(prepareResult(false, $contactList, getLangByLabelGroups('messages','message_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function readMessage($id)
    {
        $chat = ChatList::where('id',$id)->update(['status' => 'read']);
        $chat = ChatList::find($id);
        return response()->json(prepareResult(false, $chat, getLangByLabelGroups('messages','message_updated')), config('http_response.created'));
    }

    public function chatListCount(Request $request)
    {
        $chatList = ChatList::select('chat_lists.id')
                    ->join('contact_lists', 'contact_lists.id', '=', 'chat_lists.contact_list_id');
        if($request->prodOrCont=='1')
        {
            $record = $chatList->whereNotNull('contact_lists.contest_id')
                    ->where(function ($query) {
                        $query->where('chat_lists.sender_id', '=', Auth::id())
                              ->orWhere('chat_lists.receiver_id', '=', Auth::id());
                    });
        }
        else
        {
            if($request->userType=='buyer')
            {
                $record = $chatList->whereNotNull('contact_lists.products_services_book_id')
                    ->where('contact_lists.buyer_id', Auth::id())
                    ->where('chat_lists.receiver_id', Auth::id());
            }
            else 
            {
                $record = $chatList->whereNotNull('contact_lists.products_services_book_id')
                    ->where('contact_lists.seller_id', Auth::id())
                    ->where('chat_lists.receiver_id', Auth::id());
            }
        }
        $count = $record->where('chat_lists.status', 'unread')->count();
        return response()->json(prepareResult(false, $count, 'Unreaed message count'), config('http_response.success'));
    }
}
