<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;
use App\Models\ProductsServicesBook;
use App\Models\Contest;
use App\Models\Language;

class SearchController extends Controller
{
	function __construct()
    {
        $this->lang_id = Language::select('id')->first()->id;
        if(!empty(request()->lang_id))
        {
            $this->lang_id = request()->lang_id;
        }
    }

	public function productSearch(Request $request)
	{

		$dataType = 'product';
		$dataOf = '3';
		if(!empty($request->dataType))
		{
			$dataType = $request->dataType;
		}
		if(!empty($request->dataOf))
		{
			if($request->dataOf == 'student')
			{
				$dataOf = '2';
			}
			elseif($request->dataOf == 'company')
			{
				$dataOf = '3';
			}
		}

		$products = ProductsServicesBook::select('products_services_books.*')
			->join('users', function ($join) {
				$join->on('products_services_books.user_id', '=', 'users.id');
			})
			->where('users.user_type_id',$dataOf)
			->where('products_services_books.type',$dataType)
			->where('products_services_books.is_published', 1)
			->where('products_services_books.status', 2)
			->where('products_services_books.quantity',">", 0)
			->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
			->orderBy('products_services_books.created_at','desc')
			->with('coverImage','addressDetail')
			->join('category_masters', function ($join) {
				$join->on('products_services_books.category_master_id', '=', 'category_masters.id');
			});

		if(!empty($request->search))
		{
			$search = $request->search;
			$products->where(function ($query) use ($search) {
			    $query->where('products_services_books.title','like', '%'.$search.'%')
			          ->orWhere('category_masters.title','like', '%'.$search.'%')
			          ->orWhere('products_services_books.tags','like', '%'.$search.'%')
			          ->orWhere('products_services_books.gtin_isbn','like', '%'.$search.'%');
			});
		}

		if(!empty($request->per_page_record))
		{
			$res = $products->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
		}
		else
		{
			$res = $products->get();
		}
		
		return response()->json(prepareResult(false, $res, getLangByLabelGroups('messages','message_products_services_book_list')), config('http_response.success'));
	}


	public function jobSearch(Request $request)
	{
		$jobs = Job::select('sp_jobs.*')
			->join('category_masters', function ($join) {
				$join->on('sp_jobs.category_master_id', '=', 'category_masters.id');
			})
			->join('job_tags', function ($join) {
				$join->on('sp_jobs.id', '=', 'job_tags.job_id');
			})
			->where('sp_jobs.is_published', 1)
			->where('sp_jobs.is_deleted', 0)
			->where('sp_jobs.job_status', 1)
			->orderBy('sp_jobs.created_at','desc');

		if(!empty($request->search))
		{
			$search = $request->search;
			$jobs->where(function ($query) use ($search) {
			    $query->where('jobs.title','like', '%'.$search.'%')
			          ->orWhere('category_masters.title','like', '%'.$search.'%')
			          ->orWhere('job_tags.title', 'LIKE', '%'.$search.'%');
			});
		}

		if(!empty($request->per_page_record))
		{
			$res = $jobs->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
		}
		else
		{
			$res = $jobs->get();
		}
		return response()->json(prepareResult(false, $res, getLangByLabelGroups('messages','message_jobs_list')), config('http_response.success'));
	}

	public function contestSearch(Request $request)
	{
		$lang_id = $this->lang_id;
		if(empty($lang_id))
        {
            $lang_id = Language::select('id')->first()->id;
        }

		$type = 'contest';
		$user_type = '3';
		if(!empty($request->type))
		{
			$type = $request->type;
		}
		if(!empty($request->user_type))
		{
			if($request->user_type == 'student')
			{
				$user_type = '2';
			}
			elseif($request->user_type == 'company')
			{
				$user_type = '3';
			}
		}

		$contests = Contest::select('contests.*')
			->join('users', function ($join) {
				$join->on('contests.user_id', '=', 'users.id');
			})
			->join('category_masters', function ($join) {
				$join->on('contests.category_master_id', '=', 'category_masters.id');
			})
			->where('users.user_type_id',$user_type)
			->where('contests.type',$type)
			->orderBy('contests.created_at','desc')
			->where('contests.is_published', 1)
			->where('contests.is_deleted', 0)
			->where('contests.status', 'verified')
			->with('categoryMaster','subCategory')
			->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }]);


		if(!empty($request->search))
		{
			$search = $request->search;
			$contests->where(function ($query) use ($search) {
			    $query->where('contests.title','like', '%'.$search.'%')
			          ->orWhere('category_masters.title','like', '%'.$search.'%')
			          ->orWhere('contests.tags','like', '%'.$search.'%');
			});
		}

		if(!empty($request->per_page_record))
		{
			$res = $contests->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
		}
		else
		{
			$res = $contests->get();
		}
		return response()->json(prepareResult(false, $res, getLangByLabelGroups('messages','message_jobs_list')), config('http_response.success'));
	}

	public function commonSearch(Request $request)
	{
		$lang_id = $this->lang_id;
		$search = $request->search;
		if(empty($lang_id))
        {
            $lang_id = Language::select('id')->first()->id;
        }

		$data = [];

		$data['products'] = ProductsServicesBook::select('id','title','slug','type','category_master_id','sub_category_slug')
		->orderBy('created_at','desc')
		->with('categoryMaster','subCategory')
		->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '1');
        }])
        ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '0');
        }])
		->where('title','like', '%'.$request->search.'%')
		->where('is_published', '1')
		->where('status', '2')
		->where('quantity','>' ,'0')
		->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
		->limit(10)
		->get();

		$data['jobs'] = Job::select('id','title','slug','category_master_id','sub_category_slug')
		->orderBy('created_at','desc')
		->with('categoryMaster','subCategory')
		->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '1');
        }])
        ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '0');
        }])
		->where('title','like', '%'.$request->search.'%')
		->where('is_published', '1')
		->where('job_status', '1')
		->limit(10)
		->get();


		$data['contests'] = Contest::select('id','title','slug','type','category_master_id','sub_category_slug')
		->orderBy('created_at','desc')
		->with('categoryMaster','subCategory')
		->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '1');
        }])
        ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
            $q->select('id','category_master_id','title','slug')
                ->where('language_id', $lang_id)
                ->where('is_parent', '0');
        }])
		->where(function($q) use ($search) {
        	$q->where('title','like', '%'.$search.'%')
        	->orWhere('tags','like', '%'.$search.'%');
        })
		->where('is_published', '1')
		->where('status', 'verified')
		->limit(10)
		->get();

		if(!empty($request->type))
		{
			$data['products'] = ProductsServicesBook::select('id','title','slug','type','category_master_id','sub_category_slug')
			->orderBy('created_at','desc')
			->with('categoryMaster','subCategory')
			->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
			->where('title','like', '%'.$request->search.'%')
			->where('type',$request->type)
			->where('is_published', '1')
			->where('status', '2')
			->where('quantity','>' ,'0')
			->whereRaw("(CASE WHEN products_services_books.is_used_item = 1 THEN products_services_books.is_sold = 0 ELSE products_services_books.is_used_item=0 END)")
			->limit(10)
			->get();


			if($request->type == 'job')
			{
				$data['jobs'] = Job::select('id','title','slug','category_master_id','sub_category_slug')
				->orderBy('created_at','desc')
				->with('categoryMaster','subCategory')
				->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
	                $q->select('id','category_master_id','title','slug')
	                    ->where('language_id', $lang_id)
	                    ->where('is_parent', '1');
	            }])
	            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
	                $q->select('id','category_master_id','title','slug')
	                    ->where('language_id', $lang_id)
	                    ->where('is_parent', '0');
	            }])
				->where('title','like', '%'.$request->search.'%')
				->where('is_published', '1')
				->where('job_status', '1')
				->limit(10)
				->get();
			}
			else
			{
				$data['jobs'] = Job::select('id','title','slug','category_master_id','sub_category_slug')
				->orderBy('created_at','desc')
				->with('categoryMaster','subCategory')
				->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
	                $q->select('id','category_master_id','title','slug')
	                    ->where('language_id', $lang_id)
	                    ->where('is_parent', '1');
	            }])
	            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
	                $q->select('id','category_master_id','title','slug')
	                    ->where('language_id', $lang_id)
	                    ->where('is_parent', '0');
	            }])
				->where('title','like', '%'.$request->search.'%')
				->where('id',null)
				->where('is_published', '1')
				->where('job_status', '1')
				->limit(10)
				->get();
			}

			


			$data['contests'] = Contest::select('id','title','slug','type','category_master_id','sub_category_slug')
			->orderBy('created_at','desc')
			->with('categoryMaster','subCategory')
			->with(['categoryMaster.categoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '1');
            }])
            ->with(['subCategory.SubCategoryDetail' => function($q) use ($lang_id) {
                $q->select('id','category_master_id','title','slug')
                    ->where('language_id', $lang_id)
                    ->where('is_parent', '0');
            }])
            ->where(function($q) use ($search) {
	        	$q->where('title','like', '%'.$search.'%')
	        	->orWhere('tags','like', '%'.$search.'%');
	        })
			->where('type',$request->type)
			->where('is_published', '1')
			->where('status', 'verified')
			->limit(10)
			->get();
		}

		return response()->json(prepareResult(false, $data, getLangByLabelGroups('messages','message_jobs_list')), config('http_response.success'));
	}
}