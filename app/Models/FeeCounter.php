<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeCounter extends Model
{
    protected $fillable = [
        'institute_id',
        'year',
        'counter',
    ];
}
