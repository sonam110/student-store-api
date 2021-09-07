<?php

namespace App\Http\Controllers\API\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductTag;
use Auth;
use DB;

class ProductsServicesBookTagController extends Controller
{

    public function index(Request $request)
    {
        try
        {
            // if(!empty($request->per_page_record))
            // {
            //     $record = ProductTag::query();
            //     if(!empty($request->type))
            //     {
            //         $record->where('type', $request->type);
            //     }
            //     $tags = $record->groupBy('title')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record,'type' => $request->type]);
            // }
            // else
            // {
            //     $record = ProductTag::query();
            //     if(!empty($request->type))
            //     {
            //         $record->where('type', $request->type);
            //     }
            //     $tags = $record->groupBy('title')->get();
            // }

            $record = ProductTag::groupBy('title');
            if(!empty($request->type))
            {
                $record->where('type', $request->type);
            }
            if(!empty($request->tags))
            {
                $record->where('title', 'like', '%'.$request->tags.'%');
            }
            else
            {
                $record->limit(15);
            }

            $tags = $record->get();
            return response(prepareResult(false, $tags, getLangByLabelGroups('messages','messages_tags_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}
