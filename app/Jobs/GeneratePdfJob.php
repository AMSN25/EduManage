<?php

namespace App\Jobs;

use App\Models\Exam;
use App\Models\Institute;
use App\Services\PdfGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * @param string $type admit-cards|seat-plan|seat-slips|tabulation|tabulation-bulk|marksheet|marksheet-bulk
     * @param int $examId
     * @param int|null $classId
     * @param int|null $roomId (used as resultId for marksheet type)
     * @param int $instituteId
     * @param string $filename
     */
    public function __construct(
        public string $type,
        public int $examId,
        public ?int $classId,
        public ?int $roomId,
        public int $instituteId,
        public string $filename,
    ) {}

    public function handle(PdfGenerationService $pdfService): void
    {
        $exam = Exam::findOrFail($this->examId);
        $institute = Institute::findOrFail($this->instituteId);

        $content = match ($this->type) {
            'admit-cards' => $pdfService->generateBulkAdmitCards($this->classId ?? 0, $exam, $institute),
            'seat-plan' => $pdfService->generateRoomSeatPlan(
                \App\Models\ExamRoom::findOrFail($this->roomId),
                $exam,
                $institute
            ),
            'seat-slips' => $pdfService->generateSeatSlips(
                \App\Models\ExamRoom::findOrFail($this->roomId),
                $exam,
                $institute
            ),
            'tabulation' => $pdfService->generateTabulationSheet($this->classId ?? 0, $exam, $institute),
            'tabulation-bulk' => $pdfService->generateBulkTabulationSheets($exam, $institute),
            'marksheet' => $pdfService->generateMarksheet(
                \App\Models\Result::findOrFail($this->roomId),
                $institute
            ),
            'marksheet-bulk' => $pdfService->generateBulkMarksheets($this->classId ?? 0, $exam, $institute),
            default => throw new \InvalidArgumentException("Unknown PDF type: {$this->type}"),
        };

        $path = "pdfs/{$this->filename}";
        Storage::disk('local')->put($path, $content);

        // Store metadata for polling
        Storage::disk('local')->put("pdfs/{$this->filename}.meta", json_encode([
            'type' => $this->type,
            'exam_id' => $this->examId,
            'class_id' => $this->classId,
            'room_id' => $this->roomId,
            'institute_id' => $this->instituteId,
            'filename' => $this->filename,
            'path' => $path,
            'completed_at' => now()->toIso8601String(),
        ]));
    }

    public function failed(\Throwable $exception): void
    {
        Storage::disk('local')->put("pdfs/{$this->filename}.failed", json_encode([
            'error' => $exception->getMessage(),
            'failed_at' => now()->toIso8601String(),
        ]));
    }
}
