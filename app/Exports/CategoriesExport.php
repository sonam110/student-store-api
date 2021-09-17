<?php

namespace App\Exports;

use App\Models\CategoryMaster;
use App\Models\CategoryDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Auth;

class CategoriesExport implements FromCollection, WithHeadings
{
	use Exportable;

	public $requestData;

	public function __construct($requestData)
	{
		$this->requestData = $requestData;
	}
    /**
    * @return \Illuminate\Support\Collection
    */

    public function headings(): array {
    	return [
    		'SNO',
    		'module',
            'parent_category',
            'title',
            // 'slug',
            // 'is_parent',
            // 'language',
    		// 'Created At'
    	];
    }

    public function collection()
    {
    	$categories = CategoryMaster::select('category_masters.category_master_id','category_masters.slug','category_details.title','category_details.language_id','category_masters.module_type_id','category_details.is_parent')->join('category_details', function ($join) {
                $join->on('category_masters.slug', '=', 'category_details.slug');
            })
        ->orderBy('category_details.language_id','asc');
    	if(!empty($requestData->ids))
    	{
    		$categories = $categories->whereIn('category_masters.id',$requestData->ids);
    	}
    	if(!empty($requestData->module_type_id))
    	{
    		$categories = $categories->where('category_masters.module_type_id',$requestData->module_type_id);
    	}
        if(!empty($requestData->language_id))
        {
            $categories = $categories->where('category_details.language_id',$requestData->language_id);
        }
    	$categories = $categories->get();

    	// return $categories;

    	return $categories->map(function ($data, $key) {
    		return [
    			'SNO'             				=> $key+1,
    			'module'				        => $data->moduleType->title,
    			'parent_category'				=> $data->categoryMaster ? $data->categoryMaster->title : null,
    			'title'							=> $data->title,
    			// 'slug'							=> $data->slug,
    			// 'is_parent'						=> $data->is_parent,
    			// 'language_id'					=> $data->language->title,
              	// 'Created At'      				=> $data->created_at,
    		];
    	});
    }
}
