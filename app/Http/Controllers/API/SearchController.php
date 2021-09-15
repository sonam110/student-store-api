<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;
use App\Models\ProductsServicesBook;
use App\Models\Contest;

class SearchController extends Controller
{
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
		
		if(!empty($request->per_page_record))
		{
			$products = ProductsServicesBook::select('products_services_books.*')
			->join('users', function ($join) {
				$join->on('products_services_books.user_id', '=', 'users.id');
			})
			->where('users.user_type_id',$dataOf)
			->where('products_services_books.type',$dataType)
			->orderBy('products_services_books.created_at','desc')
			->with('coverImage','addressDetail')
			->where('products_services_books.title','like', '%'.$request->search.'%')
			->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
		}
		else
		{
			$products = ProductsServicesBook::select('products_services_books.*')
			->join('users', function ($join) {
				$join->on('products_services_books.user_id', '=', 'users.id');
			})
			->where('users.user_type_id',$dataOf)
			->where('products_services_books.type',$dataType)
			->orderBy('products_services_books.created_at','desc')
			->with('coverImage','addressDetail')
			->where('products_services_books.title','like', '%'.$request->search.'%')
			->get();
		}

		if($products->count() == 0)
		{
			if(!empty($request->per_page_record))
			{
				$products = ProductsServicesBook::select('products_services_books.*')
				->join('users', function ($join) {
					$join->on('products_services_books.user_id', '=', 'users.id');
				})
				->where('users.user_type_id',$dataOf)
				->where('products_services_books.type',$dataType)
				->orderBy('products_services_books.created_at','desc')
				->with('coverImage','addressDetail')
				->join('category_masters', function ($join) {
					$join->on('products_services_books.category_master_id', '=', 'category_masters.id');
				})
				->where('category_masters.title','like', '%'.$request->search.'%')
				->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$products = ProductsServicesBook::select('products_services_books.*')
				->join('users', function ($join) {
					$join->on('products_services_books.user_id', '=', 'users.id');
				})
				->where('users.user_type_id',$dataOf)
				->where('products_services_books.type',$dataType)
				->orderBy('products_services_books.created_at','desc')
				->with('coverImage','addressDetail')
				->join('category_masters', function ($join) {
					$join->on('products_services_books.category_master_id', '=', 'category_masters.id');
				})
				->where('category_masters.title','like', '%'.$request->search.'%')
				->get();
			}
		}

		if($products->count() == 0)
		{
			if(!empty($request->per_page_record))
			{
				$products = ProductsServicesBook::select('products_services_books.*')
				->join('users', function ($join) {
					$join->on('products_services_books.user_id', '=', 'users.id');
				})
				->where('users.user_type_id',$dataOf)
				->where('products_services_books.type',$dataType)
				->orderBy('products_services_books.created_at','desc')
				->with('coverImage','addressDetail')
				->join('category_masters', function ($join) {
					$join->on('products_services_books.sub_category_slug', '=', 'category_masters.slug');
				})
				->where('category_masters.title','like', '%'.$request->search.'%')
				->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$products = ProductsServicesBook::select('products_services_books.*')
				->join('users', function ($join) {
					$join->on('products_services_books.user_id', '=', 'users.id');
				})
				->where('users.user_type_id',$dataOf)
				->where('products_services_books.type',$dataType)
				->orderBy('products_services_books.created_at','desc')
				->with('coverImage','addressDetail')
				->join('category_masters', function ($join) {
					$join->on('products_services_books.sub_category_slug', '=', 'category_masters.slug');
				})
				->where('category_masters.title','like', '%'.$request->search.'%')
				->get();
			}
		}

		if($products->count() == 0)
		{
			if(!empty($request->per_page_record))
			{
				$products = ProductsServicesBook::select('products_services_books.*')
				->join('users', function ($join) {
					$join->on('products_services_books.user_id', '=', 'users.id');
				})
				->where('users.user_type_id',$dataOf)
				->where('products_services_books.type',$dataType)
				->orderBy('products_services_books.created_at','desc')
				->with('coverImage','addressDetail')
				->join('product_tags', function ($join) {
					$join->on('products_services_books.id', '=', 'product_tags.products_services_book_id');
				})
				->where('product_tags.title', 'LIKE', '%'.$request->search.'%')
				->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$products = ProductsServicesBook::select('products_services_books.*')
				->join('users', function ($join) {
					$join->on('products_services_books.user_id', '=', 'users.id');
				})
				->where('users.user_type_id',$dataOf)
				->where('products_services_books.type',$dataType)
				->orderBy('products_services_books.created_at','desc')
				->with('coverImage','addressDetail')
				->join('product_tags', function ($join) {
					$join->on('products_services_books.id', '=', 'product_tags.products_services_book_id');
				})
				->where('product_tags.title', 'LIKE', '%'.$request->search.'%')
				->get();
			}
		}

		if($products->count() == 0)
		{
			if(!empty($request->per_page_record))
			{
				$products = ProductsServicesBook::select('products_services_books.*')
				->join('users', function ($join) {
					$join->on('products_services_books.user_id', '=', 'users.id');
				})
				->where('users.user_type_id',$dataOf)
				->where('products_services_books.type',$dataType)
				->orderBy('products_services_books.created_at','desc')
				->with('coverImage','addressDetail')
				->where('gtin_isbn','like', '%'.$request->search.'%')
				->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$products = ProductsServicesBook::select('products_services_books.*')
				->join('users', function ($join) {
					$join->on('products_services_books.user_id', '=', 'users.id');
				})
				->where('users.user_type_id',$dataOf)
				->where('products_services_books.type',$dataType)
				->orderBy('products_services_books.created_at','desc')
				->with('coverImage','addressDetail')
				->where('gtin_isbn','like', '%'.$request->search.'%')
				->get();
			}
		}
		return response()->json(prepareResult(false, $products, getLangByLabelGroups('messages','message_products_services_book_list')), config('http_response.success'));
	}


	public function jobSearch(Request $request)
	{
		if(!empty($request->per_page_record))
		{
			$jobs = Job::where('title','like', '%'.$request->search.'%')
			->orderBy('sp_jobs.created_at','desc')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
		}
		else
		{
			$jobs = Job::where('title','like', '%'.$request->search.'%')
			->orderBy('sp_jobs.created_at','desc')->get();
		}
			

		if($jobs->count() == 0)
		{
			if(!empty($request->per_page_record))
			{
				$jobs = Job::select('sp_jobs.*')
						->join('category_masters', function ($join) {
							$join->on('sp_jobs.category_master_id', '=', 'category_masters.id');
						})
						->where('category_masters.title','like', '%'.$request->search.'%')
						->orderBy('sp_jobs.created_at','desc')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$jobs = Job::select('sp_jobs.*')
						->join('category_masters', function ($join) {
							$join->on('sp_jobs.category_master_id', '=', 'category_masters.id');
						})
						->where('category_masters.title','like', '%'.$request->search.'%')
						->orderBy('sp_jobs.created_at','desc')->get();
			}
		}

		if($jobs->count() == 0)
		{
			if(!empty($request->per_page_record))
			{
				$jobs = Job::select('sp_jobs.*')
							->join('category_masters', function ($join) {
								$join->on('sp_jobs.sub_category_slug', '=', 'category_masters.slug');
							})
							->where('category_masters.title','like', '%'.$request->search.'%')
							->orderBy('sp_jobs.created_at','desc')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$jobs = Job::select('sp_jobs.*')
							->join('category_masters', function ($join) {
								$join->on('sp_jobs.sub_category_slug', '=', 'category_masters.slug');
							})
							->where('category_masters.title','like', '%'.$request->search.'%')
							->orderBy('sp_jobs.created_at','desc')->get();
			}
		}

		if($jobs->count() == 0)
		{
			if(!empty($request->per_page_record))
			{
				$jobs = Job::select('sp_jobs.*')
							->join('job_tags', function ($join) {
								$join->on('sp_jobs.id', '=', 'job_tags.job_id');
							})
							->where('job_tags.title', 'LIKE', '%'.$request->search.'%')
							->orderBy('sp_jobs.created_at','desc')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$jobs = Job::select('sp_jobs.*')
							->join('job_tags', function ($join) {
								$join->on('sp_jobs.id', '=', 'job_tags.job_id');
							})
							->where('job_tags.title', 'LIKE', '%'.$request->search.'%')
							->orderBy('sp_jobs.created_at','desc')->get();
			}
		}
		return response()->json(prepareResult(false, $jobs, getLangByLabelGroups('messages','message_jobs_list')), config('http_response.success'));
	}

	public function contestSearch(Request $request)
	{
		$dataType = 'contest';
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
		if(!empty($request->per_page_record))
		{
			$contests = Contest::select('contests.*')
			->join('users', function ($join) {
				$join->on('contests.user_id', '=', 'users.id');
			})
			->where('users.user_type_id',$dataOf)
			->where('contests.type',$dataType)
			->orderBy('contests.created_at','desc')
			->with('categoryMaster','subCategory')
			->where('contests.title','like', '%'.$request->search.'%')
			->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
		}
		else
		{
			$contests = Contest::select('contests.*')
			->join('users', function ($join) {
				$join->on('contests.user_id', '=', 'users.id');
			})
			->where('users.user_type_id',$dataOf)
			->where('contests.type',$dataType)
			->orderBy('contests.created_at','desc')
			->with('categoryMaster','subCategory')
			->where('contests.title','like', '%'.$request->search.'%')
			->get();
		}

		if($contests->count() == 0)
		{
			if(!empty($request->per_page_record))
			{
				$contests = Contest::select('contests.*')
				->join('users', function ($join) {
					$join->on('contests.user_id', '=', 'users.id');
				})
				->where('users.user_type_id',$dataOf)
				->where('contests.type',$dataType)
				->orderBy('contests.created_at','desc')
				->with('categoryMaster','subCategory')
				->join('category_masters', function ($join) {
					$join->on('contests.category_master_id', '=', 'category_masters.id');
				})
				->where('category_masters.title','like', '%'.$request->search.'%')
				->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$contests = Contest::select('contests.*')
				->join('users', function ($join) {
					$join->on('contests.user_id', '=', 'users.id');
				})
				->where('users.user_type_id',$dataOf)
				->where('contests.type',$dataType)
				->orderBy('contests.created_at','desc')
				->with('categoryMaster','subCategory')
				->join('category_masters', function ($join) {
					$join->on('contests.category_master_id', '=', 'category_masters.id');
				})
				->where('category_masters.title','like', '%'.$request->search.'%')
				->get();
			}
		}

		if($contests->count() == 0)
		{
			if(!empty($request->per_page_record))
			{
				$contests = Contest::select('contests.*')
				->join('users', function ($join) {
					$join->on('contests.user_id', '=', 'users.id');
				})
				->where('users.user_type_id',$dataOf)
				->where('contests.type',$dataType)
				->orderBy('contests.created_at','desc')
				->with('categoryMaster','subCategory')
				->join('category_masters', function ($join) {
					$join->on('contests.sub_category_slug', '=', 'category_masters.slug');
				})
				->where('category_masters.title','like', '%'.$request->search.'%')
				->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
			}
			else
			{
				$contests = Contest::select('contests.*')
				->join('users', function ($join) {
					$join->on('contests.user_id', '=', 'users.id');
				})
				->where('users.user_type_id',$dataOf)
				->where('contests.type',$dataType)
				->orderBy('contests.created_at','desc')
				->with('categoryMaster','subCategory')
				->join('category_masters', function ($join) {
					$join->on('contests.sub_category_slug', '=', 'category_masters.slug');
				})
				->where('category_masters.title','like', '%'.$request->search.'%')
				->get();
			}
		}
		return response()->json(prepareResult(false, $contests, getLangByLabelGroups('messages','message_jobs_list')), config('http_response.success'));
	}

	public function commonSearch(Request $request)
	{
		$data = [];

		$data['products'] = ProductsServicesBook::select('id','title','slug','type','category_master_id','sub_category_slug')
		->orderBy('created_at','desc')
		->with('categoryMaster:id,title','subCategory:title,slug')
		->where('title','like', '%'.$request->search.'%')
		->limit(10)
		->get();

		$data['jobs'] = Job::select('id','title','slug','category_master_id','sub_category_slug')
		->orderBy('created_at','desc')
		->with('categoryMaster:id,title','subCategory:title,slug')
		->where('title','like', '%'.$request->search.'%')
		->limit(10)
		->get();


		$data['contests'] = Contest::select('id','title','slug','type','category_master_id','sub_category_slug')
		->orderBy('created_at','desc')
		->with('categoryMaster:id,title','subCategory:title,slug')
		->where('title','like', '%'.$request->search.'%')
		->limit(10)
		->get();

		if(!empty($request->type))
		{
			$data['products'] = ProductsServicesBook::select('id','title','slug','type','category_master_id','sub_category_slug')
			->orderBy('created_at','desc')
			->with('categoryMaster:id,title','subCategory:title,slug')
			->where('title','like', '%'.$request->search.'%')
			->where('type',$request->type)
			->limit(10)
			->get();


			if($request->type == 'job')
			{
				$data['jobs'] = Job::select('id','title','slug','category_master_id','sub_category_slug')
				->orderBy('created_at','desc')
				->with('categoryMaster:id,title','subCategory:title,slug')
				->where('title','like', '%'.$request->search.'%')
				->limit(10)
				->get();
			}
			else
			{
				$data['jobs'] = Job::select('id','title','slug','category_master_id','sub_category_slug')
				->orderBy('created_at','desc')
				->with('categoryMaster:id,title','subCategory:title,slug')
				->where('title','like', '%'.$request->search.'%')
				->where('id',null)
				->limit(10)
				->get();
			}

			


			$data['contests'] = Contest::select('id','title','slug','type','category_master_id','sub_category_slug')
			->orderBy('created_at','desc')
			->with('categoryMaster:id,title','subCategory:title,slug')
			->where('title','like', '%'.$request->search.'%')
			->where('type',$request->type)
			->limit(10)
			->get();
		}

		return response()->json(prepareResult(false, $data, getLangByLabelGroups('messages','message_jobs_list')), config('http_response.success'));
	}
}