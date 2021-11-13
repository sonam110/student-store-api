<?php

namespace App\Exports;

use App\Models\CategoryMaster;
use App\Models\CategoryDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Models\Language;
use Auth;

class SampleCategoriesExport implements FromCollection, WithHeadings
{
	use Exportable;


	public function __construct()
	{
	}
    /**
    * @return \Illuminate\Support\Collection
    */

    public function headings(): array {
        $data = ['SNO','id_do_not_change','module','parent_category','vat'];
        foreach(Language::all() as $language)
        {
            $data[] = 'title_in_'.$language->title;
        }
    	return $data;
    }

    public function collection()
    {
    	$categories = CategoryMaster::select('category_masters.category_master_id','category_details.id as cat_details_id','category_masters.title as cat_master_title','category_masters.vat','category_masters.slug','category_details.title','category_details.language_id','category_masters.module_type_id','category_details.is_parent')
        ->join('category_details', function ($join) {
            $join->on('category_masters.slug', '=', 'category_details.slug');
        })
        ->where('category_details.language_id',Language::first()->id)
        ->orderBy('category_details.category_master_id','asc')
        ->orderBy('category_details.is_parent','desc')
        ->orderBy('category_details.title','desc');
    	$categories = $categories->get();
    	return $categories->map(function ($data, $key) {
            $data1 = [
                'SNO'                           => $key+1,
                'id_do_not_change'              => $data->cat_details_id,
                'module'                        => $data->moduleType->title,
                'parent_category'               => trim($data->categoryMaster ? $data->categoryMaster->title : $data->cat_master_title),
                'vat'                           => $data->vat
            ];

            foreach(Language::all() as $language)
            {
                $cat = CategoryDetail::where('language_id',$language->id)->where('slug',$data->slug)->first();
                $data1['title_in_'.$language->title]  = trim($cat ? $cat->title : "");
            }
    		return $data1;
    	});
    }
}
