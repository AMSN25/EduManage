<?php

namespace App\Http\Livewire;

use App\Jobs\GeneratePdfJob;
use App\Models\ClassModel;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class AdmitCards extends Component
{
    public $examId = '';
    public $selectedClasses = [];
    public $exams = [];
    public $classes = [];
    public $generating = false;
    public $pdfReady = false;
    public $pdfPath = '';
    public $pdfFilename = '';

    public function mount(): void
    {
        $instituteId = Auth::user()->institute_id;
        $this->exams = Exam::where('institute_id', $instituteId)
            ->with('subjects')
            ->orderByDesc('created_at')
            ->get();
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
        $this->selectedClasses = [];
        $this->pdfReady = false;
    }

    public function generate(): void
    {
        $this->validate([
            'examId' => 'required|exists:exams,id',
            'selectedClasses' => 'required|array|min:1',
        ]);

        $this->generating = true;
        $this->pdfReady = false;

        $instituteId = Auth::user()->institute_id;
        $exam = Exam::findOrFail($this->examId);
        $threshold = config('exams.pdf.queue_threshold', 50);

        // Count total students
        $totalStudents = \App\Models\Student::where('institute_id', $instituteId)
            ->whereIn('class_id', $this->selectedClasses)
            ->where('status', 'active')
            ->count();

        if ($totalStudents > $threshold) {
            // Queue the job
            $filename = 'admit_cards_exam_' . $this->examId . '_' . time() . '.pdf';

            foreach ($this->selectedClasses as $classId) {
                GeneratePdfJob::dispatch(
                    'admit-cards',
                    $this->examId,
                    $classId,
                    null,
                    $instituteId,
                    $filename
                );
            }

            $this->pdfFilename = $filename;
            session()->flash('message', __('exams.pdf_queued'));
        } else {
            // Generate synchronously
            $pdfService = new \App\Services\PdfGenerationService();
            $institute = \App\Models\Institute::findOrFail($instituteId);

            $pdfContent = $pdfService->generateBulkAdmitCards(
                $this->selectedClasses[0],
                $exam,
                $institute
            );

            $filename = 'admit_cards_exam_' . $this->examId . '_' . time() . '.pdf';
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
        return view('livewire.admit-cards');
    }
}
