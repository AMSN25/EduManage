<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSubject extends Model
{
    protected $fillable = [
        'exam_id',
        'class_id',
        'subject_id',
        'full_marks',
        'pass_marks',
        'cq_marks',
        'mcq_marks',
        'practical_marks',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function classModel(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class, 'exam_subject_id');
    }
}
