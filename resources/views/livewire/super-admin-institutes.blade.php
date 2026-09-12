<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Institutes</h1>
            <p class="mt-1 text-sm text-gray-600">Manage institute subscriptions and plans</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-green-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search institutes..."
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <select wire:model.live="statusFilter" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="trial">Trial</option>
                    <option value="past_due">Past Due</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Institutes Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Institute</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ends At</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($institutes as $institute)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $institute->name }}</div>
                                <div class="text-xs text-gray-500">{{ $institute->slug }} &middot; {{ $institute->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $institute->subscriptions->first()?->plan?->name ?? 'No Plan' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $status = $institute->subscriptions->first()?->status ?? 'none';
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
                                {{ $institute->subscriptions->first()?->ends_at?->format('M d, Y') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('super-admin.institute.detail', $institute) }}"
                                       class="text-indigo-600 hover:text-indigo-800 font-medium text-xs">View</a>
                                    <button wire:click="startEdit({{ $institute->id }})"
                                            class="text-gray-600 hover:text-gray-800 font-medium text-xs">Edit Plan</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No institutes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $institutes->links() }}
        </div>
    </div>

    {{-- Edit Plan Modal --}}
    @if($editingInstituteId)
        <div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelEdit"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full mx-auto p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Institute Plan</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subscription Plan</label>
                            <select wire:model="newPlanId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }} — ৳{{ number_format($plan->price_monthly, 0) }}/mo ({{ $plan->max_students }} students)</option>
                                @endforeach
                            </select>
                            @error('newPlanId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select wire:model="newStatus" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="trial">Trial</option>
                                <option value="active">Active</option>
                                <option value="past_due">Past Due</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="expired">Expired</option>
                            </select>
                            @error('newStatus') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Extend Subscription (Days)</label>
                            <input type="number" wire:model="extendDays" min="1" max="3650" placeholder="Optional"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('extendDays') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-gray-500">Extend the subscription end date by this many days</p>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3 justify-end">
                        <button wire:click="cancelEdit" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                        <button wire:click="updateInstitute" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
