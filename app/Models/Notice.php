<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'title',
        'body',
        'audience',
        'class_id',
        'send_sms',
        'published_at',
    ];

    protected $casts = [
        'send_sms' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function classModel(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }
}
