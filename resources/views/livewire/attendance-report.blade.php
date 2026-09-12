<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('attendance.attendance_report') }}</h1>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.class') }}</label>
                <select wire:model.live="classId"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('attendance.select_class') }}</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.section') }}</label>
                <select wire:model.live="sectionId"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('attendance.select_section') }}</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.month') }}</label>
                <select wire:model.live="month"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.year') }}</label>
                <select wire:model.live="year"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach(range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($classId && $sectionId && $monthlyData)
        {{-- Monthly Grid --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">{{ __('attendance.monthly_grid') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase sticky left-0 bg-gray-50">{{ __('attendance.student') }}</th>
                            @for($day = 1; $day <= $monthlyData['days']; $day++)
                                @php $dateStr = $monthlyData['startDate']->copy()->day($day)->format('Y-m-d'); @endphp
                                <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 w-10">
                                    <div>{{ $day }}</div>
                                    @if(isset($monthlyData['attendances'][$dateStr]))
                                        <button wire:click="notifyGuardians({{ $monthlyData['attendances'][$dateStr]->id }})"
                                            class="text-indigo-600 hover:text-indigo-500 mt-1 text-[10px] underline"
                                            wire:confirm="{{ __('attendance.confirm_notify_guardians') }}">
                                            {{ __('sms.notify') }}
                                        </button>
                                    @endif
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($monthlyData['students'] as $student)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900 sticky left-0 bg-white">
                                    {{ $student->name }}
                                </td>
                                @for($day = 1; $day <= $monthlyData['days']; $day++)
                                    @php
                                        $dateStr = $monthlyData['startDate']->copy()->day($day)->format('Y-m-d');
                                        $attendance = $monthlyData['attendances']->get($dateStr);
                                        $record = $attendance?->records->firstWhere('student_id', $student->id);
                                    @endphp
                                    <td class="px-2 py-3 text-center">
                                        @if($record)
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded text-xs font-bold
                                                {{ $record->status === 'present' ? 'bg-green-100 text-green-800' :
                                                   ($record->status === 'absent' ? 'bg-red-100 text-red-800' :
                                                   ($record->status === 'late' ? 'bg-yellow-100 text-yellow-800' :
                                                   'bg-blue-100 text-blue-800')) }}">
                                                {{ strtoupper(substr($record->status, 0, 1)) }}
                                            </span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Student Statistics --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">{{ __('attendance.student_statistics') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('attendance.student') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('attendance.total') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('attendance.present') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('attendance.absent') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('attendance.late') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('attendance.leave') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('attendance.percentage') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($studentStats as $stat)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900">{{ $stat['student']->name }}</td>
                                <td class="px-4 py-3 text-center">{{ $stat['total'] }}</td>
                                <td class="px-4 py-3 text-center text-green-600 font-medium">{{ $stat['present'] }}</td>
                                <td class="px-4 py-3 text-center text-red-600 font-medium">{{ $stat['absent'] }}</td>
                                <td class="px-4 py-3 text-center text-yellow-600 font-medium">{{ $stat['late'] }}</td>
                                <td class="px-4 py-3 text-center text-blue-600 font-medium">{{ $stat['leave'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-bold {{ $stat['percentage'] >= 75 ? 'text-green-600' : ($stat['percentage'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $stat['percentage'] }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    {{ __('attendance.no_data') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
