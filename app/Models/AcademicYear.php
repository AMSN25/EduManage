<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'name',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (AcademicYear $year) {
            if ($year->is_current) {
                static::query()
                    ->where('institute_id', $year->institute_id)
                    ->where('id', '!=', $year->id)
                    ->update(['is_current' => false]);
            }
        });
    }

    public function classes(): HasMany
    {
        return $this->hasMany(ClassModel::class, 'academic_year_id');
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }
}
