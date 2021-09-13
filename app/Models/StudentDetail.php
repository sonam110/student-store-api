<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class StudentDetail extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'user_id',
        'enrollment_no',
        'education_level',
        'board_university',
        'institute_name',
        'no_of_years_of_study',
        'student_id_card_img_path',
        'avg_rating',
        'status',
        'cool_company_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
