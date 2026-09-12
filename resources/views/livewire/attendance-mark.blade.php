<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('attendance.mark_attendance') }}</h1>

    {{-- Offline Status Banner --}}
    <div id="attendance-offline-status" class="hidden mb-4 bg-amber-50 border-2 border-amber-400 rounded-lg p-4">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.242 2.829a5 5 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3"/></svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-amber-800">{{ __('pwa.offline_mode') }}</p>
                <p class="text-xs text-amber-600">{{ __('pwa.offline_attendance_hint') }}</p>
            </div>
        </div>
    </div>

    {{-- Queued Changes Banner --}}
    <div id="attendance-queued-banner" class="hidden mb-4 bg-orange-50 border-2 border-orange-400 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-orange-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-semibold text-orange-800">
                    <span id="attendance-queued-count">0</span> {{ __('pwa.unsynced_changes') }}
                </p>
            </div>
            <button onclick="EduManageOffline.sync()" class="text-xs bg-orange-600 text-white px-3 py-1.5 rounded-lg font-medium hover:bg-orange-700">
                {{ __('pwa.sync_now') }}
            </button>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Selection Form --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.class') }} *</label>
                <select wire:model.live="classId"
                        class="w-full border border-gray-300 rounded-lg px-3 py-3 text-base focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('attendance.select_class') }}</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.section') }} *</label>
                <select wire:model.live="sectionId"
                        class="w-full border border-gray-300 rounded-lg px-3 py-3 text-base focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('attendance.select_section') }}</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('attendance.date') }}</label>
                <input type="date" wire:model.live="date" max="{{ now()->format('Y-m-d') }}" min="{{ $minDate }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-3 text-base focus:ring-indigo-500 focus:border-indigo-500">
                <p class="mt-1 text-xs text-gray-500">{{ __('attendance.date_hint') }}</p>
            </div>
        </div>
    </div>

    {{-- Attendance Form --}}
    @if($showForm && $students->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            {{-- Quick Actions --}}
            <div class="p-4 border-b border-gray-200 flex flex-wrap gap-2">
                <button wire:click="markAllPresent"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    {{ __('attendance.mark_all_present') }}
                </button>
                <button wire:click="markAllAbsent"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    {{ __('attendance.mark_all_absent') }}
                </button>
                <span class="ml-auto text-sm text-gray-500 self-center">
                    {{ __('attendance.students_count', ['count' => $students->count()]) }}
                </span>
            </div>

            {{-- Student List --}}
            <div class="divide-y divide-gray-200">
                @foreach($students as $student)
                    <div class="p-4 flex items-center gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="text-base font-medium text-gray-900 truncate">{{ $student->name }}</div>
                            @if($student->roll)
                                <div class="text-sm text-gray-500">{{ __('attendance.roll') }}: {{ $student->roll }}</div>
                            @endif
                        </div>

                        {{-- Status Buttons (large tap targets >= 44px) --}}
                        <div class="flex gap-1.5">
                            <button wire:click="$set('records.{{ $student->id }}', 'present')"
                                    class="w-12 h-12 min-w-[44px] min-h-[44px] rounded-lg text-xs font-bold transition {{ ($records[$student->id] ?? '') === 'present' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-green-100' }}">
                                {{ __('attendance.present_short') }}
                            </button>
                            <button wire:click="$set('records.{{ $student->id }}', 'absent')"
                                    class="w-12 h-12 min-w-[44px] min-h-[44px] rounded-lg text-xs font-bold transition {{ ($records[$student->id] ?? '') === 'absent' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-red-100' }}">
                                {{ __('attendance.absent_short') }}
                            </button>
                            <button wire:click="$set('records.{{ $student->id }}', 'late')"
                                    class="w-12 h-12 min-w-[44px] min-h-[44px] rounded-lg text-xs font-bold transition {{ ($records[$student->id] ?? '') === 'late' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-yellow-100' }}">
                                {{ __('attendance.late_short') }}
                            </button>
                            <button wire:click="$set('records.{{ $student->id }}', 'leave')"
                                    class="w-12 h-12 min-w-[44px] min-h-[44px] rounded-lg text-xs font-bold transition {{ ($records[$student->id] ?? '') === 'leave' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-blue-100' }}">
                                {{ __('attendance.leave_short') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Save Button (sticky on mobile) --}}
            <div class="p-4 border-t border-gray-200 sm:text-right sticky bottom-0 bg-white">
                <button wire:click="save" wire:loading.attr="disabled"
                        id="attendance-save-btn"
                        class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-lg text-base font-bold transition min-h-[48px]">
                    {{ __('attendance.save') }}
                </button>
            </div>
        </div>
    @elseif($classId && $sectionId)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center text-gray-500">
            {{ __('attendance.no_students_found') }}
        </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isOffline = () => !navigator.onLine;
            const offlineStatus = document.getElementById('attendance-offline-status');
            const queuedBanner = document.getElementById('attendance-queued-banner');
            const queuedCount = document.getElementById('attendance-queued-count');
            const saveBtn = document.getElementById('attendance-save-btn');

            function updateUI() {
                if (isOffline()) {
                    offlineStatus.classList.remove('hidden');
                } else {
                    offlineStatus.classList.add('hidden');
                }

                const pending = window.EduManageOffline ? window.EduManageOffline.getPendingCount() : 0;
                if (pending > 0) {
                    queuedBanner.classList.remove('hidden');
                    queuedCount.textContent = pending;
                } else {
                    queuedBanner.classList.add('hidden');
                }
            }

            // Override save to queue when offline
            if (saveBtn && window.EduManageOffline) {
                saveBtn.addEventListener('click', function (e) {
                    if (isOffline()) {
                        e.preventDefault();
                        e.stopPropagation();

                        // Get Livewire component data
                        const component = Livewire.find(saveBtn.closest('[wire\\:id]').getAttribute('wire\\:id'));
                        if (component) {
                            window.EduManageOffline.queueAttendance({
                                method: 'save',
                                params: [],
                                updates: component.serverMemo.data,
                            });
                            updateUI();
                        }
                        return false;
                    }
                });
            }

            window.addEventListener('online', updateUI);
            window.addEventListener('offline', updateUI);
            updateUI();
        });
    </script>
    @endpush
</div>
