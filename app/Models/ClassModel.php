<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassModel extends Model
{
    use BelongsToInstitute;

    protected $table = 'classes';

    protected $fillable = [
        'institute_id',
        'academic_year_id',
        'name',
        'numeric_order',
    ];

    protected $casts = [
        'numeric_order' => 'integer',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id')
            ->withPivot('group_id')
            ->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
