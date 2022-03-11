<?php

namespace App\Exports;

use App\Models\Job;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Auth;
use mervick\aesEverywhere\AES256;

class JobsExport implements FromCollection, WithHeadings
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
            'user',
            // 'language_id',
            // 'address_detail_id',
            // 'category_master_id',
            // 'sub_category_slug',
            'title',
            'slug',
            'meta_description',
            'short_summary',
            'job_type',
            'job_nature',
            'job_hours',
            'job_environment',
            'years_of_experience',
            'known_languages',
            'description',
            'duties_and_responsibilities',
            // 'nice_to_have_skills',
            'job_start_date',
            'application_start_date',
            'application_end_date',
            'job_status',
            'is_deleted',
            'is_published',
            'published_at',
            'is_promoted',
            'promotion_start_date',
            'promotion_end_date',
            'view_count',
    	];
    }

    public function collection()
    {
    	$jobs = Job::orderBy('created_at','desc');
    	if(!empty($this->requestData['ids']))
    	{
    		$jobs = $jobs->whereIn('id',$this->requestData['ids']);
    	}
        if($this->requestData['auth_applicable'] == true)
        {
            $jobs = $jobs->where('user_id',Auth::id());
        }
    	$jobs = $jobs->get();

    	// return $jobs;

    	return $jobs->map(function ($data, $key) {

            //0 for inactive,1 for active,2 for rejected,3 for expired,4 for canceled
            switch ($data->job_status) {
                case '1':
                    $job_status = 'active';
                    break;
                case '2':
                    $job_status = 'rejected';
                    break;
                case '3':
                    $job_status = 'expired';
                    break;
                case '4':
                    $job_status = 'canceled';
                    break;
                
                default:
                    $job_status = 'inactive';
                    break;
            }

    		return [
    			'SNO'             				=> $key+1,
    			'id'                            => $data->id,
                'user'                       => ($data->user) ? AES256::decrypt($data->user->first_name, env('ENCRYPTION_KEY')).' '.AES256::decrypt($data->user->last_name, env('ENCRYPTION_KEY')) : null,
                // 'language_id'                   => $data->language->title,
                // 'address_detail_id'             => $data->address_detail_id,
                // 'category_master_id'            => $data->categoryMaster->title,
                // 'sub_category_slug'             => $data->sub_category_slug,
                'title'                         => $data->title,
                'slug'                          => $data->slug,
                'meta_description'              => $data->meta_description,
                'short_summary'                 => $data->short_summary,
                'job_type'                      => $data->job_type,
                'job_nature'                    => $data->job_nature,
                'job_hours'                     => $data->job_hours,
                'job_environment'               => (!empty($data->job_environment)) ? implode(',',json_decode($data->job_environment)) : null,
                'years_of_experience'           => $data->years_of_experience,
                'known_languages'               => (!empty($data->known_languages)) ? implode(',',json_decode($data->known_languages)) : null,
                'description'                   => $data->description,
                'duties_and_responsibilities'   => $data->duties_and_responsibilities,
                // 'nice_to_have_skills'           => $data->nice_to_have_skills,
                'job_start_date'                => $data->job_start_date,
                'application_start_date'        => $data->application_start_date,
                'application_end_date'          => $data->application_end_date,
                'job_status'                    => $job_status,
                'is_deleted'                    => ($data->is_deleted==1) ? 'yes' : 'no',
                'is_published'                  => ($data->is_published==1) ? 'yes' : 'No',
                'published_at'                  => $data->published_at,
                'is_promoted'                   => ($data->is_promoted==1) ? 'yes' : 'no',
                'promotion_start_date'          => $data->promotion_start_date,
                'promotion_end_date'            => $data->promotion_end_date,
                'view_count'                    => $data->view_count,
              	'Created at'      				=> $data->created_at,
    		];
    	});
    }
}
