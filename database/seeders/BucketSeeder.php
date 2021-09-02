<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BucketGroup;
use App\Models\BucketGroupDetail;
use App\Models\BucketGroupAttribute;
use App\Models\BucketGroupAttributeDetail;
use Str;

class BucketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bucketArr = [
            ['name' => 'Color', 'type' => 'list', 'is_multiple' => true],
            ['name' => 'Size', 'type' => 'list', 'is_multiple' => true],
            ['name' => 'Rating', 'type' => 'list', 'is_multiple' => false],
            ['name' => 'Discount', 'type' => 'list', 'is_multiple' => false],
            ['name' => 'Resolution', 'type' => 'list', 'is_multiple' => false],
            ['name' => 'Operating System', 'type' => 'list', 'is_multiple' => false],
            ['name' => 'No of USB ports', 'type' => 'list', 'is_multiple' => false],
            ['name' => 'Screen Type', 'type' => 'list', 'is_multiple' => false],
            ['name' => 'Dimesions', 'type' => 'text', 'is_multiple' => false],
            ['name' => 'Memory', 'type' => 'list', 'is_multiple' => false],
            ['name' => 'Unit', 'type' => 'list', 'is_multiple' => false],
        ];

    	$attrVal['0'] = [
    		['name' => 'Red'],
    		['name' => 'Yellow'],
    		['name' => 'Green'],
    		['name' => 'White'],
    		['name' => 'Black'],
    		['name' => 'Blue']
    	];

    	$attrVal['1'] = [
    		['name' => 'L'],
    		['name' => 'M'],
    		['name' => 'S'],
    		['name' => 'XL'],
    		['name' => 'XXL']
    	];

    	$attrVal['2'] = [
    		['name' => '1'],
    		['name' => '2'],
    		['name' => '3'],
    		['name' => '4'],
    		['name' => '5']
    	];

    	$attrVal['3'] = [
    		['name' => '10%'],
    		['name' => '20%'],
    		['name' => '30%'],
    		['name' => '40%'],
    		['name' => '50%']
    	];

    	$attrVal['4'] = [
    		['name' => 'Full HD'],
    		['name' => 'Ultra HD'],
    		['name' => 'HD Ready'],
    		['name' => 'Normal']
    	];

    	$attrVal['5'] = [
    		['name' => 'Android'],
    		['name' => 'IOS'],
    		['name' => 'HomeOS'],
    		['name' => 'FireTv OS']
    	];

    	$attrVal['6'] = [
    		['name' => '0'],
    		['name' => '1'],
    		['name' => '2'],
    		['name' => '3']
    	];

    	$attrVal['7'] = [
    		['name' => 'LED'],
    		['name' => 'OLED'],
    		['name' => 'QLED']
    	];

    	$attrVal['8'] = [
    		['name' => 'Width'],
    		['name' => 'Height'],
    		['name' => 'Depth']
    	];

    	$attrVal['9'] = [
    		['name' => 'RAM'],
    		['name' => 'HDD']
    	];

        $attrVal['10'] = [
            ['name' => 'CM'],
            ['name' => 'Grams'],
            ['name' => 'ML'],
            ['name' => 'Inches'],
        ];

    	foreach ($bucketArr as $key => $value) {
    		$bucket = new BucketGroup;
	        $bucket->group_name = $value['name'];
	        $bucket->slug = Str::slug($value['name']);
            $bucket->type = $value['type'];
            if($value['type']=='text')
            {
                $bucket->text_type = 'number';
            }
            $bucket->is_multiple = $value['is_multiple'];
	        $bucket->save();
	        if($bucket)
	        {
	        	$bucketDetail = new BucketGroupDetail;
		        $bucketDetail->bucket_group_id = $bucket->id;
		        $bucketDetail->language_id = 1;
		        $bucketDetail->name = $value['name'];
		        $bucketDetail->save();
		    	foreach ($attrVal[$key] as $newKey => $attr) {
		    		$bucketAttr = new BucketGroupAttribute;
			        $bucketAttr->bucket_group_id = $bucket->id;
			        $bucketAttr->name = $attr['name'];
			        $bucketAttr->save();
			        if($bucketAttr)
			        {
			        	$bucketAttrDetail = new BucketGroupAttributeDetail;
				        $bucketAttrDetail->bucket_group_attribute_id = $bucketAttr->id;
				        $bucketAttrDetail->language_id = 1;
				        $bucketAttrDetail->name = $attr['name'];
				        $bucketAttrDetail->save();
			        }
		    	}  
	        }
    	}  
    }	
}
