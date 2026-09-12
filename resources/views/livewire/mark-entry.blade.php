<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('exams.mark_entry') }}</h1>
    </div>

    {{-- Offline Status Banner --}}
    <div id="markentry-offline-status" class="hidden mb-4 bg-amber-50 border-2 border-amber-400 rounded-lg p-4">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.242 2.829a5 5 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3"/></svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-amber-800">{{ __('pwa.offline_mode') }}</p>
                <p class="text-xs text-amber-600">{{ __('pwa.offline_marks_hint') }}</p>
            </div>
        </div>
    </div>

    {{-- Queued Changes Banner --}}
    <div id="markentry-queued-banner" class="hidden mb-4 bg-orange-50 border-2 border-orange-400 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-orange-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-semibold text-orange-800">
                    <span id="markentry-queued-count">0</span> {{ __('pwa.unsynced_changes') }}
                </p>
            </div>
            <button onclick="EduManageOffline.sync()" class="text-xs bg-orange-600 text-white px-3 py-1.5 rounded-lg font-medium hover:bg-orange-700">
                {{ __('pwa.sync_now') }}
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('exams.class') }}</label>
                <select wire:model="classId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('exams.select_class') }}</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('exams.subject') }}</label>
                <select wire:model="examSubjectId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('exams.select_subject') }}</option>
                    @foreach($examSubjects as $es)
                        <option value="{{ $es->id }}">{{ $es->subject?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                @if($status === 'locked')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        {{ __('exams.status_locked') }}
                    </span>
                @elseif($status === 'submitted')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        {{ __('exams.status_submitted') }}
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        {{ __('exams.status_draft') }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Marks Table --}}
    @if($examSubjectId && $students->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-12">{{ __('exams.roll') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('exams.student') }}</th>
                            @if($examSubject?->cq_marks)
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase w-20">{{ __('exams.cq') }} ({{$examSubject->cq_marks}})</th>
                            @endif
                            @if($examSubject?->mcq_marks)
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase w-20">{{ __('exams.mcq') }} ({{$examSubject->mcq_marks}})</th>
                            @endif
                            @if($examSubject?->practical_marks)
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase w-20">{{ __('exams.practical') }} ({{$examSubject->practical_marks}})</th>
                            @endif
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase w-24">{{ __('exams.obtained') }} ({{$examSubject?->full_marks}})</th>
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase w-16">{{ __('exams.absent_short') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($students as $student)
                            <tr class="{{ $marks[$student->id]['is_absent'] ?? false ? 'bg-red-50' : '' }}">
                                <td class="px-4 py-2 text-gray-900">{{ $student->roll }}</td>
                                <td class="px-4 py-2 font-medium text-gray-900">{{ $student->name }}</td>
                                @if($examSubject?->cq_marks)
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" max="{{ $examSubject->cq_marks }}"
                                            wire:model="marks.{{ $student->id }}.cq_obtained"
                                            {{ $marks[$student->id]['is_absent'] ?? false ? 'disabled' : '' }}
                                            class="w-full text-center rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </td>
                                @endif
                                @if($examSubject?->mcq_marks)
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" max="{{ $examSubject->mcq_marks }}"
                                            wire:model="marks.{{ $student->id }}.mcq_obtained"
                                            {{ $marks[$student->id]['is_absent'] ?? false ? 'disabled' : '' }}
                                            class="w-full text-center rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </td>
                                @endif
                                @if($examSubject?->practical_marks)
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" min="0" max="{{ $examSubject->practical_marks }}"
                                            wire:model="marks.{{ $student->id }}.practical_obtained"
                                            {{ $marks[$student->id]['is_absent'] ?? false ? 'disabled' : '' }}
                                            class="w-full text-center rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </td>
                                @endif
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0" max="{{ $examSubject?->full_marks }}"
                                        wire:model="marks.{{ $student->id }}.obtained_marks"
                                        {{ $marks[$student->id]['is_absent'] ?? false ? 'disabled' : '' }}
                                        class="w-full text-center rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-medium">
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <input type="checkbox" wire:model="marks.{{ $student->id }}.is_absent"
                                        class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Actions --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-3">
                <p class="text-sm text-gray-500">{{ $students->count() }} {{ __('exams.students') }}</p>
                <div class="flex gap-3">
                    @if($status !== 'locked')
                        <button wire:click="saveDraft" id="markentry-save-draft-btn"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 min-h-[44px]">
                            {{ __('exams.save_draft') }}
                        </button>
                        <button wire:click="submit" id="markentry-submit-btn"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 min-h-[44px]">
                            {{ __('exams.submit') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @elseif($examSubjectId)
        <div class="text-center py-8 text-gray-500">{{ __('exams.no_students') }}</div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isOffline = () => !navigator.onLine;
            const offlineStatus = document.getElementById('markentry-offline-status');
            const queuedBanner = document.getElementById('markentry-queued-banner');
            const queuedCount = document.getElementById('markentry-queued-count');

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

            // Override save buttons to queue when offline
            ['markentry-save-draft-btn', 'markentry-submit-btn'].forEach(function (btnId) {
                const btn = document.getElementById(btnId);
                if (btn && window.EduManageOffline) {
                    btn.addEventListener('click', function (e) {
                        if (isOffline()) {
                            e.preventDefault();
                            e.stopPropagation();

                            const component = Livewire.find(btn.closest('[wire\\:id]').getAttribute('wire\\:id'));
                            if (component) {
                                window.EduManageOffline.queueMarkEntry({
                                    method: btnId === 'markentry-submit-btn' ? 'submit' : 'saveDraft',
                                    params: [],
                                    updates: component.serverMemo.data,
                                });
                                updateUI();
                            }
                            return false;
                        }
                    });
                }
            });

            window.addEventListener('online', updateUI);
            window.addEventListener('offline', updateUI);
            updateUI();
        });
    </script>
    @endpush
</div>
