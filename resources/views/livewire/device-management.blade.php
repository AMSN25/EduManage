<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Biometric Devices</h1>
        <button wire:click="openModal" class="bg-indigo-600 text-white px-4 py-2 min-h-[44px] rounded-lg text-sm font-medium hover:bg-indigo-700">
            Add Device
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serial Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Seen</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($devices as $device)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono font-medium text-gray-900">{{ $device->serial_number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $device->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $device->model ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ match($device->status) {
                                        'active' => 'bg-green-100 text-green-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    } }}">
                                    {{ ucfirst($device->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm space-x-2">
                                <button wire:click="edit({{ $device->id }})" class="text-indigo-600 hover:text-indigo-800">Edit</button>
                                @if($device->status === 'pending')
                                    <button wire:click="activate({{ $device->id }})" class="text-green-600 hover:text-green-800">Activate</button>
                                @elseif($device->status === 'active')
                                    <button wire:click="deactivate({{ $device->id }})" class="text-yellow-600 hover:text-yellow-800">Deactivate</button>
                                @else
                                    <button wire:click="activate({{ $device->id }})" class="text-green-600 hover:text-green-800">Activate</button>
                                @endif
                                <button wire:click="delete({{ $device->id }})" wire:confirm="Delete this device?" class="text-red-600 hover:text-red-800">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No devices registered yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-gray-200">{{ $devices->links() }}</div>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto" x-on:click.self="$wire.set('showModal', false)">
            <div class="fixed inset-0 bg-black/50"></div>
            <div class="flex min-h-full items-center justify-center p-4" x-on:click.self="$wire.set('showModal', false)">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-on:click.stop>
                    <h2 class="text-lg font-bold mb-4">{{ $editingId ? 'Edit Device' : 'Add Device' }}</h2>
                    <form wire:submit="save" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Serial Number *</label>
                            <input type="text" wire:model="serial_number" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('serial_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" wire:model="name" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                            <input type="text" wire:model="model" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
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
