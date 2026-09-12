<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiometricDevice extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'serial_number',
        'name',
        'model',
        'status',
        'ip_address',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function mappings(): HasMany
    {
        return $this->hasMany(DeviceUserMapping::class);
    }

    public function punches(): HasMany
    {
        return $this->hasMany(BiometricPunch::class);
    }

    public function markSeen(?string $ip = null): void
    {
        $this->update([
            'last_seen_at' => now(),
            'ip_address' => $ip ?? $this->ip_address,
        ]);
    }
}
