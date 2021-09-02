<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Job;

class FavouriteJob extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['job_id', 'sp_id', 'sa_id'];

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id', 'id');
    }

    public function spUser()
    {
        return $this->belongsTo(User::class, 'sp_id', 'id');
    }

    public function saUser()
    {
        return $this->belongsTo(User::class, 'sa_id', 'id');
    }

    //dublicate
    public function user()
    {
        return $this->belongsTo(User::class, 'sa_id', 'id');
    }
}
