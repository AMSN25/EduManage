<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultSubjectBreakdown extends Model
{
    protected $fillable = [
        'result_id',
        'subject_id',
        'obtained',
        'full',
        'grade',
        'gpa_point',
    ];

    protected $casts = [
        'obtained' => 'decimal:2',
        'full' => 'decimal:2',
        'gpa_point' => 'decimal:1',
    ];

    public function result(): BelongsTo
    {
        return $this->belongsTo(Result::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
