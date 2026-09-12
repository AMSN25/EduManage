<?php

namespace App\Jobs;

use App\Models\Institute;
use App\Models\Subscription;
use App\Models\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireTrialSubscriptions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(): void
    {
        // Find all trial subscriptions that have passed their end date
        $expiredTrials = Subscription::where('status', 'trial')
            ->where('ends_at', '<', now())
            ->get();

        foreach ($expiredTrials as $subscription) {
            $subscription->update(['status' => 'expired']);

            // Log the expiry
            AuditLog::log(
                $subscription->institute_id,
                null,
                'system.trial_expired',
                $subscription,
                [
                    'expired_at' => $subscription->ends_at->toISOString(),
                    'plan_name' => $subscription->plan?->name,
                ]
            );

            // Mark institute as inactive if no active subscription remains
            $institute = $subscription->institute;
            if ($institute) {
                $hasActive = $institute->subscriptions()
                    ->whereIn('status', ['active'])
                    ->exists();

                if (!$hasActive) {
                    $institute->update(['is_active' => false]);
                }
            }
        }

        \Log::info("Expired {$expiredTrials->count()} trial subscriptions.");
    }
}
