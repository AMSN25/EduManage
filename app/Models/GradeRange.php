<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeRange extends Model
{
    protected $fillable = [
        'grading_system_id',
        'min_percent',
        'max_percent',
        'grade',
        'gpa_point',
    ];

    protected $casts = [
        'min_percent' => 'decimal:2',
        'max_percent' => 'decimal:2',
        'gpa_point' => 'decimal:1',
    ];

    public function gradingSystem(): BelongsTo
    {
        return $this->belongsTo(GradingSystem::class, 'grading_system_id');
    }
}
