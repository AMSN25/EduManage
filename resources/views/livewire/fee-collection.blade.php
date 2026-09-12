<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('fees.fee_collection') }}</h1>
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

    {{-- Search --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('fees.search_student') }}</label>
        <div class="flex gap-3">
            <input type="text" wire:model.debounce.300ms="search" wire:change="searchStudent"
                placeholder="{{ __('fees.search_placeholder') }}"
                class="flex-1 border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    @if($student)
        {{-- Student Info --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-lg font-bold text-indigo-600">{{ substr($student->name, 0, 1) }}</span>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $student->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $student->student_id }} | {{ $student->classModel->name ?? '' }} - {{ $student->section->name ?? '' }}</p>
                </div>
            </div>
        </div>

        {{-- Outstanding Fees --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('fees.outstanding_dues') }}</h3>
                @if(count($outstandingFees) > 0)
                    <button wire:click="selectAll" class="text-sm text-indigo-600 hover:text-indigo-500">
                        {{ __('fees.select_all') }}
                    </button>
                @endif
            </div>

            @if(empty($outstandingFees))
                <p class="text-gray-500 text-sm">{{ __('fees.no_outstanding') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase w-8"></th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('fees.fee_type') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('fees.month') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('fees.due') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('fees.paid') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('fees.remaining') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('fees.pay_amount') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('fees.due_date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($outstandingFees as $fee)
                                @php
                                    $remaining = (float) $fee['amount_due'] - (float) $fee['amount_paid'];
                                    $isSelected = in_array($fee['id'], $selectedFees);
                                @endphp
                                <tr class="{{ $isSelected ? 'bg-indigo-50' : '' }}">
                                    <td class="px-4 py-3">
                                        <input type="checkbox" wire:click="toggleFee({{ $fee['id'] }})"
                                            {{ $isSelected ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $fee['fee_structure']['fee_type']['name'] ?? 'Fee' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $fee['month'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600">{{ number_format($fee['amount_due'], 2) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600">{{ number_format($fee['amount_paid'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-red-600">{{ number_format($remaining, 2) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @if($isSelected)
                                            <input type="number" wire:model="paymentAmounts.{{ $fee['id'] }}"
                                                step="0.01" min="0" max="{{ $remaining }}"
                                                class="w-24 text-right border-gray-300 rounded-lg shadow-sm text-sm">
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 text-sm">{{ \Carbon\Carbon::parse($fee['due_date'])->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Payment Method --}}
                <div class="mt-4 flex items-center gap-4">
                    <label class="text-sm font-medium text-gray-700">{{ __('fees.payment_method') }}:</label>
                    <select wire:model="paymentMethod" class="border-gray-300 rounded-lg shadow-sm text-sm">
                        <option value="cash">{{ __('fees.cash') }}</option>
                        <option value="manual_online">{{ __('fees.manual_online') }}</option>
                    </select>
                </div>

                {{-- Process Button --}}
                @if(count($selectedFees) > 0)
                    <div class="mt-4">
                        <button wire:click="processPayment" wire:loading.attr="disabled"
                            class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="processPayment">
                                {{ __('fees.process_payment') }} ({{ count($selectedFees) }} {{ __('fees.fees') }})
                            </span>
                            <span wire:loading wire:target="processPayment">{{ __('fees.processing') }}...</span>
                        </button>
                    </div>
                @endif
            @endif
        </div>
    @endif

    {{-- Receipt Modal --}}
    @if($showReceipt && $receiptData)
        <div class="fixed inset-0 z-[60] overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showReceipt', false)"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('fees.receipt') }}</h3>

                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <p class="text-sm"><strong>{{ __('fees.receipt_no') }}:</strong> {{ $receiptData['receipt_no'] }}</p>
                        <p class="text-sm"><strong>{{ __('fees.student') }}:</strong> {{ $receiptData['student']->name }}</p>
                        <p class="text-sm"><strong>{{ __('fees.total_paid') }}:</strong> {{ number_format($receiptData['total_paid'], 2) }} BDT</p>
                        <p class="text-sm"><strong>{{ __('fees.collected_by') }}:</strong> {{ $receiptData['collected_by'] }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button wire:click="downloadReceipt"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            {{ __('fees.download_receipt') }}
                        </button>
                        <button wire:click="$set('showReceipt', false)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                            {{ __('fees.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
