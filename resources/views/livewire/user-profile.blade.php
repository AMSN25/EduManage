<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('user_management.my_profile') }}</h1>

    {{-- Success Message --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Profile Information --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('user_management.profile_information') }}</h2>
        <form wire:submit.prevent="updateProfile" class="space-y-4">
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
            <div class="flex justify-end">
                <button type="submit"
                        class="min-h-[44px] bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    {{ __('user_management.update_profile') }}
                </button>
            </div>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('user_management.change_password') }}</h2>
        <form wire:submit.prevent="changePassword" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('user_management.current_password') }}</label>
                <input type="password" wire:model="current_password"
                       class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('user_management.new_password') }}</label>
                <input type="password" wire:model="new_password"
                       class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('new_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('user_management.confirm_password') }}</label>
                <input type="password" wire:model="new_password_confirmation"
                       class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="flex justify-end">
                <button type="submit"
                        class="min-h-[44px] bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    {{ __('user_management.change_password') }}
                </button>
            </div>
        </form>
    </div>
</div>
