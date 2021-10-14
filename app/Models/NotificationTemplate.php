<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class NotificationTemplate extends Model
{
    use HasFactory, Uuid;

    protected $fillable = ['language_id','template_for','title','body','attributes','status'];
}
