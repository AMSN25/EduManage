<div>
    <div class="mb-6">
        <a href="{{ route('students.index') }}" class="text-indigo-600 hover:text-indigo-500 text-sm">
            &larr; {{ __('student.back_to_students') }}
        </a>
    </div>

    {{-- Student Header --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
            @if($student->photo_path)
                <img src="{{ Storage::url($student->photo_path) }}" class="w-20 h-20 rounded-full object-cover">
            @else
                <div class="w-20 h-20 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-2xl font-bold text-indigo-600">{{ substr($student->name, 0, 1) }}</span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $student->name }}</h1>
                @if($student->name_bangla)
                    <p class="text-gray-500">{{ $student->name_bangla }}</p>
                @endif
                <p class="text-sm text-gray-500 mt-1">
                    {{ $student->student_id }} | {{ $student->classModel?->name }} - {{ $student->section?->name }}
                </p>
            </div>
            <div class="ml-auto">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    {{ $student->status === 'active' ? 'bg-green-100 text-green-800' :
                       ($student->status === 'inactive' ? 'bg-gray-100 text-gray-800' :
                       ($student->status === 'transferred' ? 'bg-yellow-100 text-yellow-800' :
                       'bg-blue-100 text-blue-800')) }}">
                    {{ __('student.' . $student->status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 overflow-x-auto px-6">
                @foreach([
                    'overview' => __('student.overview'),
                    'academic' => __('student.academic'),
                    'guardian' => __('student.guardian'),
                    'attendance' => __('student.attendance'),
                    'results' => __('student.results'),
                    'fees' => __('student.fees'),
                    'documents' => __('student.documents'),
                ] as $key => $label)
                    <button wire:click="setTab('{{ $key }}')"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === $key ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        <div class="p-6">
            {{-- Overview Tab --}}
            @if($activeTab === 'overview')
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">{{ __('student.personal_info') }}</h3>
                        <dl class="mt-2 space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">{{ __('student.gender') }}</dt>
                                <dd class="text-sm text-gray-900">{{ __('student.' . $student->gender) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">{{ __('student.dob') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $student->dob?->format('d M Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">{{ __('student.blood_group') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $student->blood_group }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">{{ __('student.religion') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $student->religion }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">{{ __('student.contact') }}</h3>
                        <dl class="mt-2 space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">{{ __('student.phone') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $student->phone }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">{{ __('student.email') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $student->email }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">{{ __('student.academic') }}</h3>
                        <dl class="mt-2 space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">{{ __('student.class') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $student->classModel?->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">{{ __('student.section') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $student->section?->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">{{ __('student.group') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $student->group?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">{{ __('student.roll') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $student->roll }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">{{ __('student.admission_date') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $student->admission_date?->format('d M Y') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            @endif

            {{-- Academic Tab --}}
            @if($activeTab === 'academic')
                <div class="text-center py-8 text-gray-500">
                    {{ __('student.academic_placeholder') }}
                </div>
            @endif

            {{-- Guardian Tab --}}
            @if($activeTab === 'guardian')
                @if($student->guardians->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        {{ __('student.no_guardians') }}
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        @foreach($student->guardians as $guardian)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-2">{{ __('student.' . $guardian->relation) }}</h4>
                                <dl class="space-y-1">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500">{{ __('student.name') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $guardian->name }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500">{{ __('student.phone') }}</dt>
                                        <dd class="text-sm text-gray-900">{{ $guardian->phone }}</dd>
                                    </div>
                                    @if($guardian->occupation)
                                        <div class="flex justify-between">
                                            <dt class="text-sm text-gray-500">{{ __('student.occupation') }}</dt>
                                            <dd class="text-sm text-gray-900">{{ $guardian->occupation }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            {{-- Attendance Tab --}}
            @if($activeTab === 'attendance')
                @if($attendanceStats)
                    <div class="space-y-6">
                        {{-- Stats Summary --}}
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-gray-900">{{ $attendanceStats['total'] }}</p>
                                <p class="text-xs text-gray-500">{{ __('attendance.total') }}</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-green-700">{{ $attendanceStats['present'] }}</p>
                                <p class="text-xs text-green-600">{{ __('attendance.present') }}</p>
                            </div>
                            <div class="bg-red-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-red-700">{{ $attendanceStats['absent'] }}</p>
                                <p class="text-xs text-red-600">{{ __('attendance.absent') }}</p>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-yellow-700">{{ $attendanceStats['late'] }}</p>
                                <p class="text-xs text-yellow-600">{{ __('attendance.late') }}</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-blue-700">{{ $attendanceStats['percentage'] }}%</p>
                                <p class="text-xs text-blue-600">{{ __('attendance.percentage') }}</p>
                            </div>
                        </div>

                        {{-- Recent Attendance --}}
                        <div class="bg-white rounded-lg border border-gray-200">
                            <div class="px-4 py-3 border-b border-gray-200">
                                <h3 class="font-medium text-gray-900">{{ __('attendance.attendance_report') }}</h3>
                            </div>
                            @if($recentAttendance->isEmpty())
                                <div class="p-4 text-center text-gray-500">{{ __('attendance.no_data') }}</div>
                            @else
                                <div class="divide-y divide-gray-100">
                                    @foreach($recentAttendance as $record)
                                        <div class="px-4 py-3 flex items-center justify-between">
                                            <span class="text-sm text-gray-900">{{ $record['date']->format('d M Y') }}</span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ match($record['status']) {
                                                    'present' => 'bg-green-100 text-green-800',
                                                    'absent' => 'bg-red-100 text-red-800',
                                                    'late' => 'bg-yellow-100 text-yellow-800',
                                                    'leave' => 'bg-blue-100 text-blue-800',
                                                    default => 'bg-gray-100 text-gray-800',
                                                } }}">
                                                {{ __('attendance.' . $record['status']) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        {{ __('attendance.no_data') }}
                    </div>
                @endif
            @endif

            {{-- Results Tab (placeholder) --}}
            @if($activeTab === 'results')
                <div class="text-center py-8 text-gray-500">
                    {{ __('student.results_placeholder') }}
                </div>
            @endif

            {{-- Fees Tab --}}
            @if($activeTab === 'fees')
                @if($feeData)
                    <div class="space-y-6">
                        {{-- Fee Summary --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($feeData['total_due'], 2) }}</p>
                                <p class="text-xs text-gray-500">{{ __('fees.total_due') }}</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-green-700">{{ number_format($feeData['total_paid'], 2) }}</p>
                                <p class="text-xs text-green-600">{{ __('fees.total_paid') }}</p>
                            </div>
                            <div class="bg-red-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-red-700">{{ number_format($feeData['remaining'], 2) }}</p>
                                <p class="text-xs text-red-600">{{ __('fees.remaining') }}</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-blue-700">{{ $feeData['all']->count() }}</p>
                                <p class="text-xs text-blue-600">{{ __('fees.total_records') }}</p>
                            </div>
                        </div>

                        {{-- Current Dues --}}
                        @if($feeData['current_dues']->count())
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">{{ __('fees.current_dues') }}</h3>
                                </div>
                                <div class="divide-y divide-gray-100">
                                    @foreach($feeData['current_dues'] as $fee)
                                        @php $remaining = (float) $fee->amount_due - (float) $fee->amount_paid; @endphp
                                        <div class="px-4 py-3 flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $fee->feeStructure->feeType->name ?? 'Fee' }}</p>
                                                <p class="text-xs text-gray-500">{{ $fee->month ?? '-' }} | Due: {{ $fee->due_date->format('d M Y') }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-red-600">{{ number_format($remaining, 2) }} BDT</p>
                                                @if($fee->status === 'partial')
                                                    <p class="text-xs text-yellow-600">{{ __('fees.partial') }}: {{ number_format($fee->amount_paid, 2) }} paid</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Arrears --}}
                        @if($feeData['arrears']->count())
                            <div class="bg-white rounded-lg border border-red-200">
                                <div class="px-4 py-3 border-b border-red-200 bg-red-50">
                                    <h3 class="font-medium text-red-900">{{ __('fees.arrears') }}</h3>
                                </div>
                                <div class="divide-y divide-gray-100">
                                    @foreach($feeData['arrears'] as $fee)
                                        @php $remaining = (float) $fee->amount_due - (float) $fee->amount_paid; @endphp
                                        <div class="px-4 py-3 flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $fee->feeStructure->feeType->name ?? 'Fee' }}</p>
                                                <p class="text-xs text-red-500">{{ $fee->month ?? '-' }} | Due: {{ $fee->due_date->format('d M Y') }} (overdue)</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-red-600">{{ number_format($remaining, 2) }} BDT</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Paid History --}}
                        @if($feeData['paid']->count())
                            <div class="bg-white rounded-lg border border-gray-200">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <h3 class="font-medium text-gray-900">{{ __('fees.payment_history') }}</h3>
                                </div>
                                <div class="divide-y divide-gray-100">
                                    @foreach($feeData['paid'] as $fee)
                                        <div class="px-4 py-3 flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $fee->feeStructure->feeType->name ?? 'Fee' }}</p>
                                                <p class="text-xs text-gray-500">{{ $fee->month ?? '-' }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-green-600">{{ number_format($fee->amount_paid, 2) }} BDT</p>
                                                <p class="text-xs text-green-500">{{ __('fees.paid') }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($feeData['all']->isEmpty())
                            <div class="text-center py-8 text-gray-500">
                                {{ __('fees.no_fee_records') }}
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        {{ __('fees.loading') }}
                    </div>
                @endif
            @endif

            {{-- Documents Tab --}}
            @if($activeTab === 'documents')
                <div class="text-center py-8 text-gray-500">
                    {{ __('student.documents_placeholder') }}
                </div>
            @endif
        </div>
    </div>
</div>
