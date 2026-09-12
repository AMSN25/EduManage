<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('sms.notices') }}</h1>
        <button wire:click="toggleForm"
            class="min-h-[44px] px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
            {{ $showForm ? __('sms.cancel') : __('sms.create_notice') }}
        </button>
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

    @if($showForm)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                {{ $editingId ? __('sms.edit_notice') : __('sms.create_notice') }}
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('sms.title') }}</label>
                    <input type="text" wire:model="title" class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('sms.body') }}</label>
                    <textarea wire:model="body" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm text-sm"></textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('sms.audience') }}</label>
                        <select wire:model="audience" class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
                            <option value="all">{{ __('sms.all') }}</option>
                            <option value="class">{{ __('sms.specific_class') }}</option>
                            <option value="teachers">{{ __('sms.teachers_only') }}</option>
                        </select>
                    </div>
                    @if($audience === 'class')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('sms.class') }}</label>
                            <select wire:model="classId" class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
                                <option value="">{{ __('sms.select_class') }}</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="sendSms" id="sendSms" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="sendSms" class="text-sm text-gray-700">{{ __('sms.also_send_sms') }}</label>
                </div>
                <button wire:click="save" class="min-h-[44px] px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    {{ __('sms.save_notice') }}
                </button>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('sms.title') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('sms.audience') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('sms.sms_sent') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('sms.published_at') }}</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('sms.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($notices as $notice)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $notice['title'] }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ match($notice['audience']) {
                                'all' => __('sms.all_students'),
                                'class' => $notice['class_model']['name'] ?? __('sms.class'),
                                'teachers' => __('sms.teachers_only'),
                                default => $notice['audience'],
                            } }}
                        </td>
                        <td class="px-4 py-3">
                            @if($notice['send_sms'])
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('sms.yes') }}</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ __('sms.no') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-sm">{{ $notice['published_at'] ? \Carbon\Carbon::parse($notice['published_at'])->format('d M Y, h:i A') : '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="edit({{ $notice['id'] }})" class="text-indigo-600 hover:text-indigo-500 text-sm">
                                {{ __('sms.edit') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('sms.no_notices') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
