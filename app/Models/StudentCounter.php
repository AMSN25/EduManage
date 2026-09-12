<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCounter extends Model
{
    protected $fillable = [
        'institute_id',
        'year',
        'counter',
    ];
}
