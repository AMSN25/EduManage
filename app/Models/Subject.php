<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'name',
        'name_bangla',
        'code',
        'is_optional',
    ];

    protected $casts = [
        'is_optional' => 'boolean',
    ];

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassModel::class, 'class_subject', 'subject_id', 'class_id')
            ->withPivot('group_id')
            ->withTimestamps();
    }
}
