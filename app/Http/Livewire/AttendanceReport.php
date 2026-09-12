<?php

namespace App\Http\Livewire;

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\Student;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceReport extends Component
{
    use WithPagination;

    public ?int $classId = null;
    public ?int $sectionId = null;
    public int $month;
    public int $year;
    public string $view = 'monthly'; // 'monthly' or 'student'

    public function mount(): void
    {
        $this->month = (int) now()->format('m');
        $this->year = (int) now()->format('Y');
    }

    public function updatedClassId(): void
    {
        $this->sectionId = null;
    }

    public function render()
    {
        $classes = ClassModel::orderBy('numeric_order')->get();
        $sections = $this->classId
            ? Section::where('class_id', $this->classId)->get()
            : collect();

        $monthlyData = null;
        $studentStats = null;

        if ($this->classId && $this->sectionId) {
            $students = Student::where('institute_id', Auth::user()->institute_id)
                ->where('class_id', $this->classId)
                ->where('section_id', $this->sectionId)
                ->where('status', 'active')
                ->orderBy('roll')
                ->get();

            $startDate = \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $attendances = Attendance::where('institute_id', Auth::user()->institute_id)
                ->where('class_id', $this->classId)
                ->where('section_id', $this->sectionId)
                ->whereBetween('date', [$startDate, $endDate])
                ->with('records')
                ->get()
                ->keyBy(fn ($a) => $a->date->format('Y-m-d'));

            // Build monthly grid: students x days
            $daysInMonth = $startDate->daysInMonth;
            $monthlyData = [
                'students' => $students,
                'days' => $daysInMonth,
                'startDate' => $startDate,
                'attendances' => $attendances,
            ];

            // Build student-level stats
            $studentStats = $students->map(function ($student) use ($attendances) {
                $total = 0;
                $present = 0;
                $absent = 0;
                $late = 0;
                $leave = 0;

                foreach ($attendances as $attendance) {
                    $record = $attendance->records->firstWhere('student_id', $student->id);
                    if ($record) {
                        $total++;
                        match ($record->status) {
                            'present' => $present++,
                            'absent' => $absent++,
                            'late' => $late++,
                            'leave' => $leave++,
                        };
                    }
                }

                return [
                    'student' => $student,
                    'total' => $total,
                    'present' => $present,
                    'absent' => $absent,
                    'late' => $late,
                    'leave' => $leave,
                    'percentage' => $total > 0 ? round(($present + $late) / $total * 100, 1) : 0,
                ];
            });
        }

        return view('livewire.attendance-report', [
            'classes' => $classes,
            'sections' => $sections,
            'monthlyData' => $monthlyData,
            'studentStats' => $studentStats,
        ]);
    }

    public function notifyGuardians(int $attendanceId): void
    {
        $this->authorize('viewAny', Attendance::class);

        $attendance = Attendance::with('records.student')->findOrFail($attendanceId);

        $absentStudents = $attendance->records
            ->filter(fn ($r) => $r->status === 'absent')
            ->pluck('student')
            ->filter(fn ($s) => $s->phone);

        $smsService = app(SmsService::class);

        $sent = 0;
        foreach ($absentStudents as $student) {
            $templateKey = 'absent';
            $template = \App\Models\SmsTemplate::where('institute_id', $attendance->institute_id)
                ->where('key', $templateKey)
                ->where('is_active', true)
                ->first();

            if ($template) {
                $smsService->sendTemplate(
                    $attendance->institute_id,
                    $templateKey,
                    $student->phone,
                    [
                        'student_name' => $student->name,
                        'date' => $attendance->date->format('d M Y'),
                        'class' => $student->class->name ?? '',
                        'section' => $student->section->name ?? '',
                    ],
                    Student::class,
                    $student->id
                );
                $sent++;
            }
        }

        session()->flash('success', __('sms.absent_notification_sent', ['count' => $sent]));
    }
}
