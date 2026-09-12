<?php

namespace App\Http\Livewire;

use App\Jobs\GeneratePdfJob;
use App\Models\ClassModel;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class TabulationSheet extends Component
{
    public $examId = '';
    public $classId = '';
    public $exams = [];
    public $classes = [];
    public $generating = false;
    public $pdfReady = false;
    public $pdfPath = '';
    public $pdfFilename = '';

    public function mount(): void
    {
        $instituteId = Auth::user()->institute_id;
        $this->exams = Exam::where('institute_id', $instituteId)->get();
    }

    public function updatedExamId(): void
    {
        $instituteId = Auth::user()->institute_id;
        if ($this->examId) {
            $classIds = Exam::find($this->examId)?->subjects->pluck('class_id')->unique() ?? [];
            $this->classes = ClassModel::whereIn('id', $classIds)->get();
        } else {
            $this->classes = [];
        }
        $this->classId = '';
        $this->pdfReady = false;
    }

    public function generateSingle(): void
    {
        $this->validate([
            'examId' => 'required|exists:exams,id',
            'classId' => 'required|exists:classes,id',
        ]);

        $this->generating = true;
        $this->pdfReady = false;

        $instituteId = Auth::user()->institute_id;
        $threshold = config('exams.pdf.queue_threshold', 50);

        $studentCount = \App\Models\Student::where('institute_id', $instituteId)
            ->where('class_id', $this->classId)
            ->where('status', 'active')
            ->count();

        if ($studentCount > $threshold) {
            $filename = 'tabulation_exam_' . $this->examId . '_class_' . $this->classId . '_' . time() . '.pdf';

            GeneratePdfJob::dispatch(
                'tabulation',
                $this->examId,
                $this->classId,
                null,
                $instituteId,
                $filename
            );

            $this->pdfFilename = $filename;
            session()->flash('message', __('exams.pdf_queued'));
        } else {
            $pdfService = new \App\Services\PdfGenerationService();
            $institute = \App\Models\Institute::findOrFail($instituteId);
            $exam = Exam::find($this->examId);

            $pdfContent = $pdfService->generateTabulationSheet($this->classId, $exam, $institute);
            $filename = 'tabulation_exam_' . $this->examId . '_class_' . $this->classId . '_' . time() . '.pdf';
            $pdfService->saveToStorage($pdfContent, $filename);

            $this->pdfPath = Storage::disk('local')->url("pdfs/{$filename}");
            $this->pdfFilename = $filename;
            $this->pdfReady = true;
        }

        $this->generating = false;
    }

    public function generateBulk(): void
    {
        $this->validate([
            'examId' => 'required|exists:exams,id',
        ]);

        $this->generating = true;
        $this->pdfReady = false;

        $instituteId = Auth::user()->institute_id;
        $exam = Exam::find($this->examId);
        $totalStudents = \App\Models\Student::where('institute_id', $instituteId)
            ->whereIn('class_id', $exam->subjects->pluck('class_id')->unique())
            ->where('status', 'active')
            ->count();

        $threshold = config('exams.pdf.queue_threshold', 50);

        if ($totalStudents > $threshold) {
            $filename = 'tabulation_bulk_exam_' . $this->examId . '_' . time() . '.pdf';

            GeneratePdfJob::dispatch(
                'tabulation-bulk',
                $this->examId,
                null,
                null,
                $instituteId,
                $filename
            );

            $this->pdfFilename = $filename;
            session()->flash('message', __('exams.pdf_queued'));
        } else {
            $pdfService = new \App\Services\PdfGenerationService();
            $institute = \App\Models\Institute::findOrFail($instituteId);

            $pdfContent = $pdfService->generateBulkTabulationSheets($exam, $institute);
            $filename = 'tabulation_bulk_exam_' . $this->examId . '_' . time() . '.pdf';
            $pdfService->saveToStorage($pdfContent, $filename);

            $this->pdfPath = Storage::disk('local')->url("pdfs/{$filename}");
            $this->pdfFilename = $filename;
            $this->pdfReady = true;
        }

        $this->generating = false;
    }

    public function downloadPdf(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Storage::disk('local')->download("pdfs/{$this->pdfFilename}");
    }

    public function checkPdfStatus(): void
    {
        if ($this->pdfFilename && Storage::disk('local')->exists("pdfs/{$this->pdfFilename}")) {
            $this->pdfReady = true;
            $this->pdfPath = Storage::disk('local')->url("pdfs/{$this->pdfFilename}");
        }
    }

    public function render()
    {
        return view('livewire.tabulation-sheet');
    }
}
