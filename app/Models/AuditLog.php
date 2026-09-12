<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'institute_id',
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Log an audit entry.
     */
    public static function log(
        int $instituteId,
        ?int $userId,
        string $action,
        ?Model $subject = null,
        ?array $changes = null
    ): self {
        return static::create([
            'institute_id' => $instituteId,
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'changes' => $changes,
        ]);
    }
}
