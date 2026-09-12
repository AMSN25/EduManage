<div>
    <div class="mb-6">
        <a href="{{ route('super-admin.institutes') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Institutes</a>
    </div>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ $institute->name }}</h1>
        <p class="mt-1 text-sm text-gray-600">{{ $institute->slug }} &middot; {{ $institute->email }}</p>
    </div>

    {{-- Institute Info --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Subscription</h3>
            @php $sub = $institute->subscriptions->first(); @endphp
            @if($sub)
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Plan</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $sub->plan->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Status</span>
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'trial' => 'bg-yellow-100 text-yellow-800',
                                'past_due' => 'bg-orange-100 text-orange-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'expired' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$sub->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($sub->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Ends At</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $sub->ends_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Auto Renew</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $sub->auto_renew ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-500">No active subscription</p>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Counts</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Users</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $institute->users_count ?? $institute->users()->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Students</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $institute->students_count ?? $institute->students()->count() }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Details</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Phone</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $institute->phone ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Address</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $institute->address ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Created</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $institute->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('super-admin.institute.payments', $institute) }}"
           class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
            Payment History
        </a>
    </div>
</div>
