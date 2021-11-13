<?php

namespace App\Imports;

use App\Models\Label;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use DB;
use Hash;
use Auth;
use App\Models\CategoryMaster;
use App\Models\CategoryDetail;
use Str;
use App\Models\Language;

class CategoriesImport implements ToModel,WithHeadingRow
{
    public function __construct()
    {

    }

    public function model(array $row)
    {
        $getLangForLoop = Language::orderby('id','ASC')->get();
        foreach($getLangForLoop as $key => $lang)
        {
            if(@$row['title_in_'.strtolower($lang->title)])
            {
                $getDetail = CategoryDetail::find($row['id_do_not_change']);
                if($getDetail)
                {
                    if(!empty($row['title_in_'.strtolower($lang->title)]))
                    {
                        if(CategoryDetail::where('category_master_id', $getDetail->category_master_id)->where('language_id', $lang->id)->where('slug', $getDetail->slug)->count()<1)
                        {
                            $categoryDetail = new CategoryDetail;
                            $categoryDetail->category_master_id = $getDetail->category_master_id;
                            $categoryDetail->language_id        = $lang->id;
                            $categoryDetail->slug               = $getDetail->slug;
                            $categoryDetail->description        = $getDetail->description;
                            $categoryDetail->status             = $getDetail->status;
                        }
                        else
                        {
                            $categoryDetail = CategoryDetail::where('category_master_id', $getDetail->category_master_id)->where('language_id', $lang->id)->where('slug', $getDetail->slug)->first();
                        }
                        
                        $categoryDetail->title = $row['title_in_'.strtolower($lang->title)];
                        $categoryDetail->save();

                        if($getDetail->is_parent==1 && $key==0)
                        {
                            $catMaster = CategoryMaster::where('slug', $getDetail->slug)->first();
                            $catMaster->title = $row['title_in_'.strtolower($lang->title)];
                            $catMaster->vat = (!empty(trim($row['vat']))) ? $row['vat'] : 0;
                            $catMaster->save();
                        }
                    }
                }
            }
        }
        
        
        return;
    }
}
