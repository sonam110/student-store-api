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
        $languages = $this->data['languages'];

        // $languages = ['english','swedish','hindi'];

        if(!empty($this->data['category_master_id']))
        {
            $is_parent = 0;
            $category_master_id = $this->data['category_master_id'];
        }
        else
        {
            $is_parent = 1;
            $category_master_id = null;
        }

        $slug_prefix = (string) \Uuid::generate(4);

        $categoryMaster = new CategoryMaster;
        $categoryMaster->module_type_id     = $this->data['module_type_id'];
        $categoryMaster->category_master_id = $category_master_id;
        $categoryMaster->title              = $row['category_in_english'];
        $categoryMaster->slug               = $slug_prefix.'-'.Str::slug($row['category_in_english']);
        $categoryMaster->status             = 1;
        $categoryMaster->save();
        if($categoryMaster)
        {
            if(empty($this->data['category_master_id']))
            {
                $category_master_id = $categoryMaster->id;
            }

            foreach($languages as $key => $value)
            {
                if(Language::where('title',$value)->count() > 0)
                {
                    $language = Language::where('title',$value)->first();
                }
                else
                {
                    $language = new Language;
                    $language->title                = $value;
                    $language->value                = $value;
                    $language->status               = 1;
                    $language->save();
                }

                

                $categoryDetail = new CategoryDetail;
                $categoryDetail->category_master_id = $category_master_id;
                $categoryDetail->language_id        = $language->id;
                $categoryDetail->is_parent          = $is_parent;
                $categoryDetail->title              = $row['category_in_'.$value];
                $categoryDetail->slug               = $categoryMaster->slug;
                $categoryDetail->status             = 1;
                $categoryDetail->save();
            }
        }
        return;
    }
}
