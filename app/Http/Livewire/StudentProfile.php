<?php

namespace App\Http\Livewire;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\StudentFee;
use Livewire\Component;

class StudentProfile extends Component
{
    public $studentId;
    public string $activeTab = 'overview';

    public function mount($studentId): void
    {
        $this->studentId = (int) $studentId;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $student = Student::with(['classModel', 'section', 'group', 'academicYear', 'guardians'])
            ->findOrFail($this->studentId);

        $attendanceStats = null;
        $recentAttendance = collect();
        $feeData = null;

        if ($this->activeTab === 'attendance') {
            $attendanceStats = $this->getAttendanceStats($student);
            $recentAttendance = $this->getRecentAttendance($student);
        }

        if ($this->activeTab === 'fees') {
            $feeData = $this->getFeeData($student);
        }

        return view('livewire.student-profile', [
            'student' => $student,
            'attendanceStats' => $attendanceStats,
            'recentAttendance' => $recentAttendance,
            'feeData' => $feeData,
        ]);
    }

    private function getAttendanceStats(Student $student): array
    {
        $records = Attendance::where('institute_id', $student->institute_id)
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('date', '>=', now()->startOfYear())
            ->with('records')
            ->get()
            ->pluck('records')
            ->flatten()
            ->where('student_id', $student->id);

        $total = $records->count();
        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $leave = $records->where('status', 'leave')->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'leave' => $leave,
            'percentage' => $total > 0 ? round(($present + $late) / $total * 100, 1) : 0,
        ];
    }

    private function getRecentAttendance(Student $student): \Illuminate\Support\Collection
    {
        return Attendance::where('institute_id', $student->institute_id)
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->with(['records' => fn ($q) => $q->where('student_id', $student->id)])
            ->orderByDesc('date')
            ->limit(30)
            ->get()
            ->filter(fn ($a) => $a->records->isNotEmpty())
            ->map(fn ($a) => [
                'date' => $a->date,
                'status' => $a->records->first()->status,
                'remarks' => $a->records->first()->remarks,
            ]);
    }

    private function getFeeData(Student $student): array
    {
        $fees = StudentFee::where('student_id', $student->id)
            ->with('feeStructure.feeType')
            ->orderByDesc('due_date')
            ->get();

        $allFees = $fees;
        $currentDues = $fees->filter(fn ($f) => $f->status !== 'paid' && $f->due_date->isFuture());
        $arrears = $fees->filter(fn ($f) => $f->status !== 'paid' && $f->due_date->isPast());
        $paid = $fees->filter(fn ($f) => $f->status === 'paid');

        $totalDue = $allFees->sum('amount_due');
        $totalPaid = $allFees->sum('amount_paid');

        return [
            'all' => $allFees,
            'current_dues' => $currentDues,
            'arrears' => $arrears,
            'paid' => $paid,
            'total_due' => $totalDue,
            'total_paid' => $totalPaid,
            'remaining' => $totalDue - $totalPaid,
        ];
    }
}
