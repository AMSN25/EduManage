<?php

namespace App\Http\Livewire;

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AttendanceMark extends Component
{
    public ?int $classId = null;
    public ?int $sectionId = null;
    public string $date = '';
    public array $records = []; // student_id => status
    public array $remarks = []; // student_id => remark
    public bool $showForm = false;

    protected $rules = [
        'classId' => 'required|exists:classes,id',
        'sectionId' => 'required|exists:sections,id',
        'date' => 'required|date',
    ];

    protected $listeners = ['attendanceSaved' => '$refresh'];

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function updatedClassId(): void
    {
        $this->sectionId = null;
        $this->records = [];
        $this->remarks = [];
        $this->showForm = false;
    }

    public function resetRecords(): void
    {
        $this->records = [];
        $this->remarks = [];
        $this->showForm = false;
    }

    public function updatedSectionId(): void
    {
        $this->tryLoadStudents();
    }

    public function updatedDate(): void
    {
        $this->tryLoadStudents();
    }

    public function tryLoadStudents(): void
    {
        if ($this->classId && $this->sectionId && $this->date) {
            $this->loadStudents();
        }
    }

    public function loadStudents(): void
    {
        if (!$this->classId || !$this->sectionId || !$this->date) {
            $this->showForm = false;
            return;
        }

        $students = Student::where('institute_id', Auth::user()->institute_id)
            ->where('class_id', $this->classId)
            ->where('section_id', $this->sectionId)
            ->where('status', 'active')
            ->orderBy('roll')
            ->get();

        if ($students->isEmpty()) {
            session()->flash('error', __('attendance.no_students_found'));
            $this->showForm = false;
            return;
        }

        // Check if attendance already exists for this session
        $attendance = Attendance::findForSession(
            $this->classId,
            $this->sectionId,
            $this->date,
            Auth::user()->institute_id
        );

        $this->records = [];
        $this->remarks = [];

        if ($attendance) {
            // Load existing records
            foreach ($attendance->records as $record) {
                $this->records[$record->student_id] = $record->status;
                $this->remarks[$record->student_id] = $record->remarks ?? '';
            }
        } else {
            // Default all to present
            foreach ($students as $student) {
                $this->records[$student->id] = 'present';
                $this->remarks[$student->id] = '';
            }
        }

        $this->showForm = true;
    }

    public function markAllPresent(): void
    {
        $this->records = array_fill_keys(array_keys($this->records), 'present');
    }

    public function markAllAbsent(): void
    {
        $this->records = array_fill_keys(array_keys($this->records), 'absent');
    }

    public function save(): void
    {
        if (!$this->classId || !$this->sectionId || !$this->date) {
            return;
        }

        // Check permission
        if (!Auth::user()->can('mark', Attendance::class)) {
            session()->flash('error', __('attendance.no_permission'));
            return;
        }

        // Check date edit limit
        $dateObj = \Carbon\Carbon::parse($this->date);
        $allowedDays = config('attendance.edit_allowed_days', 3);
        $cutoffDate = now()->subDays($allowedDays)->startOfDay();

        if ($dateObj->lt($cutoffDate) && !Auth::user()->hasRole(['super-admin', 'institute-admin'])) {
            session()->flash('error', __('attendance.date_too_old', ['days' => $allowedDays]));
            return;
        }

        $students = Student::where('institute_id', Auth::user()->institute_id)
            ->where('class_id', $this->classId)
            ->where('section_id', $this->sectionId)
            ->where('status', 'active')
            ->get();

        DB::transaction(function () use ($students) {
            $attendance = Attendance::findForSession(
                $this->classId,
                $this->sectionId,
                $this->date,
                Auth::user()->institute_id
            );

            $isNew = !$attendance;

            if ($isNew) {
                $attendance = Attendance::create([
                    'institute_id' => Auth::user()->institute_id,
                    'class_id' => $this->classId,
                    'section_id' => $this->sectionId,
                    'date' => $this->date,
                    'taken_by' => Auth::id(),
                    'academic_year_id' => $this->getAcademicYearId(),
                ]);
            }

            // Track changes for audit log
            $changes = [];
            $existingRecords = $attendance->records->keyBy('student_id');

            foreach ($students as $student) {
                $newStatus = $this->records[$student->id] ?? 'present';
                $newRemarks = $this->remarks[$student->id] ?? null;

                $existingStatus = $existingRecords[$student->id]->status ?? null;
                $existingRemarks = $existingRecords[$student->id]->remarks ?? null;

                if ($existingStatus !== $newStatus || $existingRemarks !== $newRemarks) {
                    $changes[$student->id] = [
                        'status' => [$existingStatus, $newStatus],
                        'remarks' => [$existingRemarks, $newRemarks],
                    ];
                }

                AttendanceRecord::updateOrCreate(
                    ['attendance_id' => $attendance->id, 'student_id' => $student->id],
                    ['status' => $newStatus, 'remarks' => $newRemarks]
                );
            }

            // Log audit entry if there were changes
            if (!empty($changes)) {
                AuditLog::log(
                    Auth::user()->institute_id,
                    Auth::id(),
                    $isNew ? 'attendance.created' : 'attendance.updated',
                    $attendance,
                    $changes
                );
            }
        });

        session()->flash('success', __('attendance.attendance_saved'));
    }

    private function getAcademicYearId(): int
    {
        return \App\Models\AcademicYear::current()
            ->where('institute_id', Auth::user()->institute_id)
            ->first()
            ->id;
    }

    public function render()
    {
        $user = Auth::user();
        $isTeacher = $user->hasRole('teacher');

        // Admin sees all classes; teacher sees only assigned classes
        $classes = $isTeacher
            ? ClassModel::whereIn('id', $user->assignedClassIds())->orderBy('numeric_order')->get()
            : ClassModel::orderBy('numeric_order')->get();

        $sections = $this->classId
            ? ($isTeacher
                ? Section::whereIn('id', $user->assignedSectionIds($this->classId))->get()
                : Section::where('class_id', $this->classId)->get())
            : collect();

        // Reuse students if already loaded by loadStudents(), otherwise query fresh
        $students = ($this->classId && $this->sectionId && $this->showForm)
            ? Student::where('institute_id', $user->institute_id)
                ->where('class_id', $this->classId)
                ->where('section_id', $this->sectionId)
                ->where('status', 'active')
                ->orderBy('roll')
                ->get()
            : collect();

        $minDate = now()->subDays(config('attendance.edit_allowed_days', 3))->format('Y-m-d');

        return view('livewire.attendance-mark', [
            'classes' => $classes,
            'sections' => $sections,
            'students' => $students,
            'minDate' => $minDate,
        ]);
    }
}
