<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\State;
use App\Models\City;


class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'sortname',
        'name',
        'phonecode',
        'status'
    ];


    public function states()
    {
        return $this->hasMany(State::class,'country_id','id');
    }

    public function cities()
    {
        return $this->hasManyThrough(City::class, State::class)->orderBy('name','asc');
    }
}
