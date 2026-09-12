<?php

namespace App\Http\Livewire;

use App\Models\Institute;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SuperAdminRecordPayment extends Component
{
    public ?int $instituteId = null;
    public string $amount = '';
    public string $paymentMethod = 'manual';
    public string $transactionRef = '';
    public string $notes = '';
    public string $paidAt = '';
    public $institute = null;

    protected $listeners = ['recordPayment' => 'openModal'];

    protected $rules = [
        'instituteId' => 'required|exists:institutes,id',
        'amount' => 'required|numeric|min:0.01|max:999999.99',
        'paymentMethod' => 'required|in:manual,bank_transfer,bkash,nagad,rocket',
        'transactionRef' => 'nullable|string|max:100',
        'notes' => 'nullable|string|max:500',
        'paidAt' => 'required|date',
    ];

    public function openModal(int $instituteId): void
    {
        $this->instituteId = $instituteId;
        // Intentionally bypasses BelongsToInstitute scope:
        // Super admin needs to load any institute for payment recording.
        $this->institute = Institute::with(['subscriptions' => function ($q) {
            $q->whereIn('status', ['active', 'trial'])->latest('ends_at');
        }, 'subscriptions.plan'])->findOrFail($instituteId);
        $this->amount = '';
        $this->paymentMethod = 'manual';
        $this->transactionRef = '';
        $this->notes = '';
        $this->paidAt = now()->format('Y-m-d');
    }

    public function recordPayment(): void
    {
        $this->validate();

        $institute = Institute::findOrFail($this->instituteId);
        $subscription = $institute->subscriptions()
            ->whereIn('status', ['active', 'trial', 'past_due'])
            ->latest('ends_at')
            ->first();

        if (!$subscription) {
            session()->flash('error', 'No active subscription found. Create one first.');
            return;
        }

        DB::transaction(function () use ($institute, $subscription) {
            // Record the payment
            SubscriptionPayment::create([
                'subscription_id' => $subscription->id,
                'institute_id' => $institute->id,
                'recorded_by' => Auth::id(),
                'amount' => (float) $this->amount,
                'payment_method' => $this->paymentMethod,
                'transaction_ref' => $this->transactionRef ?: null,
                'notes' => $this->notes ?: null,
                'paid_at' => $this->paidAt,
            ]);

            // Extend subscription based on payment amount and plan
            $plan = $subscription->plan;
            if ($plan && $plan->price_monthly > 0) {
                // Calculate months covered by this payment
                $monthsCovered = floor((float) $this->amount / (float) $plan->price_monthly);
                if ($monthsCovered < 1) $monthsCovered = 1;

                $currentEnd = max($subscription->ends_at ?? now(), now());
                $newEnd = $currentEnd->addMonths((int) $monthsCovered);

                $subscription->update([
                    'status' => 'active',
                    'ends_at' => $newEnd,
                    'auto_renew' => true,
                ]);
            } else {
                // For free/trial plans, just extend by 30 days per payment
                $currentEnd = max($subscription->ends_at ?? now(), now());
                $subscription->update([
                    'status' => 'active',
                    'ends_at' => $currentEnd->addDays(30),
                ]);
            }

            // Log to audit
            // Intentionally bypasses BelongsToInstitute scope:
            // Super admin payment recording spans all institutes.
            AuditLog::log(
                $institute->id,
                Auth::id(),
                'super_admin.payment_recorded',
                $subscription,
                [
                    'amount' => (float) $this->amount,
                    'payment_method' => $this->paymentMethod,
                    'transaction_ref' => $this->transactionRef,
                    'new_ends_at' => $subscription->fresh()->ends_at->toISOString(),
                ]
            );
        });

        session()->flash('success', 'Payment recorded and subscription extended.');
        $this->resetForm();
        $this->dispatch('instituteUpdated');
    }

    private function resetForm(): void
    {
        $this->instituteId = null;
        $this->institute = null;
        $this->amount = '';
        $this->paymentMethod = 'manual';
        $this->transactionRef = '';
        $this->notes = '';
        $this->paidAt = '';
    }

    public function render()
    {
        return view('livewire.super-admin-record-payment');
    }
}
