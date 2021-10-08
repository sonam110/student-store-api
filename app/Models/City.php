<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\State;

class City extends Model
{
    use HasFactory;
    protected $fillable = [
        'country_id',
        'name',
        'status'
    ];

    protected $appends = ["city"];

    public function getCityAttribute() {
         return $this->attributes['name'];
    }

    public function state()
    {
        return $this->belongsTo(State::class,'state_id','id');
    }
}
