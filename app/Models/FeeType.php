<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeType extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'name',
        'is_recurring',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
    ];

    public function structures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }
}
