<?php

namespace App\Exports;

use App\Models\Language;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Auth;

class LanguagesExport implements FromCollection, WithHeadings
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
    		'id',
    		'title',
    		'value',
            'status',
    		'Created at'
    	];
    }

    public function collection()
    {
    	$languages = Language::orderBy('created_at','desc');
    	if(!empty($this->requestData['ids']))
    	{
    		$languages = $languages->whereIn('id',$this->requestData['ids']);
    	}
    	$languages = $languages->get();


    	return $languages->map(function ($data, $key) {
    		return [
    			'SNO'             				=> $key+1,
    			'id'                            => $data->id,
    			'title'							=> trim($data->title),
    			'value'							=> trim($data->value),
    			'status'                        => ($data->status==1) ? 'active' : 'inactive',
              	'Created at'      				=> $data->created_at,
    		];
    	});
    }
}
