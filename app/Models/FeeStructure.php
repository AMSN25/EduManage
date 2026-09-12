<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'class_id',
        'fee_type_id',
        'academic_year_id',
        'amount',
        'due_day',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_day' => 'integer',
    ];

    public function classModel(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
