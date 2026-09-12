<?php

namespace App\Http\Livewire;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\ExamSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ExamSetupWizard extends Component
{
    public string $step = 'details'; // details, classes, subjects, review
    public string $name = '';
    public string $startDate = '';
    public string $endDate = '';
    public array $selectedClassIds = [];
    public array $subjects = []; // class_id => [subject_id => ['full_marks' => 100, ...]]
    public ?int $examId = null;

    public array $classes = [];
    public int $defaultFullMarks = 100;
    public int $defaultPassMarks = 33;

    public function mount(): void
    {
        $this->classes = ClassModel::orderBy('numeric_order')->get()->toArray();
        $this->defaultFullMarks = config('exams.default_full_marks', 100);
        $this->defaultPassMarks = config('exams.default_pass_marks', 33);
    }

    public function nextStep(): void
    {
        if ($this->step === 'details') {
            $this->validate([
                'name' => 'required|string|max:255',
                'startDate' => 'required|date',
                'endDate' => 'required|date|after_or_equal:startDate',
            ]);
            $this->step = 'classes';
        } elseif ($this->step === 'classes') {
            $this->validate([
                'selectedClassIds' => 'required|array|min:1',
            ]);
            $this->loadSubjects();
            $this->step = 'subjects';
        } elseif ($this->step === 'subjects') {
            $this->step = 'review';
        }
    }

    public function prevStep(): void
    {
        if ($this->step === 'classes') {
            $this->step = 'details';
        } elseif ($this->step === 'subjects') {
            $this->step = 'classes';
        } elseif ($this->step === 'review') {
            $this->step = 'subjects';
        }
    }

    public function loadSubjects(): void
    {
        $this->subjects = [];

        foreach ($this->selectedClassIds as $classId) {
            $class = ClassModel::with('subjects')->find($classId);
            if ($class) {
                foreach ($class->subjects as $subject) {
                    $this->subjects[$classId][$subject->id] = [
                        'full_marks' => $this->defaultFullMarks,
                        'pass_marks' => $this->defaultPassMarks,
                        'cq_marks' => config('exams.default_cq_marks'),
                        'mcq_marks' => config('exams.default_mcq_marks'),
                        'practical_marks' => config('exams.default_practical_marks'),
                    ];
                }
            }
        }
    }

    public function updateSubjectMark(string $classId, string $subjectId, string $field, ?string $value): void
    {
        $this->subjects[$classId][$subjectId][$field] = $value === '' ? null : (int) $value;
    }

    public function save(): void
    {
        if (!Auth::user()->can('manage', Exam::class)) {
            session()->flash('error', __('exams.no_permission'));
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'selectedClassIds' => 'required|array|min:1',
        ]);

        $year = AcademicYear::current()->where('institute_id', Auth::user()->institute_id)->first();

        DB::transaction(function () use ($year) {
            $exam = Exam::create([
                'institute_id' => Auth::user()->institute_id,
                'academic_year_id' => $year->id,
                'name' => $this->name,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'status' => 'draft',
            ]);

            foreach ($this->subjects as $classId => $classSubjects) {
                foreach ($classSubjects as $subjectId => $marks) {
                    ExamSubject::create([
                        'exam_id' => $exam->id,
                        'class_id' => $classId,
                        'subject_id' => $subjectId,
                        'full_marks' => $marks['full_marks'],
                        'pass_marks' => $marks['pass_marks'],
                        'cq_marks' => $marks['cq_marks'],
                        'mcq_marks' => $marks['mcq_marks'],
                        'practical_marks' => $marks['practical_marks'],
                    ]);
                }
            }

            $this->examId = $exam->id;
        });

        session()->flash('success', __('exams.exam_created'));
        $this->redirectRoute('exams.index');
    }

    public function render()
    {
        $classNames = [];
        $subjectNames = [];

        if ($this->step === 'subjects') {
            $classIds = array_keys($this->subjects);
            $allSubjectIds = collect($this->subjects)->flatten()->keys()->toArray();

            $classNames = ClassModel::whereIn('id', $classIds)->pluck('name', 'id')->toArray();
            $subjectNames = \App\Models\Subject::whereIn('id', $allSubjectIds)->pluck('name', 'id')->toArray();
        }

        return view('livewire.exam-setup-wizard', [
            'allClasses' => $this->classes,
            'classNames' => $classNames,
            'subjectNames' => $subjectNames,
        ]);
    }
}
