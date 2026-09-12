<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('exams.create_exam') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ __('exams.wizard_hint') }}</p>
    </div>

    {{-- Step Indicator --}}
    <div class="flex items-center mb-8 overflow-x-auto">
        @foreach(['details' => __('exams.step_details'), 'classes' => __('exams.step_classes'), 'subjects' => __('exams.step_subjects'), 'review' => __('exams.step_review')] as $step => $label)
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium
                    {{ $step === $this->step ? 'bg-indigo-600 text-white' : (array_search($step, ['details', 'classes', 'subjects', 'review']) < array_search($this->step, ['details', 'classes', 'subjects', 'review']) ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600') }}">
                    {{ array_search($step, ['details', 'classes', 'subjects', 'review']) + 1 }}
                </div>
                <span class="ml-2 text-sm font-medium {{ $step === $this->step ? 'text-indigo-600' : 'text-gray-500' }}">{{ $label }}</span>
                @if(!$loop->last)
                    <div class="w-12 h-0.5 bg-gray-200 mx-4"></div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Step: Details --}}
    @if($step === 'details')
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('exams.exam_details') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('exams.name') }}</label>
                    <input type="text" wire:model="name" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('exams.name_placeholder') }}">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('exams.start_date') }}</label>
                    <input type="date" wire:model="startDate" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('startDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('exams.end_date') }}</label>
                    <input type="date" wire:model="endDate" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('endDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    @endif

    {{-- Step: Classes --}}
    @if($step === 'classes')
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('exams.select_classes') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($allClasses as $class)
                    <label class="flex items-center gap-2 p-3 rounded-lg border {{ in_array($class['id'], $selectedClassIds) ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }} cursor-pointer">
                        <input type="checkbox" value="{{ $class['id'] }}" wire:model="selectedClassIds" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-900">{{ $class['name'] }}</span>
                    </label>
                @endforeach
            </div>
            @error('selectedClassIds') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    @endif

    {{-- Step: Subjects --}}
    @if($step === 'subjects')
        <div class="space-y-6">
            @foreach($subjects as $classId => $classSubjects)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-900">{{ $classNames[$classId] ?? '-' }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('exams.subject') }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('exams.full_marks') }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('exams.pass_marks') }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('exams.cq_marks') }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('exams.mcq_marks') }}</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('exams.practical_marks') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($classSubjects as $subjectId => $markConfig)
                                    <tr>
                                        <td class="px-6 py-3 font-medium text-gray-900">{{ $subjectNames[$subjectId] ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            <input type="number" wire:change="updateSubjectMark('{{ $classId }}', '{{ $subjectId }}', 'full_marks', $event.target.value)" value="{{ $markConfig['full_marks'] }}" class="w-20 text-center rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" wire:change="updateSubjectMark('{{ $classId }}', '{{ $subjectId }}', 'pass_marks', $event.target.value)" value="{{ $markConfig['pass_marks'] }}" class="w-20 text-center rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" wire:change="updateSubjectMark('{{ $classId }}', '{{ $subjectId }}', 'cq_marks', $event.target.value)" value="{{ $markConfig['cq_marks'] }}" class="w-20 text-center rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="-">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" wire:change="updateSubjectMark('{{ $classId }}', '{{ $subjectId }}', 'mcq_marks', $event.target.value)" value="{{ $markConfig['mcq_marks'] }}" class="w-20 text-center rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="-">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" wire:change="updateSubjectMark('{{ $classId }}', '{{ $subjectId }}', 'practical_marks', $event.target.value)" value="{{ $markConfig['practical_marks'] }}" class="w-20 text-center rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="-">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Step: Review --}}
    @if($step === 'review')
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('exams.review') }}</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-500">{{ __('exams.name') }}</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">{{ __('exams.dates') }}</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $startDate }} — {{ $endDate }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">{{ __('exams.classes') }}</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ count($selectedClassIds) }} {{ __('exams.selected') }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">{{ __('exams.subjects') }}</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ collect($subjects)->flatten()->count() }} {{ __('exams.total_entries') }}</dd>
                </div>
            </dl>
        </div>
    @endif

    {{-- Navigation Buttons --}}
    <div class="flex justify-between mt-6">
        @if($step !== 'details')
            <button wire:click="prevStep" class="min-h-[44px] px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                {{ __('exams.previous') }}
            </button>
        @else
            <div></div>
        @endif

        @if($step === 'review')
            <button wire:click="save" class="min-h-[44px] px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700">
                {{ __('exams.create_exam') }}
            </button>
        @else
            <button wire:click="nextStep" class="min-h-[44px] px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700">
                {{ __('exams.next') }}
            </button>
        @endif
    </div>
</div>
