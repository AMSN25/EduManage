<div>
    @if($showModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
                <div class="relative bg-white rounded-lg shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-3xl max-h-[90vh] overflow-y-auto">
                    <div class="bg-white rounded-lg px-4 pt-5 pb-4 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            {{ $studentId ? __('student.edit_student') : __('student.create_student') }}
                        </h3>

                        {{-- Tabs --}}
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="flex space-x-8 overflow-x-auto">
                                @foreach(['basic' => __('student.basic_info'), 'guardian' => __('student.guardian'), 'academic' => __('student.academic'), 'address' => __('student.address'), 'photo' => __('student.photo')] as $key => $label)
                                    <button wire:click="setActiveTab('{{ $key }}')"
                                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === $key ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </nav>
                        </div>

                        <form wire:submit.prevent="save">
                            {{-- Basic Info Tab --}}
                            @if($activeTab === 'basic')
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.name') }} *</label>
                                        <input type="text" wire:model="name" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.name_bangla') }}</label>
                                        <input type="text" wire:model="name_bangla" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.gender') }} *</label>
                                        <select wire:model="gender" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="male">{{ __('student.male') }}</option>
                                            <option value="female">{{ __('student.female') }}</option>
                                            <option value="other">{{ __('student.other') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.dob') }}</label>
                                        <input type="date" wire:model="dob" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.blood_group') }}</label>
                                        <select wire:model="blood_group" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">{{ __('student.select') }}</option>
                                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                                <option value="{{ $bg }}">{{ $bg }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.religion') }}</label>
                                        <input type="text" wire:model="religion" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.phone') }}</label>
                                        <input type="text" wire:model="phone" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.email') }}</label>
                                        <input type="email" wire:model="email" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                            @endif

                            {{-- Guardian Tab --}}
                            @if($activeTab === 'guardian')
                                <div class="space-y-6">
                                    {{-- Father --}}
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <h4 class="font-medium text-gray-900 mb-3">{{ __('student.father') }}</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.name') }}</label>
                                                <input type="text" wire:model="father_name" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.phone') }}</label>
                                                <input type="text" wire:model="father_phone" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.occupation') }}</label>
                                                <input type="text" wire:model="father_occupation" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.nid') }}</label>
                                                <input type="text" wire:model="father_nid" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Mother --}}
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <h4 class="font-medium text-gray-900 mb-3">{{ __('student.mother') }}</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.name') }}</label>
                                                <input type="text" wire:model="mother_name" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.phone') }}</label>
                                                <input type="text" wire:model="mother_phone" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.occupation') }}</label>
                                                <input type="text" wire:model="mother_occupation" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.nid') }}</label>
                                                <input type="text" wire:model="mother_nid" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Guardian --}}
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <h4 class="font-medium text-gray-900 mb-3">{{ __('student.guardian') }}</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.name') }}</label>
                                                <input type="text" wire:model="guardian_name" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.phone') }}</label>
                                                <input type="text" wire:model="guardian_phone" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.occupation') }}</label>
                                                <input type="text" wire:model="guardian_occupation" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.nid') }}</label>
                                                <input type="text" wire:model="guardian_nid" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Academic Tab --}}
                            @if($activeTab === 'academic')
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.class') }} *</label>
                                        <select wire:model="class_id" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            <option value="">{{ __('student.select_class') }}</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('class_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.section') }} *</label>
                                        <select wire:model="section_id" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            <option value="">{{ __('student.select_section') }}</option>
                                            @foreach($sections as $section)
                                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('section_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.group') }}</label>
                                        <select wire:model="group_id" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            <option value="">{{ __('student.select_group') }}</option>
                                            @foreach($groups as $group)
                                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.academic_year') }} *</label>
                                        <select wire:model="academic_year_id" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('academic_year_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.admission_date') }} *</label>
                                        <input type="date" wire:model="admission_date" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                        @error('admission_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.admission_no') }}</label>
                                        <input type="text" wire:model="admission_no" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.roll') }}</label>
                                        <input type="number" wire:model="roll" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('student.status') }} *</label>
                                        <select wire:model="status" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            <option value="active">{{ __('student.active') }}</option>
                                            <option value="inactive">{{ __('student.inactive') }}</option>
                                            <option value="transferred">{{ __('student.transferred') }}</option>
                                            <option value="graduated">{{ __('student.graduated') }}</option>
                                        </select>
                                    </div>
                                </div>
                            @endif

                            {{-- Address Tab --}}
                            @if($activeTab === 'address')
                                <div class="space-y-6">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <h4 class="font-medium text-gray-900 mb-3">{{ __('student.present_address') }}</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.village') }}</label>
                                                <input type="text" wire:model="present_village" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.post_office') }}</label>
                                                <input type="text" wire:model="present_post_office" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.upazila') }}</label>
                                                <input type="text" wire:model="present_upazila" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.district') }}</label>
                                                <input type="text" wire:model="present_district" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.division') }}</label>
                                                <input type="text" wire:model="present_division" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center">
                                        <input type="checkbox" wire:model="same_as_present" id="same_address" class="rounded border-gray-300 text-indigo-600">
                                        <label for="same_address" class="ml-2 text-sm text-gray-700">{{ __('student.same_as_present') }}</label>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <h4 class="font-medium text-gray-900 mb-3">{{ __('student.permanent_address') }}</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.village') }}</label>
                                                <input type="text" wire:model="permanent_village" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.post_office') }}</label>
                                                <input type="text" wire:model="permanent_post_office" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.upazila') }}</label>
                                                <input type="text" wire:model="permanent_upazila" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.district') }}</label>
                                                <input type="text" wire:model="permanent_district" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">{{ __('student.division') }}</label>
                                                <input type="text" wire:model="permanent_division" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Photo Tab --}}
                            @if($activeTab === 'photo')
                                <div class="text-center py-8">
                                    @if($photo)
                                        <img src="{{ $photo->temporaryUrl() }}" class="w-32 h-32 rounded-full mx-auto mb-4 object-cover">
                                    @elseif($studentId)
                                        <p class="text-sm text-gray-500 mb-4">{{ __('student.current_photo') }}</p>
                                    @else
                                        <div class="w-32 h-32 rounded-full bg-gray-200 mx-auto mb-4 flex items-center justify-center">
                                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <input type="file" wire:model="photo" accept="image/*"
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    @error('photo') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                    <p class="mt-2 text-xs text-gray-500">{{ __('student.photo_help') }}</p>
                                </div>
                            @endif

                            <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                                <button type="button" wire:click="closeModal"
                                        class="min-h-[44px] px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    {{ __('student.cancel') }}
                                </button>
                                <button type="submit"
                                        class="min-h-[44px] px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                                    {{ __('student.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
