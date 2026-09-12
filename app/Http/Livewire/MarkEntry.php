<?php

namespace App\Http\Livewire;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Mark;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MarkEntry extends Component
{
    public ?int $examId = null;
    public ?int $classId = null;
    public ?int $examSubjectId = null;
    public array $marks = []; // student_id => ['obtained_marks' => ..., 'is_absent' => false]
    public string $status = 'draft';

    public function mount(?int $examId = null, ?int $classId = null, ?int $examSubjectId = null): void
    {
        $this->examId = $examId;
        $this->classId = $classId;
        $this->examSubjectId = $examSubjectId;

        if ($examSubjectId) {
            $this->loadMarks();
        }
    }

    public function updatedExamId(): void
    {
        $this->classId = null;
        $this->examSubjectId = null;
        $this->marks = [];
    }

    public function updatedClassId(): void
    {
        $this->examSubjectId = null;
        $this->marks = [];
    }

    public function updatedExamSubjectId(): void
    {
        $this->loadMarks();
    }

    public function loadMarks(): void
    {
        if (!$this->examSubjectId) {
            return;
        }

        $examSubject = ExamSubject::find($this->examSubjectId);
        if (!$examSubject) {
            return;
        }

        $students = Student::where('institute_id', Auth::user()->institute_id)
            ->where('class_id', $examSubject->class_id)
            ->where('section_id', '!=', null)
            ->where('status', 'active')
            ->orderBy('roll')
            ->get();

        $existingMarks = Mark::where('exam_subject_id', $this->examSubjectId)
            ->get()
            ->keyBy('student_id');

        $this->marks = [];
        foreach ($students as $student) {
            $existing = $existingMarks[$student->id] ?? null;
            $this->marks[$student->id] = [
                'obtained_marks' => $existing?->obtained_marks ?? '',
                'cq_obtained' => $existing?->cq_obtained ?? '',
                'mcq_obtained' => $existing?->mcq_obtained ?? '',
                'practical_obtained' => $existing?->practical_obtained ?? '',
                'is_absent' => $existing?->is_absent ?? false,
                'status' => $existing?->status ?? 'draft',
            ];
        }

        $this->status = $existingMarks->first()?->status ?? 'draft';
    }

    public function saveDraft(): void
    {
        $this->saveMarks('draft');
    }

    public function submit(): void
    {
        $this->saveMarks('submitted');
    }

    /**
     * For testing: set marks and save in one action.
     */
    public function setMarksAndSave(array $marksData, string $status = 'draft'): void
    {
        $this->marks = $marksData;
        $this->saveMarks($status);
    }

    private function saveMarks(string $newStatus): void
    {
        if (!$this->examSubjectId) {
            return;
        }

        $examSubject = ExamSubject::find($this->examSubjectId);
        if (!$examSubject) {
            return;
        }

        // Check if marks are already locked
        if ($this->status === 'locked') {
            session()->flash('error', __('exams.marks_locked'));
            return;
        }

        // Check permission
        if (!Auth::user()->can('enterMarksForSubject', $examSubject)) {
            session()->flash('error', __('exams.no_permission'));
            return;
        }

        // Validation: obtained marks cannot exceed full_marks
        foreach ($this->marks as $studentId => $markData) {
            if ($markData['is_absent']) {
                continue;
            }

            $obtained = $markData['obtained_marks'] !== '' ? (float) $markData['obtained_marks'] : 0;

            if ($obtained > $examSubject->full_marks) {
                $student = Student::find($studentId);
                session()->flash('error', __('exams.marks_exceed_full', ['name' => $student->name]));
                return;
            }

            // Validate component marks
            if ($examSubject->cq_marks && ($markData['cq_obtained'] ?? null) !== null && (float) $markData['cq_obtained'] > $examSubject->cq_marks) {
                session()->flash('error', __('exams.cq_marks_exceed', ['name' => $student->name]));
                return;
            }

            if ($examSubject->mcq_marks && ($markData['mcq_obtained'] ?? null) !== null && (float) $markData['mcq_obtained'] > $examSubject->mcq_marks) {
                session()->flash('error', __('exams.mcq_marks_exceed', ['name' => $student->name]));
                return;
            }

            if ($examSubject->practical_marks && ($markData['practical_obtained'] ?? null) !== null && (float) $markData['practical_obtained'] > $examSubject->practical_marks) {
                session()->flash('error', __('exams.practical_marks_exceed', ['name' => $student->name]));
                return;
            }
        }

        DB::transaction(function () use ($examSubject, $newStatus) {
            foreach ($this->marks as $studentId => $markData) {
                $obtained = $markData['obtained_marks'] !== '' ? (float) $markData['obtained_marks'] : 0;

                // Auto-sum component marks if components are used
                if ($examSubject->cq_marks || $examSubject->mcq_marks || $examSubject->practical_marks) {
                    $cq = $markData['cq_obtained'] !== '' ? (float) ($markData['cq_obtained'] ?? 0) : 0;
                    $mcq = $markData['mcq_obtained'] !== '' ? (float) ($markData['mcq_obtained'] ?? 0) : 0;
                    $practical = $markData['practical_obtained'] !== '' ? (float) ($markData['practical_obtained'] ?? 0) : 0;

                    if ($markData['obtained_marks'] === '' || $markData['obtained_marks'] === null) {
                        $obtained = $cq + $mcq + $practical;
                    }
                }

                Mark::updateOrCreate(
                    ['exam_subject_id' => $examSubject->id, 'student_id' => $studentId],
                    [
                        'obtained_marks' => $markData['is_absent'] ? 0 : $obtained,
                        'cq_obtained' => $markData['cq_obtained'] !== '' ? $markData['cq_obtained'] : null,
                        'mcq_obtained' => $markData['mcq_obtained'] !== '' ? $markData['mcq_obtained'] : null,
                        'practical_obtained' => $markData['practical_obtained'] !== '' ? $markData['practical_obtained'] : null,
                        'is_absent' => $markData['is_absent'] ?? false,
                        'entered_by' => Auth::id(),
                        'status' => $newStatus,
                    ]
                );
            }
        });

        $this->status = $newStatus;

        $message = $newStatus === 'draft' ? __('exams.marks_saved_draft') : __('exams.marks_submitted');
        session()->flash('success', $message);
    }

    public function getClasses()
    {
        $user = Auth::user();
        if ($user->hasRole(['super-admin', 'institute-admin'])) {
            return \App\Models\ClassModel::orderBy('numeric_order')->get();
        }
        return \App\Models\ClassModel::whereIn('id', $user->assignedClassIds())->orderBy('numeric_order')->get();
    }

    public function render()
    {
        $exam = $this->examId ? Exam::find($this->examId) : null;
        $classes = $this->getClasses();

        $examSubjects = ($this->examId && $this->classId)
            ? ExamSubject::where('exam_id', $this->examId)
                ->where('class_id', $this->classId)
                ->with('subject')
                ->get()
            : collect();

        $students = $this->examSubjectId
            ? Student::where('institute_id', Auth::user()->institute_id)
                ->where('class_id', $examSubjects->first()?->class_id)
                ->where('status', 'active')
                ->orderBy('roll')
                ->get()
            : collect();

        $examSubject = $this->examSubjectId ? ExamSubject::find($this->examSubjectId) : null;

        return view('livewire.mark-entry', [
            'exam' => $exam,
            'classes' => $classes,
            'examSubjects' => $examSubjects,
            'students' => $students,
            'examSubject' => $examSubject,
        ]);
    }
}
