<?php

namespace App\Policies;

use App\Models\Institute;
use App\Models\Subscription;
use App\Models\User;

class PlanPolicy
{
    /**
     * Check if the institute can add more students (hasn't exceeded plan limits).
     */
    public function canAddStudent(User $user, Institute $institute): bool
    {
        if ($user->institute_id !== $institute->id) {
            return false;
        }

        $subscription = $institute->currentSubscription();
        if (!$subscription || !$subscription->plan) {
            return false;
        }

        $currentCount = $institute->students()->count();
        return $currentCount < $subscription->plan->max_students;
    }

    /**
     * Check if the institute can access a specific feature.
     */
    public function canAccessFeature(User $user, Institute $institute, string $feature): bool
    {
        if ($user->institute_id !== $institute->id) {
            return false;
        }

        $subscription = $institute->currentSubscription();
        if (!$subscription || !$subscription->plan) {
            return false;
        }

        // Active and trial subscriptions can access features
        if (!in_array($subscription->status, ['active', 'trial'])) {
            return false;
        }

        return $subscription->plan->hasFeature($feature);
    }

    /**
     * Get the current student count vs plan limit.
     */
    public function studentCountInfo(Institute $institute): array
    {
        $subscription = $institute->currentSubscription();
        $currentCount = $institute->students()->count();
        $limit = $subscription?->plan?->max_students ?? 0;

        return [
            'current' => $currentCount,
            'limit' => $limit,
            'remaining' => max(0, $limit - $currentCount),
            'is_full' => $currentCount >= $limit,
        ];
    }
}
