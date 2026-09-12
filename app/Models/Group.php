<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'name',
    ];

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassModel::class, 'class_subject', 'group_id', 'class_id')
            ->withPivot('subject_id')
            ->withTimestamps();
    }
}
