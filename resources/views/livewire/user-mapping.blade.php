<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Device User Mappings</h1>
        <button wire:click="openModal" class="bg-indigo-600 text-white px-4 py-2 min-h-[44px] rounded-lg text-sm font-medium hover:bg-indigo-700">
            Add Mapping
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device User ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Roll</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($mappings as $mapping)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $mapping->device->name ?? $mapping->device->serial_number }}</td>
                            <td class="px-6 py-4 text-sm font-mono text-gray-900">{{ $mapping->device_user_id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $mapping->student->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $mapping->student->roll }}</td>
                            <td class="px-6 py-4 text-right text-sm space-x-2">
                                <button wire:click="edit({{ $mapping->id }})" class="text-indigo-600 hover:text-indigo-800">Edit</button>
                                <button wire:click="delete({{ $mapping->id }})" wire:confirm="Delete this mapping?" class="text-red-600 hover:text-red-800">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No mappings configured yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-gray-200">{{ $mappings->links() }}</div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto" x-on:click.self="$wire.set('showModal', false)">
            <div class="fixed inset-0 bg-black/50"></div>
            <div class="flex min-h-full items-center justify-center p-4" x-on:click.self="$wire.set('showModal', false)">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-on:click.stop>
                    <h2 class="text-lg font-bold mb-4">{{ $editingId ? 'Edit Mapping' : 'Add Mapping' }}</h2>
                    <form wire:submit="save" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Device *</label>
                            <select wire:model="biometric_device_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Select device</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name ?? $device->serial_number }}</option>
                                @endforeach
                            </select>
                            @error('biometric_device_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Device User ID (PIN) *</label>
                            <input type="number" wire:model="device_user_id" min="1" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('device_user_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Student *</label>
                            <select wire:model="student_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Select student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }} (Roll: {{ $student->roll }})</option>
                                @endforeach
                            </select>
                            @error('student_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button type="button" x-on:click="$wire.set('showModal', false)" class="px-4 py-2 min-h-[44px] text-sm text-gray-700 hover:bg-gray-100 rounded-lg">Cancel</button>
                            <button type="submit" class="px-4 py-2 min-h-[44px] text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
