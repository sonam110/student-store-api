<?php

namespace App\Http\Controllers\API\Admin;


use App\Http\Controllers\Controller;
use App\Models\Abuse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use App\Models\ProductsServicesBook;
use App\Models\Contest;
use App\Models\Job;
use DB;

class AbuseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        try
        {
            $abuses = Abuse::orderBy('abuses.created_at','desc');
            // if(!empty($request->product_title))
            // {
            //     $abuses->join('products_services_books', function ($join) {
            //                 $join->on('abuses.products_services_book_id', '=', 'products_services_books.id');
            //             })
            //         ->where('products_services_books.title','like','%'.$request->product_title.'%');
            // }
            // if(!empty($request->job_title))
            // {
            //     $abuses->join('sp_jobs', function ($join) {
            //                 $join->on('abuses.job_id', '=', 'sp_jobs.id');
            //             })
            //         ->where('sp_jobs.title','like','%'.$request->job_title.'%');
            // }

            // if(!empty($request->contest_title))
            // {
            //     $abuses->join('contests', function ($join) {
            //                 $join->on('abuses.contest_id', '=', 'contests.id');
            //             })
            //         ->where('contests.title','like','%'.$request->contest_title.'%');
            // }

            if(!empty($request->type))
            {
                if(($request->type == 'product') || ($request->type == 'service') || ($request->type == 'book'))
                {
                    $abuses->join('products_services_books', function ($join) {
                            $join->on('abuses.products_services_book_id', '=', 'products_services_books.id');
                        })
                    ->where('products_services_books.type',$request->type);
                }
                elseif(($request->type == 'contest') || ($request->type == 'event'))
                {
                    $abuses->join('contests', function ($join) {
                            $join->on('abuses.contest_id', '=', 'contests.id');
                        })
                    ->where('contests.type',$request->type);
                }
                else
                {
                    $abuses->whereNotNull('abuses.job_id');
                }
                
            }
            if(!empty($request->products_services_book_id))
            {
                $abuses->whereIn('abuses.products_services_book_id',$request->products_services_book_id);
            }
            if(!empty($request->contest_id))
            {
                $abuses->whereIn('abuses.contest_id',$request->contest_id);
            }
            if(!empty($request->job_id))
            {
                $abuses->whereIn('abuses.job_id',$request->job_id);
            }
            if(!empty($request->user_id))
            {
                $abuses->whereIn('abuses.user_id',$request->user_id);
            }
            if(!empty($request->user_name))
            {
                $abuses->join('users', function ($join) {
                            $join->on('abuses.user_id', '=', 'users.id');
                        })
                    ->where('users.first_name','like','%'.$request->user_name.'%')
                    ->orWhere('users.last_name','like','%'.$request->user_name.'%');
            }
            if(!empty($request->per_page_record))
            {
                $abuses = $abuses->with('product','contest','job','user')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $abuses = $abuses->with('product','contest','job','user')->get();
            }
            return response(prepareResult(false, $abuses, getLangByLabelGroups('messages','message_abuse_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    
    public function show(Abuse $abuse)
    {
        $abuse = Abuse::with('product','contest','job','user')->find($abuse->id);
        return response()->json(prepareResult(false, $abuse, getLangByLabelGroups('messages','message_abuse_list')), config('http_response.success'));
    }

    public function statusUpdate(Request $request,$id)
    {
        $abuse = Abuse::find($id)->update(['status'=>$request->status]);
        $abuse = Abuse::with('product','contest','job','user')->find($id);
        return response()->json(prepareResult(false, $abuse, getLangByLabelGroups('messages','message_abuse_updated')), config('http_response.success'));
    }

    public function multipleStatusUpdate(Request $request)
    {
        $abuse = Abuse::whereIn('id',$request->abuse_id)->update(['status'=>$request->status]);

        $abuse = Abuse::whereIn('id',$request->abuse_id)->with('product','contest','job','user')->get();

        foreach ($abuse as $key => $value) {

            if(!empty($value->products_services_book_id))
            {
                $product = ProductsServicesBook::find($value->products_services_book_id);
                $module = 'Product';
                $body =  'Abuse status  for product '.$product->title.' has been updated to  '.$request->status.' .';
            }
            elseif(!empty($value->contest_id))
            {
                $product = Contest::find($value->contest_id);
                $module = 'Contest';
                $body =  'Abuse status for contest '.$product->title.' has been updated to  '.$request->status.' .';
            }
            else
            {
                $product = Job::find($value->job_id);
                $module = 'Job';
                $body =  'Abuse status for job '.$product->title.' has been updated to  '.$request->status.' .';
            }

            

            $title = 'Abuse Status updated';
            
                
            $type = 'Abuse';
            pushNotification($title,$body,$value->user,$type,true,'buyer',$module,$product->id,'Abuse-list');
            # code...
        }
        return response()->json(prepareResult(false, $abuse, getLangByLabelGroups('messages','message_abuse_updated')), config('http_response.success'));
    }

    
    public function destroy(Abuse $abuse)
    {
        $abuse->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_abuse_deleted')), config('http_response.success'));
    }
}