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
            $categories = CategoryMaster::select('id', 'title', 'category_master_id', 'module_type_id')
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
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function subCategoryList($catId, $language_id)
    {
        try
        {
            // $subcategory = CategoryMaster::select('id', 'title', 'category_master_id', 'module_type_id')
            // 	->with(['subCategories.categoryDetails' => function($q) use ($language_id) {
            //             $q->select('id','category_master_id','title','description','slug')
            //             ->where('language_id', $language_id)
            //             ->where('is_parent', 0);
            //         }])
            // 	->where('status', '1')
            //     ->orderBy('created_at','DESC')
            // 	->where('category_master_id', null)
            // 	->where('id', $catId)
            // 	->first();


            $subcategory = CategoryMaster::select('id', 'title', 'category_master_id', 'module_type_id','slug')
                ->with(['categoryDetails' => function($q) use ($language_id) {
                        $q->select('id','category_master_id','title','description','slug')
                        ->where('language_id', $language_id)
                        ->where('is_parent', 0);
                    }])
                ->where('status', '1')
                ->orderBy('created_at','DESC')
                ->where('category_master_id', $catId)
                ->get();
            return response(prepareResult(false, $subcategory, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function attributeList($catId, $language_id)
    {
        try
        {
            $attributes = AttributeMaster::select('id', 'bucket_group_id', 'category_master_id')
                ->with(['bucketGroup:id,group_name,type,text_type,is_multiple','bucketGroup.bucketGroupAttributes:id,bucket_group_id,name','bucketGroup.bucketGroupAttributes.attributeDetails' => function($q) use ($language_id) {
                        $q->select('id','bucket_group_attribute_id','name')
                        ->where('language_id', $language_id);
                    }])
                ->where('category_master_id', $catId)
                ->orderBy('created_at','DESC')
                ->get();
            return response(prepareResult(false, $attributes, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function brands(Request $request,$catId)
    {
        try
        {
            $brands = Brand::select('id', 'name')
                ->where('status', '1')
                ->where('category_master_id', $catId)
                ->orderBy('created_at','DESC');

            if(!empty($request->brand))
            {
                $brands = $brands->where('name', 'like', '%'.$request->brand.'%');
            }
            $brands = $brands->get();
            return response(prepareResult(false, $brands, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
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
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }
}
