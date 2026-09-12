<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Institute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'logo',
        'is_active',
        'trial_ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
    ];

    public function settings(): HasOne
    {
        return $this->hasOne(InstituteSetting::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function currentSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trial'])
            ->latest('ends_at')
            ->first();
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isTrialExpired(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }
}
