<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGuardian extends Model
{
    protected $fillable = [
        'student_id',
        'relation',
        'name',
        'phone',
        'occupation',
        'nid',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
