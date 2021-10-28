<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CoolCompanyFreelancer;

class CoolCompanyAssignment extends Model
{
    use HasFactory, Uuid;

    protected $fillable = ['auto_id', 'user_id', 'cool_company_freelancer_id', 'paymentAccountTypeId', 'bankName', 'bankAccountNo', 'bankIdentifierCode', 'response','is_start_assignment','start_assignment_date','start_assignment_response','is_complete_assignment','complete_assignment_date','complete_assignment_response'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function coolCompanyFreelancer()
    {
        return $this->belongsTo(CoolCompanyFreelancer::class,'cool_company_freelancer_id','id');
    }
}
