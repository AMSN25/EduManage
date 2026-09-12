<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('exams.exams') }}</h1>
        @if(auth()->user()->can('manage', \App\Models\Exam::class))
            <a href="{{ route('exams.create') }}" class="px-4 py-2 min-h-[44px] text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                {{ __('exams.create_exam') }}
            </a>
        @endif
    </div>

    @if($exams->isEmpty())
        <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
            <p class="text-gray-500">{{ __('exams.no_exams') }}</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('exams.name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('exams.academic_year') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('exams.dates') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('exams.status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('exams.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($exams as $exam)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $exam->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $exam->academicYear?->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $exam->start_date->format('d M Y') }} — {{ $exam->end_date->format('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ match($exam->status) {
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'ongoing' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-yellow-100 text-yellow-800',
                                        'published' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    } }}">
                                    {{ __('exams.status_' . $exam->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('marks.entry', ['examId' => $exam->id]) }}" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                                    {{ __('exams.enter_marks') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    @endif
</div>
