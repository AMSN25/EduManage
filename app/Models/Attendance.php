<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'class_id',
        'section_id',
        'date',
        'taken_by',
        'academic_year_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function classModel(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function takenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Find attendance for a given class/section/date combination.
     */
    public static function findForSession(int $classId, int $sectionId, string $date, int $instituteId): ?self
    {
        return static::withoutGlobalScopes()
            ->where('institute_id', $instituteId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereDate('date', $date)
            ->with('records')
            ->first();
    }

    /**
     * Scope: get absent students on a given date (for SMS module in Phase 10).
     */
    public static function absentOn(string $date, ?int $instituteId = null): \Illuminate\Database\Eloquent\Builder
    {
        $query = static::where('date', $date)
            ->whereHas('records', fn ($q) => $q->where('status', 'absent'))
            ->with('records.student');

        if ($instituteId) {
            $query->where('institute_id', $instituteId);
        }

        return $query;
    }
}
