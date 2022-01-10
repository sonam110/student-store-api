<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryMaster;
use App\Models\AttributeMaster;
use App\Models\Brand;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;

class CategoryController extends Controller
{
    public function categoryList($moduleId, $language_id)
    {
        try
        {
            $categories = CategoryMaster::select('id', 'title', 'category_master_id', 'module_type_id','slug','vat')
            	->with(['categoryParent' => function($q) use ($language_id) {
                        $q->select('id','category_master_id','title','description')
                        ->where('language_id', $language_id)
                        ->where('is_parent', 1);
                    }])
            	->where('status', '1')
                ->orderBy('created_at','DESC')
            	->where('category_master_id', null)
            	->where('module_type_id', $moduleId)
            	->get();

            // $categories = CategoryMaster::select('id', 'title', 'category_master_id', 'module_type_id','slug')
            //     ->with(['categoryDetails' => function($q) use ($language_id) {
            //             $q->select('id','category_master_id','title','description','slug')
            //             ->where('language_id', $language_id)
            //             ->where('is_parent', 1);
            //         }])
            //     ->where('status', '1')
            //     ->orderBy('created_at','DESC')
            //     ->where('category_master_id', null)
            //     ->where('module_type_id', $moduleId)
            //     ->get();
            return response(prepareResult(false, $categories, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function subCategoryList($catId, $language_id)
    {
        try
        {
            $subcategory = CategoryMaster::select('id', 'title', 'category_master_id', 'module_type_id','slug','vat')
            	->with(['categoryDetails' => function($q) use ($language_id) {
                        $q->select('id','category_master_id','title','description','slug')
                        ->where('language_id', $language_id)
                        ->where('is_parent', 0);
                    }])
            	->where('status', '1')
                ->orderBy('created_at','DESC')
            	->where('category_master_id', null)
            	->where('id', $catId)
            	->first();


            // $subcategory = CategoryMaster::select('id', 'title', 'category_master_id', 'module_type_id','slug')
            //     ->with(['categoryDetails' => function($q) use ($language_id) {
            //             $q->select('id','category_master_id','title','description','slug')
            //             ->where('language_id', $language_id)
            //             ->where('is_parent', 0);
            //         }])
            //     ->where('status', '1')
            //     ->orderBy('created_at','DESC')
            //     ->where('category_master_id', $catId)
            //     ->get();
            return response(prepareResult(false, $subcategory, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function attributeList($catId, $language_id)
    {
        try
        {
            $attributes = AttributeMaster::select('attribute_masters.id', 'attribute_masters.bucket_group_id', 'attribute_masters.category_master_slug')
                ->join('bucket_group_details', function ($join) {
                    $join->on('attribute_masters.bucket_group_id', '=', 'bucket_group_details.bucket_group_id');
                })

                ->with(['bucketGroup:id,group_name,type,text_type,is_multiple','bucketGroup.bucketGroupAttributes:id,bucket_group_id,name','bucketGroup.bucketGroupAttributes.attributeDetails' => function($q) use ($language_id) {
                        $q->select('id','bucket_group_attribute_id','name','language_id')
                        ->where('language_id', $language_id);
                    }])
                ->whereHas('bucketGroup.bucketGroupAttributes.attributeDetails', function ($query) use ($language_id) {
                    $query->where('language_id', $language_id);
                })
                ->where('attribute_masters.category_master_slug', $catId)
                ->orderBy('attribute_masters.created_at','DESC')
                ->where('bucket_group_details.language_id', $language_id)
                ->get();
            return response(prepareResult(false, $attributes, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function brands(Request $request,$catId)
    {
        try
        {
            $brands = Brand::select('id', 'name')
                ->where('status', '1')
                ->orderBy('name','ASC');
            if($catId!='all')
            {
                $brands->where('category_master_id', $catId);
            }

            if(!empty($request->brand))
            {
                $brands = $brands->where('name', 'like', '%'.$request->brand.'%');
            }
            $brands = $brands->get();
            return response(prepareResult(false, $brands, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function createBrand(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'category_master_id'    => 'required',
            'name'                  => 'required',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }
        DB::beginTransaction();
        try
        {
            if(Brand::where('category_master_id', $request->category_master_id)->where('name', $request->name)->count()>0)
            {
                return response()->json(prepareResult(true, [], getLangByLabelGroups('messages','message_already_exist')), config('http_response.bad_request'));
            }
            $brand          = new Brand;
            $brand->category_master_id    = $request->category_master_id;
            $brand->name    = $request->name;
            $brand->status  = false;
            $brand->save();
            DB::commit();
            return response()->json(prepareResult(false, $brand, getLangByLabelGroups('messages','message_success_title')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }
}
