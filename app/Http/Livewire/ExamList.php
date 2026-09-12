<?php

namespace App\Http\Livewire;

use App\Models\AuditLog;
use App\Models\Exam;
use App\Services\ResultCalculationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExamList extends Component
{
    public function publishResults(int $examId): void
    {
        $exam = Exam::findOrFail($examId);

        if (!Auth::user()->can('publishResults', $exam)) {
            abort(403);
        }

        $service = new ResultCalculationService();

        try {
            $service->calculateForExam($exam);
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        if (!$service->canPublish($exam)) {
            session()->flash('error', __('exams.cannot_publish_incomplete'));
            return;
        }

        $exam->update(['status' => 'published']);
        $service->publishResults($exam);

        AuditLog::log(
            $exam->institute_id,
            Auth::id(),
            'exam.results_published',
            $exam,
            ['exam_name' => $exam->name]
        );

        session()->flash('success', __('exams.results_published'));
    }

    public function unpublishResults(int $examId): void
    {
        $exam = Exam::findOrFail($examId);

        if (!Auth::user()->can('publishResults', $exam)) {
            abort(403);
        }

        $service = new ResultCalculationService();
        $exam->update(['status' => 'completed']);
        $service->unpublishResults($exam);

        AuditLog::log(
            $exam->institute_id,
            Auth::id(),
            'exam.results_unpublished',
            $exam,
            ['exam_name' => $exam->name]
        );

        session()->flash('success', __('exams.results_unpublished'));
    }

    public function render()
    {
        $exams = Exam::where('institute_id', Auth::user()->institute_id)
            ->with('academicYear')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.exam-list', [
            'exams' => $exams,
        ]);
    }
}
