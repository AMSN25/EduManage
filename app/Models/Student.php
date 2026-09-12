<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use BelongsToInstitute, SoftDeletes;

    protected $fillable = [
        'institute_id',
        'student_id',
        'admission_no',
        'roll',
        'name',
        'name_bangla',
        'gender',
        'dob',
        'blood_group',
        'religion',
        'phone',
        'email',
        'photo_path',
        'class_id',
        'section_id',
        'group_id',
        'academic_year_id',
        'admission_date',
        'status',
        'present_village',
        'present_post_office',
        'present_upazila',
        'present_district',
        'present_division',
        'permanent_village',
        'permanent_post_office',
        'permanent_upazila',
        'permanent_district',
        'permanent_division',
    ];

    protected $casts = [
        'dob' => 'date',
        'admission_date' => 'date',
        'roll' => 'integer',
    ];

    public function classModel(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function guardians(): HasMany
    {
        return $this->hasMany(StudentGuardian::class);
    }

    public function studentFees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForSection($query, $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
