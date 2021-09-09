<?php

namespace App\Imports;

use App\Models\Label;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use DB;
use Hash;
use Auth;
use App\Models\Language;
use Str;

class LanguagesImport implements ToModel,WithHeadingRow
{
    public function model(array $row)
    {
        $brand = new Language;
        $brand->title            	= $row['title'];
        $brand->value 	            = $row['value'];
        $brand->status             	= 1;
        $brand->save();
        return;
    }
}
