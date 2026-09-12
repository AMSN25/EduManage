<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('fees.fee_structures') }}</h1>
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

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('fees.add_structure') }}</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('fees.class') }}</label>
                <select wire:model="classId" class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
                    <option value="">{{ __('fees.select_class') }}</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('fees.fee_type') }}</label>
                <select wire:model="feeTypeId" class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
                    <option value="">{{ __('fees.select_fee_type') }}</option>
                    @foreach($feeTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('fees.amount_bdt') }}</label>
                <input type="number" wire:model="amount" step="0.01" min="0"
                    class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('fees.due_day') }}</label>
                <input type="number" wire:model="dueDay" min="1" max="31"
                    placeholder="{{ __('fees.optional') }}"
                    class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
            </div>
            <div class="flex items-end">
                <button wire:click="save" class="px-4 py-2 min-h-[44px] text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    {{ __('fees.save_structure') }}
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('fees.class') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('fees.fee_type') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('fees.amount') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('fees.due_day') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('fees.academic_year') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($structures as $structure)
                    <tr>
                        <td class="px-4 py-3 text-gray-900">{{ $structure->classModel->name ?? '' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $structure->feeType->name ?? '' }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($structure->amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $structure->due_day ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $structure->academicYear->name ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('fees.no_structures') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
