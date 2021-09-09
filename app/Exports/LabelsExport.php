<?php

namespace App\Exports;

use App\Models\Label;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Auth;

class LabelsExport implements FromCollection, WithHeadings
{
	use Exportable;


    public function headings(): array {
    	return [
    		'SNO',
            'Label Group',
            'Label Name',
            'Label Value English',
            // 'Label Value Swidish',
    		// 'Created At'
    	];
    }

    public function collection()
    {
    	$labels = Label::where('language_id',1)->get();

    	// return $labels;

    	return $labels->map(function ($data, $key) {
    		return [
    			'SNO'             				=> $key+1,
    			'Label Group'					=> $data->labelGroup->name,
    			'Label Name'					=> $data->label_name,
    			'Label Value English'			=> $data->label_value,
    			// 'Label Value Swidish'			=> $data->label_value,
              	// 'Created At'      				=> $data->created_at,
    		];
    	});
    }
}
