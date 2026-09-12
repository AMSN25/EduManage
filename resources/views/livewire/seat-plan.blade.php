<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('exams.seat_plan') }}</h1>
    </div>

    @if(session('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 rounded-lg text-green-800">
            {{ session('message') }}
        </div>
    @endif

    @if($allocationError)
        <div class="mb-4 p-4 bg-red-100 border border-red-300 rounded-lg text-red-800">
            {{ $allocationError }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('exams.select_exam') }}</label>
                <select wire:model="examId" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('exams.choose_exam') }}</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('exams.allocation_strategy') }}</label>
                <select wire:model="strategy" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="sequential">{{ __('exams.sequential') }}</option>
                    <option value="shuffled">{{ __('exams.shuffled') }}</option>
                </select>
            </div>
        </div>

        @if($examId)
            <div class="mt-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">{{ __('exams.rooms') }}</h2>
                    <button wire:click="toggleAddRoom"
                        class="min-h-[44px] px-3 py-1.5 text-sm font-medium text-indigo-700 bg-indigo-100 rounded-lg hover:bg-indigo-200">
                        {{ $showAddRoom ? __('exams.cancel') : __('exams.add_room') }}
                    </button>
                </div>

                @if($showAddRoom)
                    <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('exams.room_name') }}</label>
                                <input type="text" wire:model="roomName" class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('exams.capacity') }}</label>
                                <input type="number" wire:model="roomCapacity" class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('exams.rows') }}</label>
                                <input type="number" wire:model="roomRows" class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('exams.columns') }}</label>
                                <input type="number" wire:model="roomColumns" class="w-full border-gray-300 rounded-lg shadow-sm text-sm">
                            </div>
                        </div>
                        <button wire:click="addRoom" class="mt-3 min-h-[44px] px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            {{ __('exams.save_room') }}
                        </button>
                    </div>
                @endif

                @if($rooms->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('exams.room_name') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('exams.capacity') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('exams.assigned') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('exams.available') }}</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('exams.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($rooms as $room)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $room->room_name }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $room->capacity }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $room->assignedCount() }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $room->availableSeats() }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <select wire:model="selectedRoomId" class="text-sm border-gray-300 rounded-lg">
                                                <option value="">{{ __('exams.select_room') }}</option>
                                                <option value="{{ $room->id }}">{{ $room->room_name }}</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <button wire:click="allocate" wire:loading.attr="disabled"
                            class="min-h-[44px] px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">
                            {{ __('exams.allocate_seats') }}
                        </button>

                        @if($selectedRoomId)
                            <button wire:click="generateSeatPlanPdf" wire:loading.attr="disabled"
                                class="min-h-[44px] px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                <span wire:loading.remove wire:target="generateSeatPlanPdf">{{ __('exams.generate_seat_plan') }}</span>
                                <span wire:loading wire:target="generateSeatPlanPdf">{{ __('exams.generating') }}...</span>
                            </button>

                            <button wire:click="generateSeatSlipsPdf" wire:loading.attr="disabled"
                                class="min-h-[44px] px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50">
                                {{ __('exams.generate_seat_slips') }}
                            </button>
                        @endif

                        @if($pdfReady)
                            <a href="{{ $pdfPath }}" target="_blank"
                                class="min-h-[44px] inline-flex items-center px-4 py-2 text-sm font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200">
                                {{ __('exams.view_pdf') }}
                            </a>
                            <button wire:click="downloadPdf"
                                class="min-h-[44px] px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200">
                                {{ __('exams.download_pdf') }}
                            </button>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500 text-sm">{{ __('exams.no_rooms_configured') }}</p>
                @endif
            </div>
        @endif
    </div>
</div>
