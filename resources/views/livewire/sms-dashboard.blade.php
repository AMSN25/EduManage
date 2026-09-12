<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('sms.sms_dashboard') }}</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 rounded-lg text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-300 rounded-lg text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats->get('sent', 0) + $stats->get('failed', 0) + $stats->get('queued', 0) }}</p>
            <p class="text-xs text-gray-500">{{ __('sms.total') }}</p>
        </div>
        <div class="bg-green-50 rounded-lg shadow-sm border border-green-200 p-4 text-center">
            <p class="text-2xl font-bold text-green-700">{{ $stats->get('sent', 0) }}</p>
            <p class="text-xs text-green-600">{{ __('sms.sent') }}</p>
        </div>
        <div class="bg-red-50 rounded-lg shadow-sm border border-red-200 p-4 text-center">
            <p class="text-2xl font-bold text-red-700">{{ $stats->get('failed', 0) }}</p>
            <p class="text-xs text-red-600">{{ __('sms.failed') }}</p>
        </div>
        <div class="bg-blue-50 rounded-lg shadow-sm border border-blue-200 p-4 text-center">
            <p class="text-2xl font-bold text-blue-700">{{ number_format($totalCost, 2) }}</p>
            <p class="text-xs text-blue-600">{{ __('sms.total_cost') }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('sms.status') }}</label>
                <select wire:model="statusFilter" class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
                    <option value="">{{ __('sms.all') }}</option>
                    <option value="sent">{{ __('sms.sent') }}</option>
                    <option value="failed">{{ __('sms.failed') }}</option>
                    <option value="queued">{{ __('sms.queued') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('sms.phone') }}</label>
                <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('sms.search_phone') }}"
                    class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('sms.from') }}</label>
                <input type="date" wire:model="dateFrom" class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('sms.to') }}</label>
                <input type="date" wire:model="dateTo" class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
            </div>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('sms.date') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('sms.phone') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('sms.message') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('sms.status') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('sms.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                    <tr>
                        <td class="px-4 py-3 text-gray-600 text-sm">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                        <td class="px-4 py-3 text-gray-900 font-mono text-sm">{{ $log->recipient_phone }}</td>
                        <td class="px-4 py-3 text-gray-600 text-sm max-w-xs truncate">{{ Str::limit($log->message, 50) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ match($log->status) {
                                    'sent' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'queued' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-800',
                                } }}">
                                {{ __('sms.' . $log->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($log->status === 'failed')
                                <button wire:click="retryFailed({{ $log->id }})"
                                    class="min-h-[44px] px-3 py-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-500 bg-indigo-50 rounded-lg hover:bg-indigo-100">
                                    {{ __('sms.retry') }}
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('sms.no_logs') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div class="px-4 py-3 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    </div>
</div>
