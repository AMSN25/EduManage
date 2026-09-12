<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('academic_structure.academic_years') }}</h1>
        <button wire:click="openModal" class="bg-indigo-600 text-white px-4 py-2 min-h-[44px] rounded-lg text-sm font-medium hover:bg-indigo-700">
            {{ __('academic_structure.add_new') }}
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('academic_structure.name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('academic_structure.start_date') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('academic_structure.end_date') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('academic_structure.is_current') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('academic_structure.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($years as $year)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $year->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $year->start_date?->format('d M Y') ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $year->end_date?->format('d M Y') ?? '-' }}</td>
                        <td class="px-6 py-4">
                            @if($year->is_current)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">✓</span>
                            @else
                                <button wire:click="toggleCurrent({{ $year->id }})" class="text-xs text-indigo-600 hover:text-indigo-800">
                                    {{ __('academic_structure.set_current') }}
                                </button>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            <button wire:click="edit({{ $year->id }})" class="text-indigo-600 hover:text-indigo-800">{{ __('academic_structure.edit') }}</button>
                            <button wire:click="delete({{ $year->id }})" wire:confirm="{{ __('academic_structure.confirm_delete') }}" class="text-red-600 hover:text-red-800">{{ __('academic_structure.delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('academic_structure.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-6 py-3 border-t border-gray-200">{{ $years->links() }}</div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto" x-on:click.self="$wire.set('showModal', false)">
            <div class="fixed inset-0 bg-black/50"></div>
            <div class="flex min-h-full items-center justify-center p-4" x-on:click.self="$wire.set('showModal', false)">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-on:click.stop>
                    <h2 class="text-lg font-bold mb-4">{{ $editingId ? __('academic_structure.edit') : __('academic_structure.add_new') }}</h2>
                    <form wire:submit="save" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('academic_structure.year_name') }} *</label>
                            <input type="text" wire:model="name" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('academic_structure.start_date') }}</label>
                                <input type="date" wire:model="start_date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('academic_structure.end_date') }}</label>
                                <input type="date" wire:model="end_date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" wire:model="is_current" id="is_current" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="is_current" class="text-sm text-gray-700">{{ __('academic_structure.is_current') }}</label>
                        </div>
                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" x-on:click="$wire.set('showModal', false)" class="px-4 py-2 min-h-[44px] text-sm text-gray-700 hover:bg-gray-100 rounded-lg">{{ __('academic_structure.cancel') }}</button>
                            <button type="submit" class="px-4 py-2 min-h-[44px] text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">{{ __('academic_structure.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
