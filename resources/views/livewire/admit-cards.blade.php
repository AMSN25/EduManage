<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('exams.admit_cards') }}</h1>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('exams.select_classes') }}</label>
                    <div class="space-y-2 mt-2">
                        @foreach($classes as $class)
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="selectedClasses" value="{{ $class->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">{{ $class->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-6 flex items-center gap-4">
            <button wire:click="generate" wire:loading.attr="disabled"
                class="px-4 py-2 min-h-[44px] text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                <span wire:loading.remove wire:target="generate">{{ __('exams.generate_admit_cards') }}</span>
                <span wire:loading wire:target="generate">{{ __('exams.generating') }}...</span>
            </button>

            @if($pdfReady)
                <a href="{{ $pdfPath }}" target="_blank"
                    class="px-4 py-2 min-h-[44px] text-sm font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200">
                    {{ __('exams.view_pdf') }}
                </a>
                <button wire:click="downloadPdf"
                    class="px-4 py-2 min-h-[44px] text-sm font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200">
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
