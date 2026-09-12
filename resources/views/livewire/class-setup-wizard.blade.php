<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('academic_structure.wizard_title') }}</h1>

    {{-- Step Indicators --}}
    <div class="flex items-center gap-4 mb-8 overflow-x-auto">
        @foreach([1 => __('academic_structure.step_1'), 2 => __('academic_structure.step_2'), 3 => __('academic_structure.step_3')] as $s => $label)
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold {{ $step >= $s ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                    {{ $s }}
                </div>
                <span class="text-sm font-medium {{ $step >= $s ? 'text-gray-900' : 'text-gray-400' }}">{{ $label }}</span>
            </div>
            @if($s < 3)
                <div class="flex-1 h-0.5 {{ $step > $s ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
            @endif
        @endforeach
    </div>

    {{-- Step 1: Select Academic Year --}}
    @if($step === 1)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-600 mb-4">{{ __('academic_structure.select_year_hint') }}</p>
            <div class="max-w-md">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('academic_structure.academic_year') }} *</label>
                <select wire:model="academicYearId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">{{ __('academic_structure.select_year_hint') }}</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end mt-6">
                <button wire:click="nextStep" class="min-h-[44px] px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                    {{ __('academic_structure.next') }} →
                </button>
            </div>
        </div>
    @endif

    {{-- Step 2: Add Classes & Sections --}}
    @if($step === 2)
        <div class="space-y-4">
            @foreach($classes as $ci => $classData)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3 flex-1">
                            <input type="text" wire:model="classes.{{ $ci }}.name" placeholder="{{ __('academic_structure.class_name') }}" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <input type="number" wire:model="classes.{{ $ci }}.numeric_order" placeholder="{{ __('academic_structure.numeric_order') }}" min="1" class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <button wire:click="removeClass({{ $ci }})" class="ml-3 min-h-[44px] text-red-500 hover:text-red-700 text-sm">{{ __('academic_structure.remove_class') }}</button>
                    </div>
                    <div class="ml-4 space-y-2">
                        <p class="text-xs font-medium text-gray-500 uppercase">{{ __('academic_structure.sections') }}</p>
                        @foreach($classData['sections'] as $si => $section)
                            <div class="flex items-center gap-2">
                                <input type="text" wire:model="classes.{{ $ci }}.sections.{{ $si }}" placeholder="{{ __('academic_structure.section_name') }}" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <button wire:click="removeSection({{ $ci }}, {{ $si }})" class="min-h-[44px] text-red-400 hover:text-red-600 text-xs">✕</button>
                            </div>
                        @endforeach
                        <button wire:click="addSection({{ $ci }})" class="min-h-[44px] text-xs text-indigo-600 hover:text-indigo-800">+ {{ __('academic_structure.add_section') }}</button>
                    </div>
                </div>
            @endforeach
            <button wire:click="addClass" class="min-h-[44px] w-full border-2 border-dashed border-gray-300 rounded-xl p-4 text-sm text-gray-500 hover:border-indigo-400 hover:text-indigo-600">
                + {{ __('academic_structure.add_class') }}
            </button>
            <div class="flex justify-between">
                <button wire:click="prevStep" class="min-h-[44px] px-6 py-2 text-gray-700 bg-gray-100 rounded-lg text-sm font-medium hover:bg-gray-200">
                    ← {{ __('academic_structure.previous') }}
                </button>
                <button wire:click="nextStep" class="min-h-[44px] px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                    {{ __('academic_structure.next') }} →
                </button>
            </div>
        </div>
    @endif

    {{-- Step 3: Assign Subjects --}}
    @if($step === 3)
        <div class="space-y-4">
            @foreach($classes as $ci => $classData)
                @if(!empty($classData['name']))
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                        <h3 class="font-semibold text-gray-900 mb-3">{{ $classData['name'] }}</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allSubjects as $subject)
                                <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm cursor-pointer {{ in_array($subject->name, $classData['subjects'] ?? []) ? 'bg-indigo-50 border-indigo-300 text-indigo-700' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                                    <input type="checkbox" wire:model="classes.{{ $ci }}.subjects" value="{{ $subject->name }}" class="hidden">
                                    {{ $subject->name_bangla ?? $subject->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
            <div class="flex justify-between">
                <button wire:click="prevStep" class="min-h-[44px] px-6 py-2 text-gray-700 bg-gray-100 rounded-lg text-sm font-medium hover:bg-gray-200">
                    ← {{ __('academic_structure.previous') }}
                </button>
                <button wire:click="saveAll" class="min-h-[44px] px-6 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                    {{ __('academic_structure.finish') }} ✓
                </button>
            </div>
        </div>
    @endif
</div>
