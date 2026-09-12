<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatAssignment extends Model
{
    protected $fillable = [
        'exam_room_id',
        'student_id',
        'seat_no',
    ];

    public function examRoom(): BelongsTo
    {
        return $this->belongsTo(ExamRoom::class, 'exam_room_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
