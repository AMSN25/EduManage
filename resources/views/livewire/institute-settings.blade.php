<div>
    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('dashboard.institute_settings') }}</h1>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form wire:submit="save">
                <div class="space-y-6">
                    <div>
                        <label for="default_language" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('dashboard.default_language') }}
                        </label>
                        <select wire:model="default_language" id="default_language"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="bn">{{ __('dashboard.bangla') }}</option>
                            <option value="en">{{ __('dashboard.english') }}</option>
                        </select>
                        @error('default_language')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('dashboard.currency') }}
                        </label>
                        <input type="text" wire:model="currency" id="currency"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                               placeholder="BDT">
                        @error('currency')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="current_academic_year" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('dashboard.current_academic_year') }}
                        </label>
                        <input type="text" wire:model="current_academic_year" id="current_academic_year"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                               placeholder="2026">
                        @error('current_academic_year')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- SMS Configuration --}}
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-sm font-semibold text-gray-800 mb-4">{{ __('sms.sms') }} {{ __('dashboard.settings') }}</h3>

                        <div class="space-y-4">
                            <div>
                                <label for="sms_provider" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('sms.sms_provider') }}
                                </label>
                                <select wire:model="sms_provider" id="sms_provider"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="log">{{ __('sms.log_driver') }} ({{ __('sms.development') }})</option>
                                    <option value="http_post">{{ __('sms.http_post') }}</option>
                                </select>
                                @error('sms_provider')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            @if($sms_provider === 'http_post')
                                <div>
                                    <label for="sms_api_url" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('sms.api_url') }}
                                    </label>
                                    <input type="url" wire:model="sms_api_url" id="sms_api_url"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                           placeholder="https://api.sms-provider.com/send">
                                    @error('sms_api_url')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="sms_api_key" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('sms.api_key') }}
                                    </label>
                                    <input type="password" wire:model="sms_api_key" id="sms_api_key"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    @error('sms_api_key')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="sms_sender_id" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('sms.sender_id') }}
                                    </label>
                                    <input type="text" wire:model="sms_sender_id" id="sms_sender_id"
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                           placeholder="EDUMANAGE">
                                    @error('sms_sender_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                            class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition text-sm">
                        {{ __('dashboard.save_settings') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
