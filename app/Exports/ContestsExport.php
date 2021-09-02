<?php

namespace App\Exports;

use App\Models\Contest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Auth;

class ContestsExport implements FromCollection, WithHeadings
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
    		// 'auto_id',
            'id',
            'user',
            // 'address_detail',
            'category_master',
            // 'service_provider_type',
            // 'registration_type',
            // 'sub_category_slug',
            'title',
            'slug',
            'description',
            'type',
            // 'cover_image_path',
            'sponsor_detail',
            'start_date',
            'start_time',
            'end_time',
            'application_start_date',
            'application_end_date',
            'max_participants',
            'no_of_winners',
            'winner_prizes',
            'mode',
            'meeting_link',
            'address',
            'target_country',
            'target_city',
            'education_level',
            'educational_institition',
            'age_restriction',
            'min_age',
            'max_age',
            'others',
   			'condition_for_joining',
			'available_for',
			'condition_description',
			'condition_file_path',
			'jury_members',
			'is_free',
			'subscription_fees',
			'use_cancellation_policy',
			'provide_participation_certificate',
			'is_on_offer',
			// 'discount_type',
			// 'discount_value',
			'discounted_price',
			'is_published',
			'published_at',
			// 'is_deleted',
			// 'required_file_upload',
			// 'file_title',
			'is_reward_point_applicable',
			'reward_points',
			'is_min_participants',
			'min_participants',
			'status',
			// 'reason_id_for_cancellation',
			// 'reason_for_cancellation',
			// 'reason_id_for_rejection',
			// 'reason_for_rejection',
    		'Created at'
    	];
    }

    public function collection()
    {
    	$contests = Contest::orderBy('created_at','desc');
    	if(!empty($this->requestData['ids']))
    	{
    		$contests = $contests->whereIn('id',$this->requestData['ids']);
    	}
    	if(!empty($this->requestData['type']))
    	{
    		$contests = $contests->where('type',$this->requestData['type']);
    	}

        if($this->requestData['auth_applicable'] == true)
        {
            $contests = $contests->where('user_id',Auth::id());
        }

    	$contests = $contests->get();

    	return $contests->map(function ($data, $key) {
    		return [
    			'SNO'             				=> $key+1,
    			// 'auto_id'						=> $data->auto_id,
                'id'							=> $data->id,
                'user'							=> $data->user->first_name.' '.$data->user->last_name,
                // 'address_detail_id'				=> $data->address_detail_id,
                'category_master_id'			=> $data->categoryMaster ? $data->categoryMaster->title : '',
                // 'service_provider_type_id'		=> $data->service_provider_type_id,
                // 'registration_type_id'			=> $data->registration_type_id,
                // 'sub_category_slug'				=> $data->sub_category_slug,
                'title'							=> $data->title,
                'slug'							=> $data->slug,
                'description'					=> $data->description,
                'type'							=> $data->type,
                // 'cover_image_path'				=> $data->cover_image_path,
                'sponsor_detail'				=> $data->sponsor_detail,
                'start_date'					=> $data->start_date,
                'start_time'					=> $data->start_time,
                'end_time'						=> $data->end_time,
                'application_start_date'		=> $data->application_start_date,
                'application_end_date'			=> $data->application_end_date,
                'max_participants'				=> $data->max_participants,
                'no_of_winners'					=> $data->no_of_winners,
                'winner_prizes'					=> $data->winner_prizes,
                'mode'							=> $data->mode,
                'meeting_link'					=> $data->meeting_link,
                'address'						=> $data->address,
                'target_country'				=> $data->target_country,
                'target_city'					=> $data->target_city,
                'education_level'				=> $data->education_level,
                'educational_institition'		=> $data->educational_institition,
                'age_restriction'				=> $data->age_restriction,
                'min_age'						=> $data->min_age,
                'max_age'						=> $data->max_age,
                'others'						=> $data->others,
    			'condition_for_joining'			=> $data->condition_for_joining,
				'available_for'					=> $data->available_for,
				'condition_description'			=> $data->condition_description,
				'condition_file_path'			=> $data->condition_file_path,
				'jury_members'					=> $data->jury_members,
				'is_free'						=> $data->is_free,
				'subscription_fees'				=> $data->subscription_fees,
				'use_cancellation_policy'		=> $data->use_cancellation_policy,
				'provide_participation_certificate'=> $data->provide_participation_certificate,
				'is_on_offer'					=> $data->is_on_offer,
				// 'discount_type'					=> $data->discount_type,
				// 'discount_value'				=> $data->discount_value,
				'discounted_price'				=> $data->discounted_price,
				'is_published'					=> $data->is_published,
				'published_at'					=> $data->published_at,
				// 'is_deleted'					=> $data->is_deleted,
				// 'required_file_upload'			=> $data->required_file_upload,
				// 'file_title'					=> $data->file_title,
				'is_reward_point_applicable'	=> $data->is_reward_point_applicable,
				'reward_points'					=> $data->reward_points,
				'is_min_participants'			=> $data->is_min_participants,
				'min_participants'				=> $data->min_participants,
				'status'						=> $data->status,
				// 'reason_id_for_cancellation'	=> $data->reason_id_for_cancellation,
				// 'reason_for_cancellation'		=> $data->reason_for_cancellation,
				// 'reason_id_for_rejection'		=> $data->reason_id_for_rejection,
				// 'reason_for_rejection'			=> $data->reason_for_rejection,
              	'Created at'      				=> $data->created_at,
    		];
    	});
    }
}
