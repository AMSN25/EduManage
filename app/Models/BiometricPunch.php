<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricPunch extends Model
{
    protected $fillable = [
        'biometric_device_id',
        'institute_id',
        'device_user_id',
        'punch_time',
        'punch_state',
        'resolution_status',
        'resolved_at',
    ];

    protected $casts = [
        'punch_time' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class);
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function resolve(string $status = 'resolved'): void
    {
        $this->update([
            'resolution_status' => $status,
            'resolved_at' => now(),
        ]);
    }
}
