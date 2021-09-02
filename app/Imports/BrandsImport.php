<?php

namespace App\Imports;

use App\Models\Label;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use DB;
use Hash;
use Auth;
use App\Models\Brand;
use Str;
use App\Models\Language;

class BrandsImport implements ToModel,WithHeadingRow
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

        $brand = new Brand;
        $brand->user_id            	= $this->data['user_id'];
        $brand->category_master_id 	= $this->data['category_master_id'];
        $brand->name              	= $row['name'];
        $brand->status             	= 1;
        $brand->save();
        return;
    }
}
