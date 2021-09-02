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
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if(Language::where('title',$this->data['language_title'])->count() > 0)
        {
            $language = Language::where('title',$this->data['language_title'])->first();
        }
        else
        {
            $language = new Language;
            $language->title                = $this->data['language_title'];
            $language->value                = $this->data['language_value'];
            $language->status               = 1;
            $language->save();
        }

        if(!empty($row['parent_category_title']))
        {
            $is_parent = 0;
            $category_master_id = CategoryMaster::where('title',$row['parent_category_title'])->first()->id;
        }
        else
        {
            $is_parent = 1;
            $category_master_id = null;
        }

        $categoryMaster = new CategoryMaster;
        $categoryMaster->module_type_id     = $this->data['module_type_id'];
        $categoryMaster->category_master_id = $category_master_id;
        $categoryMaster->title              = $row['title'];
        $categoryMaster->slug               = Str::slug($row['title']);
        $categoryMaster->status             = 1;
        $categoryMaster->save();
        if($categoryMaster)
        {
            if(empty($row['parent_category_title']))
            {
                $is_parent = 1;
                $category_master_id = $categoryMaster->id;
            }
            $categoryDetail = new CategoryDetail;
            $categoryDetail->category_master_id = $category_master_id;
            $categoryDetail->language_id        = $language->id;
            $categoryDetail->is_parent          = $is_parent;
            $categoryDetail->title              = $row['title'];
            $categoryDetail->slug               = Str::slug($row['title']);
            $categoryDetail->description        = Str::slug($row['description']);
            $categoryDetail->status             = 1;
            $categoryDetail->save();
        }
        
        return;
    }
}
