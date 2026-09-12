<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('user_management.user_management') }}</h1>
        <button wire:click="openCreateModal"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            {{ __('user_management.add_user') }}
        </button>
    </div>

    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="{{ __('user_management.search_users') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="sm:w-48">
                <select wire:model.live="roleFilter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('user_management.all_roles') }}</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('user_management.name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('user_management.email') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('user_management.role') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('user_management.status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('user_management.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $role->name === 'institute-admin' ? 'bg-purple-100 text-purple-800' :
                                           ($role->name === 'teacher' ? 'bg-blue-100 text-blue-800' :
                                           ($role->name === 'student' ? 'bg-green-100 text-green-800' :
                                           ($role->name === 'accountant' ? 'bg-yellow-100 text-yellow-800' :
                                           'bg-gray-100 text-gray-800'))) }}">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user->is_active ? __('user_management.active') : __('user_management.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="editUser({{ $user->id }})"
                                        class="text-indigo-600 hover:text-indigo-900 mr-3">
                                    {{ __('user_management.edit') }}
                                </button>
                                <button wire:click="toggleActive({{ $user->id }})"
                                        class="{{ $user->is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}">
                                    {{ $user->is_active ? __('user_management.deactivate') : __('user_management.activate') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                                {{ __('user_management.no_users_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeCreateModal"></div>
                <div class="relative bg-white rounded-lg shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white rounded-lg px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            {{ $editingUserId ? __('user_management.edit_user') : __('user_management.create_user') }}
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('user_management.name') }}</label>
                                <input type="text" wire:model="name"
                                       class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('user_management.email') }}</label>
                                <input type="email" wire:model="email"
                                       class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('user_management.phone') }}</label>
                                <input type="text" wire:model="phone"
                                       class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('user_management.password') }}</label>
                                <input type="password" wire:model="password"
                                       class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                @if($editingUserId)
                                    <p class="mt-1 text-xs text-gray-500">{{ __('user_management.leave_blank_to_keep') }}</p>
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('user_management.confirm_password') }}</label>
                                <input type="password" wire:model="password_confirmation"
                                       class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('user_management.role') }}</label>
                                <select wire:model="selectedRole"
                                        class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">{{ __('user_management.select_role') }}</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedRole') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button wire:click="{{ $editingUserId ? 'updateUser' : 'createUser' }}"
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:w-auto sm:text-sm">
                            {{ $editingUserId ? __('user_management.update') : __('user_management.create') }}
                        </button>
                        <button wire:click="closeCreateModal"
                                class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm">
                            {{ __('user_management.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
