<?php

namespace App\Http\Livewire;

use App\Models\Institute;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SuperAdminDashboard extends Component
{
    public int $totalInstitutes = 0;
    public int $activeInstitutes = 0;
    public int $trialInstitutes = 0;
    public int $expiredInstitutes = 0;
    public float $totalRevenue = 0;
    public float $monthlyRevenue = 0;
    public $recentInstitutes = [];
    public $recentPayments = [];
    public $registrationTrend = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    private function loadStats(): void
    {
        // Intentionally bypasses BelongsToInstitute scope:
        // Super admin needs to see ALL institutes across all tenants.
        $this->totalInstitutes = Institute::count();

        $this->activeInstitutes = Subscription::where('status', 'active')->distinct('institute_id')->count('institute_id');
        $this->trialInstitutes = Subscription::where('status', 'trial')->distinct('institute_id')->count('institute_id');
        $this->expiredInstitutes = Institute::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('trial_ends_at')
                  ->orWhere('trial_ends_at', '<', now());
            })
            ->whereDoesntHave('subscriptions', fn ($q) => $q->whereIn('status', ['active', 'trial']))
            ->count();

        $this->totalRevenue = SubscriptionPayment::sum('amount');
        $this->monthlyRevenue = SubscriptionPayment::where('paid_at', '>=', now()->startOfMonth())->sum('amount');

        // Intentionally bypasses BelongsToInstitute scope:
        // Super admin needs to list all institutes for management.
        $this->recentInstitutes = Institute::with(['subscriptions' => function ($q) {
            $q->whereIn('status', ['active', 'trial'])->latest('ends_at');
        }, 'subscriptions.plan'])
            ->latest()
            ->take(10)
            ->get();

        $this->recentPayments = SubscriptionPayment::with('institute', 'recordedBy')
            ->latest('paid_at')
            ->take(10)
            ->get();

        // Registration trend: institutes created per month (last 6 months)
        $this->registrationTrend = Institute::select(
            DB::raw('strftime("%Y-%m", created_at) as month'),
            DB::raw('count(*) as count')
        )
        ->where('created_at', '>=', now()->subMonths(6))
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->toArray();
    }

    public function render()
    {
        return view('livewire.super-admin-dashboard');
    }
}
