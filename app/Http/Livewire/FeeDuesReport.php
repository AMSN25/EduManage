<?php

namespace App\Http\Livewire;

use App\Exports\DuesExport;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\StudentFee;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FeeDuesReport extends Component
{
    public $classId = '';
    public $classes = [];
    public $students = [];
    public $search = '';

    public function mount(): void
    {
        $instituteId = Auth::user()->institute_id;
        $this->classes = ClassModel::where('institute_id', $instituteId)
            ->orderBy('numeric_order')
            ->get();
    }

    public function updatedClassId(): void
    {
        $this->loadReport();
    }

    public function loadReport(): void
    {
        $instituteId = Auth::user()->institute_id;

        $query = Student::where('institute_id', $instituteId)
            ->where('status', 'active')
            ->with(['classModel', 'section', 'studentFees' => fn ($q) => $q->unpaidOrPartial()])
            ->whereHas('studentFees', fn ($q) => $q->unpaidOrPartial());

        if ($this->classId) {
            $query->where('class_id', $this->classId);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('student_id', 'like', '%' . $this->search . '%');
            });
        }

        $this->students = $query->get()->map(function ($student) {
            $fees = $student->studentFees;
            $student->total_due = $fees->sum('amount_due');
            $student->total_paid = $fees->sum('amount_paid');
            return $student;
        })->filter(fn ($s) => ((float) $s->total_due - (float) $s->total_paid) > 0)
          ->sortBy('name')
          ->values();
    }

    public function exportExcel(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new DuesExport($this->students),
            'dues_report_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function sendReminder(int $studentId): void
    {
        $student = Student::find($studentId);
        if (!$student || !$student->phone) {
            session()->flash('error', __('sms.no_phone_number'));
            return;
        }

        $fees = StudentFee::where('student_id', $studentId)
            ->unpaidOrPartial()
            ->with('feeStructure.feeType')
            ->get();

        $totalDue = $fees->sum('amount_due') - $fees->sum('amount_paid');
        $feeCount = $fees->count();

        $smsService = app(SmsService::class);
        $smsService->sendTemplate(
            Auth::user()->institute_id,
            'fee_due',
            $student->phone,
            [
                'student_name' => $student->name,
                'student_id' => $student->student_id,
                'total_due' => number_format($totalDue, 2),
                'fee_count' => $feeCount,
            ],
            Student::class,
            $student->id
        );

        session()->flash('success', __('sms.reminder_sent', ['name' => $student->name]));
    }

    public function render()
    {
        return view('livewire.fee-dues-report');
    }
}
