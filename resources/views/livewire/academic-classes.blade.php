<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('academic_structure.classes') }}</h1>
        <button wire:click="openModal" class="bg-indigo-600 text-white px-4 py-2 min-h-[44px] rounded-lg text-sm font-medium hover:bg-indigo-700">
            {{ __('academic_structure.add_new') }}
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('academic_structure.name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('academic_structure.academic_year') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('academic_structure.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($classes as $class)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $class->numeric_order }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $class->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $class->academicYear?->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            <button wire:click="edit({{ $class->id }})" class="text-indigo-600 hover:text-indigo-800">{{ __('academic_structure.edit') }}</button>
                            <button wire:click="delete({{ $class->id }})" wire:confirm="{{ __('academic_structure.confirm_delete') }}" class="text-red-600 hover:text-red-800">{{ __('academic_structure.delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('academic_structure.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-6 py-3 border-t border-gray-200">{{ $classes->links() }}</div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto" x-on:click.self="$wire.set('showModal', false)">
            <div class="fixed inset-0 bg-black/50"></div>
            <div class="flex min-h-full items-center justify-center p-4" x-on:click.self="$wire.set('showModal', false)">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-on:click.stop>
                    <h2 class="text-lg font-bold mb-4">{{ $editingId ? __('academic_structure.edit') : __('academic_structure.add_new') }}</h2>
                    <form wire:submit="save" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('academic_structure.class_name') }} *</label>
                            <input type="text" wire:model="name" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('academic_structure.numeric_order') }} *</label>
                            <input type="number" wire:model="numeric_order" min="1" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('numeric_order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('academic_structure.academic_year') }}</label>
                            <select wire:model="academic_year_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">{{ __('dashboard.select') ?? 'Select' }}</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
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
