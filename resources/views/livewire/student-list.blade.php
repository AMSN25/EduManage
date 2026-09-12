<div>
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('student.students') }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('students.export') }}?class_id={{ $classFilter }}&section_id={{ $sectionFilter }}&status={{ $statusFilter }}&search={{ $search }}"
               class="min-h-[44px] inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                {{ __('student.export_excel') }}
            </a>
            <button wire:click="$dispatch('showImportModal')"
                    class="min-h-[44px] bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                {{ __('student.import_excel') }}
            </button>
            <button wire:click="$dispatch('openStudentForm')"
                    class="min-h-[44px] bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                {{ __('student.add_student') }}
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="{{ __('student.search_students') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <select wire:model.live="classFilter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('student.all_classes') }}</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="sectionFilter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('student.all_sections') }}</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="statusFilter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('student.all_statuses') }}</option>
                    <option value="active">{{ __('student.active') }}</option>
                    <option value="inactive">{{ __('student.inactive') }}</option>
                    <option value="transferred">{{ __('student.transferred') }}</option>
                    <option value="graduated">{{ __('student.graduated') }}</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Students Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('student.student_id') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('student.name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('student.class') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('student.section') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('student.roll') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('student.status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('student.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($students as $student)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $student->student_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($student->photo_path)
                                        <img src="{{ Storage::url($student->photo_path) }}" class="w-8 h-8 rounded-full mr-3">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                            <span class="text-sm font-medium text-indigo-600">{{ substr($student->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $student->name }}</div>
                                        @if($student->name_bangla)
                                            <div class="text-xs text-gray-500">{{ $student->name_bangla }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $student->classModel?->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $student->section?->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $student->roll }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $student->status === 'active' ? 'bg-green-100 text-green-800' :
                                       ($student->status === 'inactive' ? 'bg-gray-100 text-gray-800' :
                                       ($student->status === 'transferred' ? 'bg-yellow-100 text-yellow-800' :
                                       'bg-blue-100 text-blue-800')) }}">
                                    {{ __('student.' . $student->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('students.profile', $student->id) }}"
                                   class="text-indigo-600 hover:text-indigo-900 mr-3">
                                    {{ __('student.view') }}
                                </a>
                                <button wire:click="$dispatch('editStudent', { studentId: {{ $student->id }} })"
                                        class="text-yellow-600 hover:text-yellow-900 mr-3">
                                    {{ __('student.edit') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                                {{ __('student.no_students_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $students->links() }}
        </div>
    </div>

    {{-- Student Form Modal --}}
    @livewire(\App\Http\Livewire\StudentForm::class, key('student-form-' . now()->timestamp))
</div>
