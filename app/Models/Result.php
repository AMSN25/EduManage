<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Result extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'student_id',
        'exam_id',
        'total_obtained',
        'total_full',
        'percentage',
        'gpa',
        'grade',
        'position',
        'status',
    ];

    protected $casts = [
        'total_obtained' => 'decimal:2',
        'total_full' => 'decimal:2',
        'percentage' => 'decimal:2',
        'gpa' => 'decimal:2',
        'position' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function breakdowns(): HasMany
    {
        return $this->hasMany(ResultSubjectBreakdown::class, 'result_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
