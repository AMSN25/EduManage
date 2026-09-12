<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone', 'institute_id', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function teacherSubjects(): HasMany
    {
        return $this->hasMany(TeacherSubject::class, 'teacher_id');
    }

    /**
     * Get unique class IDs this teacher is assigned to.
     */
    public function assignedClassIds(): \Illuminate\Support\Collection
    {
        return $this->teacherSubjects()->pluck('class_id')->unique();
    }

    /**
     * Get unique section IDs this teacher is assigned to for a given class.
     */
    public function assignedSectionIds(int $classId): \Illuminate\Support\Collection
    {
        return $this->teacherSubjects()
            ->where('class_id', $classId)
            ->pluck('section_id')
            ->unique();
    }

    /**
     * Check if teacher is assigned to a specific class/section.
     */
    public function isAssignedTo(int $classId, int $sectionId): bool
    {
        return $this->teacherSubjects()
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->exists();
    }
}
