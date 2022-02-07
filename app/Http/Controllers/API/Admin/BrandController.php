<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BrandsImport;
use App\Models\Language;
use Auth;

class BrandController extends Controller
{
    function __construct()
    {
        $this->lang_id = Language::select('id')->first()->id;
        if(!empty(request()->lang_id))
        {
            $this->lang_id = request()->lang_id;
        }
    }

	public function index(Request $request)
	{
        try
		{
            $brands = Brand::select('*');
            if(!empty($request->title))
            {
                $brands = $brands->where('name', 'LIKE', '%'.$request->title.'%');
            }
            if($request->status=='verified')
            {
                $brands = $brands->where('status', 1);
            }
            elseif($request->status=='unverified')
            {
                $brands = $brands->where('status', 0);
            }

            if(!empty($request->category_id))
            {
                $brands = $brands->where('category_master_id', $request->category_id);
            }

			if(!empty($request->per_page_record))
			{
				$brands = $brands->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$brands = $brands->get();
			}
			return response(prepareResult(false, $brands, getLangByLabelGroups('messages','message__category_master_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			\Log::error($exception);
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
    		'name'  => 'required'
    	]);

    	if ($validation->fails()) {
    		return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
    	}

    	DB::beginTransaction();
    	try
    	{
    		$brand = new Brand;
    		$brand->category_master_id  = $request->category_master_id;
    		$brand->user_id             = Auth::id();
    		$brand->name                = $request->name;
    		$brand->status              = 1;
    		$brand->save();
    		DB::commit();
    		return response()->json(prepareResult(false, $brand, getLangByLabelGroups('messages','message_brand_created')), config('http_response.created'));
    	}
    	catch (\Throwable $exception)
    	{
    		DB::rollback();
    		\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function show(Brand $brand)
    {
        $lang_id = $this->lang_id;
        if(empty($lang_id))
        {
            $lang_id = Language::select('id')->first()->id;
        }
        $brandInfo = Brand::with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->find($brand->id);
    	return response()->json(prepareResult(false, $brandInfo, getLangByLabelGroups('messages','message_brand_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,Brand $brand)
    {
    	$validation = Validator::make($request->all(), [
    		'name' => 'required'
    	]);

    	if ($validation->fails()) {
    		return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
    	}

    	DB::beginTransaction();
    	try
    	{
    		$brand->category_master_id  = $request->category_master_id;
    		$brand->user_id             = Auth::id();
    		$brand->name                = $request->name;
    		$brand->status              = 1;
    		$brand->save();
    		DB::commit();
    		return response()->json(prepareResult(false, $brand, getLangByLabelGroups('messages','message_brand_updated')), config('http_response.success'));
    	}
    	catch (\Throwable $exception)
    	{
    		DB::rollback();
    		\Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    	}
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Brand $brand
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Brand $brand)
    {
    	$brand->delete();
    	return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_brand_deleted')), config('http_response.success'));
    }

    public function brandsImport(Request $request)
    {
    	$data = ['category_master_id' => $request->category_master_id, 'user_id' => $request->user_id];
    	$import = Excel::import(new BrandsImport($data),request()->file('file'));

    	return response(prepareResult(false, [], getLangByLabelGroups('messages','messages_products_services_book_imported')), config('http_response.success'));
    }


    public function multipleStatusUpdate(Request $request)
    {
        $validation = Validator::make($request->all(), [
                'status'    => 'required',
                'brand_id'    => 'required'
            ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $brands = Brand::whereIn('id',$request->brand_id)->get();
            foreach ($brands as $key => $brand) {
                $brand->status = $request->status;
                $brand->save();
            }

            DB::commit();
            return response()->json(prepareResult(false, $brands, getLangByLabelGroups('messages','messages_brand_status_updated')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_validation')), config('http_response.internal_server_error'));
        }
    }
}
