<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Contest;

class ContestTag extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['contest_id','type','user_id','title'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class,'contest_id','id');
    }
}
