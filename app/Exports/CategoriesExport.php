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
    		'module_type_id',
            'category_master_id',
            'title',
            'status',
            'language_id',
            'title',
            'slug',
            'description',
            'is_parent',
            'status',
    		'Created At'
    	];
    }

    public function collection()
    {
    	$categories = CategoryMaster::join('category_details', function ($join) {
                $join->on('category_masters.title', '=', 'category_details.title');
            })
        ->orderBy('category_masters.created_at','desc');
    	if(!empty($requestData->ids))
    	{
    		$categories = $categories->whereIn('category_masters.id',$requestData->ids);
    	}
    	if(!empty($requestData->module_type_id))
    	{
    		$categories = $categories->where('category_details.module_type_id',$requestData->module_type_id);
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
    			'module_type_id'				=> $data->moduleType->title,
    			'category_master_id'			=> $data->categoryMaster ? $data->categoryMaster->title : null,
    			'title'							=> $data->title,
    			'status'						=> $data->status,
    			'language_id'					=> $data->language_id,
    			'title'							=> $data->title,
    			'description'					=> $data->description,
    			'is_parent'						=> $data->is_parent,
              	'Created At'      				=> $data->created_at,
    		];
    	});
    }
}
