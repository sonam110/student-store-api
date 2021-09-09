<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryMaster;
use App\Models\CategoryDetail;
use App\Models\Language;

use App\Http\Resources\CategoryMasterResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CategoriesImport;

class CategoryMasterController extends Controller
{
    public function index(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $categoryMasters = CategoryMaster::with('categoryDetails','subCategories.categoryDetails')->where('category_master_id',null)->orderBy('created_at','DESC')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $categoryMasters = CategoryMaster::with('categoryDetails','subCategories.categoryDetails')->where('category_master_id',null)->orderBy('created_at','DESC')->get();
            }
            return response(prepareResult(false, $categoryMasters, getLangByLabelGroups('messages','message__category_master_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {        
        $validation = Validator::make($request->all(), [
            'module_type_id'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $category = $request->category;
            $cat_slug_prefix = (string) \Uuid::generate(4);
            $sub_cat_slug_prefix = (string) \Uuid::generate(4);
            foreach ($category as $key => $value) {
                if($key == 0)
                {
                    $categoryMaster = new CategoryMaster;
                    $categoryMaster->module_type_id     = $request->module_type_id;
                    $categoryMaster->category_master_id = null;
                    $categoryMaster->title              = $value['category_title'];
                    $categoryMaster->slug               = $cat_slug_prefix.'-'.Str::slug($value['category_title']);
                    $categoryMaster->status             = 1;
                    $categoryMaster->save();
                }
                $cat_parent_id = $categoryMaster->id;
                if($key > 0)
                {
                    $categoryMaster = CategoryMaster::find($cat_parent_id);
                }

                if($categoryMaster)
                {
                    if(Language::where('title',$value['language_title'])->count() > 0)
                    {
                        $language = Language::where('title',$value['language_title'])->first();
                    }
                    else
                    {
                        $language = new Language;
                        $language->title                = $value['language_title'];
                        $language->value                = $value['language_value'];
                        $language->status               = 1;
                        $language->save();
                    }

                    $categoryDetail = new CategoryDetail;
                    $categoryDetail->category_master_id = $categoryMaster->id;
                    $categoryDetail->language_id        = $language->id;
                    $categoryDetail->is_parent          = 1;
                    $categoryDetail->title              = $value['category_title'];
                    $categoryDetail->slug               = $cat_slug_prefix.'-'.Str::slug($categoryMaster->title);
                    $categoryDetail->description        = $request->description;
                    $categoryDetail->status             = 1;
                    $categoryDetail->save();
                }

                $subCategory = $value['subcategories'];

                foreach ($subCategory as $subkey => $subvalue) {
                    if($key == 0)
                    {
                        $subCategoryMaster = new CategoryMaster;
                        $subCategoryMaster->module_type_id     = $request->module_type_id;
                        $subCategoryMaster->category_master_id = $categoryMaster->id;
                        $subCategoryMaster->title              = $subvalue;
                        $subCategoryMaster->slug               = $sub_cat_slug_prefix.'-'.Str::slug($subvalue);
                        $subCategoryMaster->status             = 1;
                        $subCategoryMaster->save();

                        $sub_cat_parent_id[$subkey] = $subCategoryMaster->id;
                    }
                    $parent_id = $sub_cat_parent_id;
                    
                    if($key > 0)
                    {
                        $subCategoryMaster = CategoryMaster::find($sub_cat_parent_id[$subkey]);
                    }

                    if($subCategoryMaster)
                    {
                        $categoryDetail = new CategoryDetail;
                        $categoryDetail->category_master_id = $subCategoryMaster->id;
                        $categoryDetail->language_id        = $language->id;
                        $categoryDetail->is_parent          = 0;
                        $categoryDetail->title              = $subvalue;
                        $categoryDetail->slug               = $sub_cat_slug_prefix.'-'.Str::slug($subCategoryMaster->title);
                        $categoryDetail->description        = $request->description;
                        $categoryDetail->status             = 1;
                        $categoryDetail->save();
                    }
                }
            }
            DB::commit();
            return response()->json(prepareResult(false, new CategoryMasterResource($categoryMaster), getLangByLabelGroups('messages','message_category_master_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function storeOld(Request $request)
    {        
        $validation = Validator::make($request->all(), [
            'title'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $categoryMaster = new CategoryMaster;
            $categoryMaster->module_type_id     = $request->module_type_id;
            $categoryMaster->category_master_id = $request->category_master_id;
            $categoryMaster->title            	= $request->title;
            $categoryMaster->slug 				= Str::slug($request->title);
            $categoryMaster->status    			= $request->status;
            $categoryMaster->save();
            if($categoryMaster)
            {
                if(!empty($request->category_master_id))
                {
                    $is_parent = 0;
                    $category_master_id = $request->category_master_id;
                }
                else
                {
                    $is_parent = 1;
                    $category_master_id = $categoryMaster->id;
                }
                $categoryDetail = new CategoryDetail;
                $categoryDetail->category_master_id = $category_master_id;
                $categoryDetail->language_id        = !empty($request->language_id) ? $request->language_id : 1;
                $categoryDetail->is_parent          = $is_parent;
                $categoryDetail->title              = $request->title;
                $categoryDetail->slug               = Str::slug($request->title);
                $categoryDetail->description        = $request->description;
                $categoryDetail->status             = $request->status;
                $categoryDetail->save();
            }
            DB::commit();
            return response()->json(prepareResult(false, new CategoryMasterResource($categoryMaster), getLangByLabelGroups('messages','message_category_master_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CategoryMaster  $categoryMaster
     * @return \Illuminate\Http\Response
     */
    public function show(CategoryMaster $categoryMaster)
    {
        $categoryMaster = CategoryMaster::with('categoryDetails','subCategories.categoryDetails')->find($categoryMaster->id);
        return response()->json(prepareResult(false, $categoryMaster, getLangByLabelGroups('messages','message_category_master_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CategoryMaster  $categoryMaster
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request)
    {        
        $validation = Validator::make($request->all(), [
            'category_master_id'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $category = $request->category;
            $sub_cat_slug_prefix = (string) \Uuid::generate(4);
            CategoryDetail::where('category_master_id',$request->category_master_id)->delete();
            foreach ($category as $key => $value) {
                if($key == 0)
                {
                    $categoryMaster = CategoryMaster::find($request->category_master_id);
                    $categoryMaster->category_master_id = null;
                    $categoryMaster->title              = $value['category_title'];
                    $categoryMaster->save();
                }
                $cat_parent_id = $categoryMaster->id;
                if($key > 0)
                {
                    $categoryMaster = CategoryMaster::find($cat_parent_id);
                }

                if($categoryMaster)
                {
                    if(Language::where('title',$value['language_title'])->count() > 0)
                    {
                        $language = Language::where('title',$value['language_title'])->first();
                    }
                    else
                    {
                        $language = new Language;
                        $language->title                = $value['language_title'];
                        $language->value                = $value['language_value'];
                        $language->status               = 1;
                        $language->save();
                    }

                    $categoryDetail = new CategoryDetail;
                    $categoryDetail->category_master_id = $categoryMaster->id;
                    $categoryDetail->language_id        = $language->id;
                    $categoryDetail->is_parent          = 1;
                    $categoryDetail->title              = $value['category_title'];
                    $categoryDetail->slug               = $categoryMaster->slug;
                    $categoryDetail->status             = 1;
                    $categoryDetail->save();
                }

                $subCategory = $value['subcategories'];

                foreach ($subCategory as $subkey => $subvalue) {
                    if($key == 0)
                    {
                        if(!empty($subvalue['subcategory_id']))
                        {
                            CategoryDetail::where('category_master_id',$subvalue['subcategory_id'])->delete();

                            $subCategoryMaster = CategoryMaster::find($subvalue['subcategory_id']);
                            $subCategoryMaster->title              = $subvalue['subcategory_title'];
                            $subCategoryMaster->save();
                        }
                        else
                        {
                            $subCategoryMaster = new CategoryMaster;
                            $subCategoryMaster->module_type_id     = $categoryMaster->module_type_id;
                            $subCategoryMaster->category_master_id = $categoryMaster->id;
                            $subCategoryMaster->title              = $subvalue['subcategory_title'];
                            $subCategoryMaster->slug               = $sub_cat_slug_prefix.'-'.Str::slug($subCategoryMaster->title);
                            $subCategoryMaster->status             = 1;
                            $subCategoryMaster->save();
                        }

                        $sub_cat_parent_id[$subkey] = $subCategoryMaster->id;
                    }
                    $parent_id = $sub_cat_parent_id;
                    
                    if($key > 0)
                    {
                        $subCategoryMaster = CategoryMaster::find($sub_cat_parent_id[$subkey]);
                    }

                    if($subCategoryMaster)
                    {
                        $categoryDetail = new CategoryDetail;
                        $categoryDetail->category_master_id = $subCategoryMaster->id;
                        $categoryDetail->language_id        = $language->id;
                        $categoryDetail->is_parent          = 0;
                        $categoryDetail->title              = $subvalue['subcategory_title'];
                        $categoryDetail->slug               = $subCategoryMaster->slug;
                        $categoryDetail->status             = 1;
                        $categoryDetail->save();
                    }
                }
            }
            DB::commit();
            return response()->json(prepareResult(false, new CategoryMasterResource($categoryMaster), getLangByLabelGroups('messages','message_category_master_updated')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
    
    public function updateOld(Request $request,CategoryMaster $categoryMaster)
    {
        $validation = Validator::make($request->all(), [
            'title' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $categoryMaster->module_type_id     = $request->module_type_id;
            $categoryMaster->category_master_id = $request->category_master_id;
            $categoryMaster->title              = $request->title;
            $categoryMaster->slug               = Str::slug($request->title);
            $categoryMaster->status             = $request->status;
            $categoryMaster->save();
            if($categoryMaster)
            {
                $categoryDetail = CategoryDetail::where('category_master_id', $categoryMaster->id)->first();
                $categoryDetail->title              = $request->title;
                $categoryDetail->slug               = Str::slug($request->title);
                $categoryDetail->description        = $request->description;
                $categoryDetail->status             = $request->status;
                $categoryDetail->save();
            }
            DB::commit();
            return response()->json(prepareResult(false, new CategoryMasterResource($categoryMaster), getLangByLabelGroups('messages','message_category_master_updated')), config('http_response.success'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\CategoryMaster $categoryMaster
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(CategoryMaster $categoryMaster)
    {
        $categoryMaster->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_category_master_deleted')), config('http_response.success'));
    }

    public function categoriesImport(Request $request)
    {
        $data = ['language_title' => $request->language_title, 'language_value' => $request->language_value,'module_type_id' => $request->module_type_id];
        $import = Excel::import(new CategoriesImport($data),request()->file('file'));

        return response(prepareResult(false, [], getLangByLabelGroups('messages','messages_products_services_book_imported')), config('http_response.success'));
    }


    public function subCategorydelete($id)
    {
        $subCat = CategoryDetail::find($id);
        CategoryMaster::where('category_master_id',$subCat->category_master_id)->where('title',$subCat->title)->delete();
        $subCat->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_category_master_deleted')), config('http_response.success'));
    }
}
