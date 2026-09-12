<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Super Admin Dashboard</h1>
        <p class="mt-1 text-sm text-gray-600">Platform overview and management</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Institutes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalInstitutes }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeInstitutes }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Trial</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $trialInstitutes }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Expired</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $expiredInstitutes }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">This Month</p>
                    <p class="text-2xl font-bold text-green-600">৳{{ number_format($monthlyRevenue, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">All Time</p>
                    <p class="text-2xl font-bold text-gray-900">৳{{ number_format($totalRevenue, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Registrations</h3>
            @if(count($registrationTrend) > 0)
                <div class="space-y-2">
                    @foreach($registrationTrend as $trend)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ $trend['month'] }}</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $trend['count'] }} institutes</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">No registration data yet.</p>
            @endif
        </div>
    </div>

    {{-- Recent Institutes --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Institutes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Registered</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentInstitutes as $inst)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $inst->name }}</div>
                                <div class="text-xs text-gray-500">{{ $inst->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $inst->subscriptions->first()?->plan?->name ?? 'No Plan' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $status = $inst->subscriptions->first()?->status ?? 'none';
                                    $colors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'trial' => 'bg-yellow-100 text-yellow-800',
                                        'past_due' => 'bg-orange-100 text-orange-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'expired' => 'bg-red-100 text-red-800',
                                        'none' => 'bg-gray-100 text-gray-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors[$status] ?? $colors['none'] }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $inst->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('super-admin.institute.detail', $inst) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No institutes yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Payments --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Payments</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Institute</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentPayments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $payment->institute?->name ?? 'Unknown' }}</td>
                            <td class="px-6 py-4 text-green-600 font-semibold">৳{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $payment->paid_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">No payments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
