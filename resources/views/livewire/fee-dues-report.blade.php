<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('fees.dues_report') }}</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 rounded-lg text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('fees.filter_class') }}</label>
                <select wire:model="classId" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('fees.all_classes') }}</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('fees.search') }}</label>
                <input type="text" wire:model.debounce.300ms="search" wire:change="loadReport"
                    placeholder="{{ __('fees.search_student_name') }}"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="flex items-end gap-3">
                <button wire:click="loadReport" class="px-4 py-2 min-h-[44px] text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    {{ __('fees.load_report') }}
                </button>
                @if($students->count())
                    <button wire:click="exportExcel" class="px-4 py-2 min-h-[44px] text-sm font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200">
                        {{ __('fees.export_excel') }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if($students->count())
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('fees.student_id') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('fees.student_name') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('fees.class') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('fees.total_due') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('fees.total_paid') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('fees.remaining') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('fees.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($students as $student)
                        @php
                            $remaining = (float) $student->total_due - (float) $student->total_paid;
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-gray-600">{{ $student->student_id }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $student->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $student->classModel->name ?? '' }} {{ $student->section->name ?? '' }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ number_format($student->total_due, 2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ number_format($student->total_paid, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-red-600">{{ number_format($remaining, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="sendReminder({{ $student->id }})"
                                    class="text-sm text-indigo-600 hover:text-indigo-500">
                                    {{ __('fees.send_reminder') }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    @else
        <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
            <p class="text-gray-500">{{ __('fees.no_dues') }}</p>
        </div>
    @endif
</div>
