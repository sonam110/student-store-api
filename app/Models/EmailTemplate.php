<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory, Uuid;


    protected $fillable = ['language_id','to','cc','bcc','template_for','from','subject','body','attachment_path','status'];
}
