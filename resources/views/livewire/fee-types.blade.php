<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('fees.fee_types') }}</h1>
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
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ $editingId ? __('fees.edit_fee_type') : __('fees.add_fee_type') }}</h3>

        <div class="flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('fees.name') }}</label>
                <input type="text" wire:model="name" placeholder="{{ __('fees.fee_type_name_placeholder') }}"
                    class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="isRecurring" id="isRecurring" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="isRecurring" class="text-sm text-gray-700">{{ __('fees.recurring') }}</label>
            </div>
            <button wire:click="save" class="px-4 py-2 min-h-[44px] text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                {{ $editingId ? __('fees.update') : __('fees.save') }}
            </button>
            @if($editingId)
                <button wire:click="$set('editingId', null); $set('name', ''); $set('isRecurring', false);"
                    class="px-4 py-2 min-h-[44px] text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    {{ __('fees.cancel') }}
                </button>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('fees.name') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('fees.recurring') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('fees.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($feeTypes as $type)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $type->name }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($type->is_recurring)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('fees.yes') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ __('fees.no') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="edit({{ $type->id }})" class="text-indigo-600 hover:text-indigo-500 text-sm">
                                {{ __('fees.edit') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">{{ __('fees.no_fee_types') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
