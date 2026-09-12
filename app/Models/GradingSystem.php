<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradingSystem extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'name',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function ranges(): HasMany
    {
        return $this->hasMany(GradeRange::class, 'grading_system_id');
    }

    /**
     * Get the default grading system for the current institute.
     */
    public static function getDefault(): ?self
    {
        return static::where('institute_id', auth()->user()->institute_id)
            ->where('is_default', true)
            ->first();
    }
}
