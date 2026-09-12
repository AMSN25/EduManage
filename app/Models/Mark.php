<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mark extends Model
{
    protected $fillable = [
        'exam_subject_id',
        'student_id',
        'obtained_marks',
        'cq_obtained',
        'mcq_obtained',
        'practical_obtained',
        'is_absent',
        'entered_by',
        'status',
    ];

    protected $casts = [
        'obtained_marks' => 'decimal:2',
        'cq_obtained' => 'decimal:2',
        'mcq_obtained' => 'decimal:2',
        'practical_obtained' => 'decimal:2',
        'is_absent' => 'boolean',
    ];

    public function examSubject(): BelongsTo
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
