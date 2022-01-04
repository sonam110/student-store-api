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
use App\Models\ProductsServicesBook;
use App\Models\Contest;
use App\Models\Job;


class CategoryMasterController extends Controller
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
            $categoryMasters = CategoryMaster::orderBy('created_at','DESC');
            if(!empty($request->module_type_id))
            {
                $categoryMasters = $categoryMasters->where('module_type_id',$request->module_type_id);
            }

            if(!empty($request->title))
            {
                $categoryMasters = $categoryMasters->where('title','like', '%'.$request->title.'%');
            }

            // if(!empty($request->language_id))
            // {
            //     $categoryMasters = $categoryMasters->join('category_details', function ($join) {
            //             $join->on('category_masters.id', '=', 'category_details.category_master_id');
            //         })
            //     ->where('category_details.language_id',$request->language_id)
            //     ->where('category_details.is_parent',1);
            // }

            if(!empty($request->per_page_record))
            {
                $categoryMasters = $categoryMasters->with('categoryLanguageDetails','subcategories.categoryLanguageDetails','moduleType')->where('category_master_id',null)->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $categoryMasters = $categoryMasters->with('categoryLanguageDetails','subcategories.categoryLanguageDetails','moduleType')->where('category_master_id',null)->get();
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
                    if(CategoryMaster::where('module_type_id', $request->module_type_id)->where('title', $value['category_title'])->count()<1)
                    {
                        $categoryMaster = new CategoryMaster;
                        $categoryMaster->module_type_id     = $request->module_type_id;
                        $categoryMaster->category_master_id = null;
                        $categoryMaster->title              = $value['category_title'];
                        $categoryMaster->slug               = $cat_slug_prefix.'-'.Str::slug($value['category_title']);
                        $categoryMaster->status             = 1;
                        $categoryMaster->vat                = $request->vat;
                        $categoryMaster->save();
                    }
                    else
                    {
                        $categoryMaster = CategoryMaster::where('module_type_id', $request->module_type_id)->where('title', $value['category_title'])->first();
                    }
                    
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

                    if(CategoryDetail::where('category_master_id', $categoryMaster->id)->where('language_id', $language->id)->where('title', $value['category_title'])->count()<1)
                    {
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
                }

                $subCategory = $value['subcategories'];

                foreach ($subCategory as $subkey => $subvalue) {
                    if($key == 0)
                    {
                        if(CategoryMaster::where('module_type_id', $request->module_type_id)->where('category_master_id', $categoryMaster->id)->where('title', $subvalue)->count()<1)
                        {

                            $subCategoryMaster = new CategoryMaster;
                            $subCategoryMaster->module_type_id     = $request->module_type_id;
                            $subCategoryMaster->category_master_id = $categoryMaster->id;
                            $subCategoryMaster->title              = $subvalue;
                            $subCategoryMaster->slug               = $sub_cat_slug_prefix.'-'.Str::slug($subvalue);
                            $subCategoryMaster->status             = 1;
                            $subCategoryMaster->vat                = $categoryMaster->vat;
                            $subCategoryMaster->save();
                        }
                        else
                        {
                            $subCategoryMaster = CategoryMaster::where('module_type_id', $request->module_type_id)->where('category_master_id', $categoryMaster->id)->where('title', $subvalue)->first();
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
                        if(CategoryDetail::where('category_master_id', $subCategoryMaster->category_master_id)->where('language_id', $language->id)->where('title', $subvalue)->count()<1)
                        {
                            $categoryDetail = new CategoryDetail;
                            $categoryDetail->category_master_id = $subCategoryMaster->category_master_id;
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
            $categoryMaster->title              = $request->title;
            $categoryMaster->slug               = Str::slug($request->title);
            $categoryMaster->status             = $request->status;
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
        $categoryMaster = CategoryMaster::with('categoryLanguageDetails','subcategories.categoryLanguageDetails','moduleType')->find($categoryMaster->id);
        return response()->json(prepareResult(false, $categoryMaster, getLangByLabelGroups('messages','message_category_master_list')), config('http_response.success'));
    }

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
            CategoryDetail::where('category_master_id',$request->category_master_id)->where('is_parent', '0')->delete();
            $slugArray = [];
            foreach ($category as $key => $value) 
            {
                $nullSlug = [];
                $language = Language::where('title',$value['language_title'])->first();

                if($key == 0)
                {
                    $categoryMaster = CategoryMaster::find($request->category_master_id);
                    $categoryMaster->title              = $value['category_title'];
                    $categoryMaster->vat                = $request->vat;
                    if($categoryMaster->vat!==$request->vat)
                    {
                        //update price if VAT changed
                        $categoryID = $request->category_master_id;
                        $vat_percentage = $request->vat;
                        $type = $categoryMaster->moduleType->slug;
                        updatePrice($categoryID, $vat_percentage, $type);
                    }
                    $categoryMaster->save();
                }
                $cat_parent_id = $categoryMaster->id;
                if($key > 0)
                {
                    $categoryMaster = CategoryMaster::find($cat_parent_id);
                }

                $detailInfo = CategoryDetail::where('category_master_id', $request->category_master_id)->where('language_id', $language->id)->first();
                    $detailInfo->title = $value['category_title'];
                    $detailInfo->save();
                    
                if($categoryMaster)
                {
                    $subCategory = $value['subcategories'];
                    foreach ($subCategory as $subkey => $subvalue) 
                    {
                        if($key == 0)
                        {
                            if(!empty($subvalue['subcategory_id']))
                            {
                                $subCategoryMaster = CategoryMaster::where('slug', $subvalue['slug'])->first();
                                if($subCategoryMaster)
                                {
                                    $subCategoryMaster->title   = $subvalue['subcategory_title'];
                                    $subCategoryMaster->vat     = $categoryMaster->vat;
                                    $subCategoryMaster->save();
                                }
                            }
                            else
                            {
                                if(CategoryMaster::where('module_type_id', $categoryMaster->module_type_id)->where('category_master_id', $categoryMaster->id)->where('title',$subvalue['subcategory_title'])->count()<1)
                                {
                                    $subCategoryMaster = new CategoryMaster;
                                    $subCategoryMaster->module_type_id     = $categoryMaster->module_type_id;
                                    $subCategoryMaster->category_master_id = $categoryMaster->id;
                                    $subCategoryMaster->title = $subvalue['subcategory_title'];
                                    $subCategoryMaster->slug = $sub_cat_slug_prefix.'-'.Str::slug($subvalue['subcategory_title']);
                                    $subCategoryMaster->status = 1;
                                    $subCategoryMaster->save();
                                }
                                else
                                {
                                    $subCategoryMaster = CategoryMaster::where('module_type_id', $categoryMaster->module_type_id)->where('category_master_id', $categoryMaster->id)->where('title',$subvalue['subcategory_title'])->first();
                                }
                            }
                            $slugArray[] = $subCategoryMaster->slug;
                        }

                        
                        //now detail added
                        $subCatDetail = new CategoryDetail;
                        $subCatDetail->category_master_id = $categoryMaster->id;
                        $subCatDetail->language_id        = $language->id;
                        $subCatDetail->is_parent          = 0;
                        $subCatDetail->title              = $subvalue['subcategory_title'];
                        $subCatDetail->slug               = $slugArray[$subkey];
                        $subCatDetail->status             = 1;
                        $subCatDetail->save();
                    }
                }
            }
            DB::commit();
            return response()->json(prepareResult(false, new CategoryMasterResource($categoryMaster), getLangByLabelGroups('messages','message_category_master_updated')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            dd($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function update2(Request $request)
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
                    $categoryMaster->vat                = $request->vat;
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

                    if(CategoryDetail::where('category_master_id', $categoryMaster->id)->where('language_id', $language->id)->where('is_parent', 1)->count()<1)
                    {
                        $categoryDetail = new CategoryDetail;
                    }
                    else
                    {
                        $categoryDetail = CategoryDetail::where('category_master_id', $categoryMaster->id)->where('language_id', $language->id)->where('is_parent', 1)->first();
                    }
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
                            $subcat_slug = CategoryMaster::find($subvalue['subcategory_id'])->slug;
                            CategoryDetail::where('slug',$subcat_slug)->delete();

                            $subCategoryMaster = CategoryMaster::find($subvalue['subcategory_id']);
                            if(!$subCategoryMaster)
                            {
                                $subCategoryMaster = new CategoryMaster;
                            }   
                            $subCategoryMaster->title              = $subvalue['subcategory_title'];
                            $subCategoryMaster->vat                = $categoryMaster->vat;
                            $subCategoryMaster->save();
                        }
                        else
                        {
                            if(CategoryMaster::where('module_type_id', $categoryMaster->module_type_id)->where('category_master_id', $categoryMaster->id)->where('title', $subvalue['subcategory_title'])->count()<1)
                            {
                                $subCategoryMaster = new CategoryMaster;
                                $subCategoryMaster->module_type_id     = $categoryMaster->module_type_id;
                                $subCategoryMaster->category_master_id = $categoryMaster->id;
                                $subCategoryMaster->title              = $subvalue['subcategory_title'];
                                $subCategoryMaster->slug               = $sub_cat_slug_prefix.'-'.Str::slug($subCategoryMaster->title);
                                $subCategoryMaster->status             = 1;
                                $subCategoryMaster->vat                = $categoryMaster->vat;
                                $subCategoryMaster->save();
                            }
                            else
                            {
                                $subCategoryMaster = CategoryMaster::where('module_type_id', $categoryMaster->module_type_id)->where('category_master_id', $categoryMaster->id)->where('title', $subvalue['subcategory_title'])->first();
                            }
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
                        if(CategoryDetail::where('category_master_id', $subCategoryMaster->category_master_id)->where('language_id', $language->id)->where('is_parent', 0)->count()<1)
                        {
                            $categoryDetail = new CategoryDetail;
                        }
                        else
                        {
                            $categoryDetail = CategoryDetail::where('category_master_id', $subCategoryMaster->category_master_id)->where('language_id', $language->id)->where('is_parent', 0)->first();
                        }
                        $categoryDetail->category_master_id = $subCategoryMaster->category_master_id;
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

    public function singleSubCategoryUpdate(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'sub_category_id'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }
        DB::beginTransaction();
        try
        {
            $subCategoryMaster = CategoryMaster::find($request->sub_category_id);
            CategoryDetail::where('slug',$subCategoryMaster->slug)->delete();
            foreach ($request->titles as $key => $title) {
                $language = Language::where('value',$key)->first();

                $categoryDetail = new CategoryDetail;
                $categoryDetail->category_master_id = $subCategoryMaster->category_master_id;
                $categoryDetail->language_id        = $language->id;
                $categoryDetail->is_parent          = 0;
                $categoryDetail->title              = $title;
                $categoryDetail->slug               = $subCategoryMaster->slug;
                $categoryDetail->status             = 1;
                $categoryDetail->save();
            }
            DB::commit();
            return response()->json(prepareResult(false, $subCategoryMaster, getLangByLabelGroups('messages','message_category_master_updated')), config('http_response.created'));
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
        if($categoryMaster->category_master_id == null)
        {
            if($categoryMaster->subcategories->count() > 0)
            {
                return response(prepareResult(true, ['can not be deleted. subcategory exists. delete subcategories.'], getLangByLabelGroups('messages','messages_subcategory_exists')), config('http_response.bad_request'));
            }
            elseif(ProductsServicesBook::where('category_master_id',$categoryMaster->id)->count() > 0 || Contest::where('category_master_id',$categoryMaster->id)->count() > 0 || Job::where('category_master_id',$categoryMaster->id)->count() > 0)
            {
                return response(prepareResult(true, ['can not be deleted. Category in use.'], getLangByLabelGroups('messages','messages_category_in_use')), config('http_response.bad_request'));
            }
            else
            {
                CategoryDetail::where('category_master_id',$categoryMaster->id)->delete();
                $categoryMaster->delete();
            }
        }
        else
        {
            if(ProductsServicesBook::where('sub_category_slug',$categoryMaster->slug)->count() > 0 || Contest::where('sub_category_slug',$categoryMaster->slug)->count() > 0 || Job::where('sub_category_slug',$categoryMaster->slug)->count() > 0)
            {
                return response(prepareResult(true, ['can not be deleted. Category in use.'], getLangByLabelGroups('messages','messages_category_in_use')), config('http_response.bad_request'));
            }
            else
            {
                CategoryDetail::where('slug',$categoryMaster->slug)->delete();
                $categoryMaster->delete();
            }
        }
        
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_category_master_deleted')), config('http_response.success'));
    }

    public function categoriesImport(Request $request)
    {
        $import = Excel::import(new CategoriesImport(),request()->file('file'));

        return response(prepareResult(false, [], getLangByLabelGroups('messages','messages_products_services_book_imported')), config('http_response.success'));
    }

    // public function subCategoriesImport(Request $request)
    // {
    //     $data = ['language_id' => $request->language_id,'module_type_id' => $request->module_type_id];
    //     $import = Excel::import(new CategoriesImport($data),request()->file('file'));

    //     return response(prepareResult(false, [], getLangByLabelGroups('messages','messages_products_services_book_imported')), config('http_response.success'));
    // }


    // public function subCategorydelete($id)
    // {
    //     $subCat = CategoryDetail::find($id);
    //     CategoryMaster::where('category_master_id',$subCat->category_master_id)->where('title',$subCat->title)->delete();
    //     $subCat->delete();
    //     return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_category_master_deleted')), config('http_response.success'));
    // }
}
