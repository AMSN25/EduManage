<?php

namespace App\Http\Livewire;

use App\Models\Institute;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SuperAdminInstitutes extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public ?int $editingInstituteId = null;
    public ?int $newPlanId = null;
    public string $newStatus = '';
    public ?string $extendDays = null;

    protected $listeners = ['instituteUpdated' => '$refresh'];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function startEdit(int $instituteId): void
    {
        $this->editingInstituteId = $instituteId;
        $institute = Institute::with(['subscriptions' => function ($q) {
            $q->whereIn('status', ['active', 'trial'])->latest('ends_at');
        }])->findOrFail($instituteId);
        $activeSub = $institute->subscriptions->first();
        $this->newPlanId = $activeSub?->subscription_plan_id;
        $this->newStatus = $activeSub?->status ?? 'trial';
        $this->extendDays = null;
    }

    public function cancelEdit(): void
    {
        $this->editingInstituteId = null;
        $this->newPlanId = null;
        $this->newStatus = '';
        $this->extendDays = null;
    }

    public function updateInstitute(): void
    {
        if (!$this->editingInstituteId) return;

        $this->validate([
            'newPlanId' => 'required|exists:subscription_plans,id',
            'newStatus' => 'required|in:trial,active,past_due,cancelled,expired',
            'extendDays' => 'nullable|integer|min:1|max:3650',
        ]);

        $institute = Institute::findOrFail($this->editingInstituteId);
        $subscription = $institute->subscriptions()
            ->whereIn('status', ['active', 'trial', 'past_due'])
            ->latest('ends_at')
            ->first();

        $oldPlanId = $subscription?->subscription_plan_id;
        $oldStatus = $subscription?->status;
        $oldEndsAt = $subscription?->ends_at;

        $plan = SubscriptionPlan::find($this->newPlanId);

        if ($subscription) {
            $subscription->update([
                'subscription_plan_id' => $this->newPlanId,
                'status' => $this->newStatus,
            ]);

            // Extend subscription if requested
            if ($this->extendDays && $this->extendDays > 0) {
                $newEndsAt = max($subscription->ends_at ?? now(), now())->addDays((int) $this->extendDays);
                $subscription->update(['ends_at' => $newEndsAt]);
            }
        } else {
            // Create new subscription
            $startsAt = now();
            $endsAt = $this->newStatus === 'trial'
                ? now()->addDays(30)
                : now()->addMonth();

            Subscription::create([
                'institute_id' => $institute->id,
                'subscription_plan_id' => $this->newPlanId,
                'status' => $this->newStatus,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'auto_renew' => $this->newStatus === 'active',
            ]);
        }

        // Log the change to audit_logs
        // Intentionally bypasses BelongsToInstitute scope:
        // Super admin actions span all institutes — logged under the target institute.
        AuditLog::log(
            $institute->id,
            Auth::id(),
            'super_admin.subscription_changed',
            $institute,
            [
                'old_plan_id' => $oldPlanId,
                'new_plan_id' => $this->newPlanId,
                'old_status' => $oldStatus,
                'new_status' => $this->newStatus,
                'old_ends_at' => $oldEndsAt?->toISOString(),
                'extended_days' => $this->extendDays ? (int) $this->extendDays : null,
            ]
        );

        $this->cancelEdit();
        session()->flash('success', 'Institute subscription updated.');
    }

    public function render()
    {
        // Intentionally bypasses BelongsToInstitute scope:
        // Super admin needs to list and search ALL institutes.
        $query = Institute::with(['subscriptions' => function ($q) {
            $q->whereIn('status', ['active', 'trial'])->latest('ends_at');
        }, 'subscriptions.plan'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('slug', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, function ($q) {
                if ($this->statusFilter === 'expired') {
                    $q->whereDoesntHave('subscriptions', fn ($q) => $q->whereIn('status', ['active', 'trial']));
                } else {
                    $q->whereHas('subscriptions', fn ($q) => $q->where('status', $this->statusFilter));
                }
            });

        $institutes = $query->latest()->paginate(15);
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('name')->get();

        return view('livewire.super-admin-institutes', [
            'institutes' => $institutes,
            'plans' => $plans,
        ]);
    }
}
