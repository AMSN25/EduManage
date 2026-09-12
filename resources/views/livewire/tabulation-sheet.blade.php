<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('exams.tabulation_sheet') }}</h1>
    </div>

    @if(session('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 rounded-lg text-green-800">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
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

            @if($classes->count())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('exams.select_class') }}</label>
                    <select wire:model="classId" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('exams.choose_class') }}</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button wire:click="generateSingle" wire:loading.attr="disabled" wire:target="generateSingle"
                class="min-h-[44px] px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                <span wire:loading.remove wire:target="generateSingle">{{ __('exams.generate_tabulation') }}</span>
                <span wire:loading wire:target="generateSingle">{{ __('exams.generating') }}...</span>
            </button>

            @if($examId)
                <button wire:click="generateBulk" wire:loading.attr="disabled" wire:target="generateBulk"
                    class="min-h-[44px] px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="generateBulk">{{ __('exams.print_all_classes') }}</span>
                    <span wire:loading wire:target="generateBulk">{{ __('exams.generating') }}...</span>
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

        @if($pdfReady && $pdfFilename)
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                {{ __('exams.pdf_generated') }}: {{ $pdfFilename }}
            </div>
        @endif
    </div>
</div>
