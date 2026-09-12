<?php

namespace App\Jobs;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\FeeStructure;
use App\Models\Institute;
use App\Models\Student;
use App\Models\StudentFee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GenerateMonthlyFees implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    public function __construct(
        public int $instituteId,
        public ?string $month = null,
    ) {}

    public function handle(): void
    {
        $month = $this->month ?? now()->format('Y-m');
        $institute = Institute::findOrFail($this->instituteId);

        $currentYear = AcademicYear::where('institute_id', $this->instituteId)
            ->where('is_current', true)
            ->first();

        if (!$currentYear) {
            return;
        }

        $feeStructures = FeeStructure::where('institute_id', $this->instituteId)
            ->where('academic_year_id', $currentYear->id)
            ->whereHas('feeType', fn ($q) => $q->where('is_recurring', true))
            ->get();

        if ($feeStructures->isEmpty()) {
            return;
        }

        $students = Student::where('institute_id', $this->instituteId)
            ->where('status', 'active')
            ->get()
            ->groupBy('class_id');

        DB::transaction(function () use ($feeStructures, $students, $month) {
            foreach ($feeStructures as $structure) {
                $classStudents = $students->get($structure->class_id);

                if (!$classStudents) {
                    continue;
                }

                foreach ($classStudents as $student) {
                    // Idempotent: skip if already exists for this student+structure+month
                    $exists = StudentFee::where('student_id', $student->id)
                        ->where('fee_structure_id', $structure->id)
                        ->where('month', $month)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $dueDay = $structure->due_day ?? 10;
                    $dueDate = \Carbon\Carbon::parse($month . '-' . str_pad($dueDay, 2, '0', STR_PAD_LEFT));

                    StudentFee::create([
                        'student_id' => $student->id,
                        'fee_structure_id' => $structure->id,
                        'month' => $month,
                        'amount_due' => $structure->amount,
                        'amount_paid' => 0,
                        'status' => 'unpaid',
                        'due_date' => $dueDate,
                    ]);
                }
            }
        });
    }
}
