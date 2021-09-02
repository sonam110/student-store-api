<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\FavouriteJob;
use Auth;

class JobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if($fav = FavouriteJob::where('sa_id',Auth::id())->where('job_id',$this->id)->first())
        {
            $favouriteJob = true;
            $favouriteId = $fav->id;
        }
        else
        {
            $favouriteJob = false;
            $favouriteId = null;
        }

        // return parent::toArray($request);
        return [
            "id"                    => $this->id,
            "user_id"               => $this->user_id,
            "language_id"           => $this->language_id,
            "address_detail_id"     => $this->address_detail_id,
            "title"                 => $this->title,
            "slug"                  => $this->slug,
            "meta_description"      => $this->meta_description,
            "short_summary"         => $this->short_summary,
            "job_type"              => $this->job_type,
            "job_nature"            => $this->job_nature,
            "job_hours"             =>$this->job_hours,
            "job_environment"       => $this->job_environment,
            "years_of_experience"   => $this->years_of_experience,
            "known_languages"       => $this->known_languages,
            "description"           => $this->description,
            "nice_to_have_skills"   =>$this->nice_to_have_skills,
            "job_start_date"        => $this->job_start_date,
            "application_start_date"=> $this->application_start_date,
            "application_end_date"  => $this->application_end_date,
            "job_status"            => $this->job_status,
            "is_deleted"            => $this->is_deleted,
            "is_published"          => $this->is_published,
            "published_at"          => $this->published_at,
            "is_promoted"           => $this->is_promoted,
            "promotion_start_date"  => $this->promotion_start_date,
            "promotion_end_date"    => $this->promotion_end_date,
            "view_count"            => $this->view_count,
            "created_at"            => $this->created_at,
            "updated_at"            => $this->updated_at,
            "deleted_at"            => $this->deleted_at,
            "user"                  => new UserResource($this->user),
            "address_detail"        => $this->addressDetail,
            "job_tags"              => $this->jobTags,
            'favourite_job'         => $favouriteJob,
            'favourite_id'          => $favouriteId,
            "category_master"       => $this->categoryMaster,
            "sub_category"          => $this->subCategory,
        ];
    }
}
