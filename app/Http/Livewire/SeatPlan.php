<?php

namespace App\Http\Livewire;

use App\Jobs\GeneratePdfJob;
use App\Models\Exam;
use App\Models\ExamRoom;
use App\Services\SeatAllocationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class SeatPlan extends Component
{
    public $examId = '';
    public $exams = [];
    public $rooms = [];
    public $roomName = '';
    public $roomCapacity = 30;
    public $roomRows = 5;
    public $roomColumns = 6;
    public $showAddRoom = false;
    public $allocated = false;
    public $allocationError = '';
    public $strategy = 'sequential';
    public $generating = false;
    public $pdfReady = false;
    public $pdfPath = '';
    public $pdfFilename = '';
    public $selectedRoomId = '';

    public function mount(): void
    {
        $instituteId = Auth::user()->institute_id;
        $this->exams = Exam::where('institute_id', $instituteId)->get();
        $this->strategy = config('exams.seat_allocation.default_strategy', 'sequential');
    }

    public function updatedExamId(): void
    {
        $this->loadRooms();
        $this->allocated = false;
        $this->allocationError = '';
    }

    public function loadRooms(): void
    {
        if ($this->examId) {
            $this->rooms = ExamRoom::where('institute_id', Auth::user()->institute_id)
                ->where('exam_id', $this->examId)
                ->with('assignments')
                ->get();
        } else {
            $this->rooms = [];
        }
    }

    public function toggleAddRoom(): void
    {
        $this->showAddRoom = !$this->showAddRoom;
    }

    public function addRoom(): void
    {
        $this->validate([
            'roomName' => 'required|string|max:255',
            'roomCapacity' => 'required|integer|min:1',
            'roomRows' => 'required|integer|min:1',
            'roomColumns' => 'required|integer|min:1',
        ]);

        ExamRoom::create([
            'institute_id' => Auth::user()->institute_id,
            'exam_id' => $this->examId,
            'room_name' => $this->roomName,
            'capacity' => $this->roomCapacity,
            'rows' => $this->roomRows,
            'columns' => $this->roomColumns,
        ]);

        $this->loadRooms();
        $this->showAddRoom = false;
        $this->roomName = '';
        $this->roomCapacity = 30;
        $this->roomRows = 5;
        $this->roomColumns = 6;
    }

    public function allocate(): void
    {
        $this->allocationError = '';

        if (empty($this->rooms)) {
            $this->allocationError = __('exams.no_rooms_configured');
            return;
        }

        $service = new SeatAllocationService();
        $exam = Exam::find($this->examId);

        try {
            $result = $service->allocate(
                $exam,
                $this->rooms->pluck('id')->toArray(),
                Auth::user()->institute_id,
                $this->strategy
            );

            $this->allocated = true;
            $this->loadRooms();
            session()->flash('message', $result['message']);
        } catch (\RuntimeException $e) {
            $this->allocationError = $e->getMessage();
        }
    }

    public function generateSeatPlanPdf(): void
    {
        if (!$this->selectedRoomId) {
            return;
        }

        $this->generating = true;
        $this->pdfReady = false;

        $instituteId = Auth::user()->institute_id;
        $threshold = config('exams.pdf.queue_threshold', 50);
        $room = ExamRoom::findOrFail($this->selectedRoomId);
        $assignedCount = $room->assignedCount();

        if ($assignedCount > $threshold) {
            $filename = 'seat_plan_room_' . $this->selectedRoomId . '_' . time() . '.pdf';

            GeneratePdfJob::dispatch(
                'seat-plan',
                $this->examId,
                null,
                $this->selectedRoomId,
                $instituteId,
                $filename
            );

            $this->pdfFilename = $filename;
            session()->flash('message', __('exams.pdf_queued'));
        } else {
            $pdfService = new \App\Services\PdfGenerationService();
            $institute = \App\Models\Institute::findOrFail($instituteId);
            $exam = Exam::find($this->examId);

            $pdfContent = $pdfService->generateRoomSeatPlan($room, $exam, $institute);
            $filename = 'seat_plan_room_' . $this->selectedRoomId . '_' . time() . '.pdf';
            $pdfService->saveToStorage($pdfContent, $filename);

            $this->pdfPath = Storage::disk('local')->url("pdfs/{$filename}");
            $this->pdfFilename = $filename;
            $this->pdfReady = true;
        }

        $this->generating = false;
    }

    public function generateSeatSlipsPdf(): void
    {
        if (!$this->selectedRoomId) {
            return;
        }

        $this->generating = true;
        $this->pdfReady = false;

        $instituteId = Auth::user()->institute_id;
        $room = ExamRoom::findOrFail($this->selectedRoomId);

        $pdfService = new \App\Services\PdfGenerationService();
        $institute = \App\Models\Institute::findOrFail($instituteId);
        $exam = Exam::find($this->examId);

        $pdfContent = $pdfService->generateSeatSlips($room, $exam, $institute);
        $filename = 'seat_slips_room_' . $this->selectedRoomId . '_' . time() . '.pdf';
        $pdfService->saveToStorage($pdfContent, $filename);

        $this->pdfPath = Storage::disk('local')->url("pdfs/{$filename}");
        $this->pdfFilename = $filename;
        $this->pdfReady = true;
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
        return view('livewire.seat-plan');
    }
}
