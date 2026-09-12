<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamRoom extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'exam_id',
        'room_name',
        'capacity',
        'rows',
        'columns',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(SeatAssignment::class, 'exam_room_id');
    }

    public function assignedStudents(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'seat_assignments', 'exam_room_id', 'student_id')
            ->withPivot('seat_no');
    }

    public function assignedCount(): int
    {
        return $this->assignments()->count();
    }

    public function availableSeats(): int
    {
        return $this->capacity - $this->assignedCount();
    }
}
