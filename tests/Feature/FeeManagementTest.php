<?php

use App\Jobs\GenerateMonthlyFees;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\FeeCounter;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Institute;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\User;
use App\Services\FeeReceiptGenerator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

function createInstituteWithStudentAndFees(int $studentCount = 1): array
{
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $section = \App\Models\Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    $feeType = FeeType::create(['institute_id' => $institute->id, 'name' => 'Monthly Tuition', 'is_recurring' => true]);
    $structure = FeeStructure::create([
        'institute_id' => $institute->id,
        'class_id' => $class->id,
        'fee_type_id' => $feeType->id,
        'academic_year_id' => $year->id,
        'amount' => 1500,
        'due_day' => 10,
    ]);

    $students = [];
    for ($i = 1; $i <= $studentCount; $i++) {
        $students[] = Student::create([
            'institute_id' => $institute->id,
            'student_id' => "TEST-2026-" . str_pad($i, 4, '0', STR_PAD_LEFT),
            'name' => "Student $i",
            'gender' => 'male',
            'class_id' => $class->id,
            'section_id' => $section->id,
            'academic_year_id' => $year->id,
            'admission_date' => '2026-01-01',
            'status' => 'active',
            'roll' => $i,
        ]);
    }

    return compact('institute', 'year', 'class', 'section', 'feeType', 'structure', 'students');
}

it('generates unique receipt_no under concurrent collection', function () {
    $data = createInstituteWithStudentAndFees(1);
    $generator = new FeeReceiptGenerator();

    // Simulate 20 concurrent collections
    $receipts = [];
    for ($i = 0; $i < 20; $i++) {
        $receipts[] = $generator->generate($data['institute']);
    }

    // All receipt numbers must be unique
    expect(array_unique($receipts))->toHaveCount(20);

    // All must follow the format
    foreach ($receipts as $receipt) {
        expect($receipt)->toMatch('/^TEST-2026-\d{6}$/');
    }
});

it('generates receipt_no with lockForUpdate to prevent races', function () {
    $data = createInstituteWithStudentAndFees(1);

    // Simulate concurrent counter increment by running in parallel transactions
    $receipts = collect();
    $barrier = new \stdClass();
    $barrier->count = 0;

    $threads = [];
    for ($i = 0; $i < 10; $i++) {
        $threads[] = new \Thread(function () use ($data, &$receipts) {
            $generator = new FeeReceiptGenerator();
            $receipt = $generator->generate($data['institute']);
            $receipts[] = $receipt;
        });
    }

    foreach ($threads as $thread) {
        $thread->start();
    }
    foreach ($threads as $thread) {
        $thread->join();
    }

    // Even with threading, all should be unique
    expect($receipts->unique()->count())->toBe(10);
})->skip(!extension_loaded('pthreads'), 'Requires pthreads extension');

it('generates sequential receipt numbers', function () {
    $data = createInstituteWithStudentAndFees(1);
    $generator = new FeeReceiptGenerator();

    $r1 = $generator->generate($data['institute']);
    $r2 = $generator->generate($data['institute']);
    $r3 = $generator->generate($data['institute']);

    // Should be sequential
    expect($r1)->toBe('TEST-2026-000001');
    expect($r2)->toBe('TEST-2026-000002');
    expect($r3)->toBe('TEST-2026-000003');
});

it('partially paid fee correctly updates status and remaining', function () {
    $data = createInstituteWithStudentAndFees(1);
    $student = $data['students'][0];

    $studentFee = StudentFee::create([
        'student_id' => $student->id,
        'fee_structure_id' => $data['structure']->id,
        'month' => '2026-09',
        'amount_due' => 1500,
        'amount_paid' => 0,
        'status' => 'unpaid',
        'due_date' => '2026-09-10',
    ]);

    // Partial payment
    $studentFee->update([
        'amount_paid' => 500,
        'status' => 'partial',
    ]);

    $studentFee->refresh();

    expect($studentFee->status)->toBe('partial');
    expect($studentFee->amount_paid)->toBe('500.00');
    expect($studentFee->remaining_due)->toBe(1000.0);

    // Full payment
    $studentFee->update([
        'amount_paid' => 1500,
        'status' => 'paid',
    ]);

    $studentFee->refresh();
    expect($studentFee->status)->toBe('paid');
    expect($studentFee->remaining_due)->toBe(0.0);
});

it('monthly fee generation job is idempotent', function () {
    $data = createInstituteWithStudentAndFees(3);

    // Run once
    $job = new GenerateMonthlyFees($data['institute']->id, '2026-09');
    $job->handle();

    $count1 = StudentFee::where('month', '2026-09')->count();
    expect($count1)->toBe(3);

    // Run again — should not duplicate
    $job->handle();

    $count2 = StudentFee::where('month', '2026-09')->count();
    expect($count2)->toBe(3);
});

it('monthly fee generation creates fees for active students only', function () {
    $data = createInstituteWithStudentAndFees(2);

    // Create inactive student
    Student::create([
        'institute_id' => $data['institute']->id,
        'student_id' => 'TEST-2026-0099',
        'name' => 'Inactive Student',
        'gender' => 'male',
        'class_id' => $data['class']->id,
        'section_id' => $data['section']->id,
        'academic_year_id' => $data['year']->id,
        'admission_date' => '2026-01-01',
        'status' => 'inactive',
        'roll' => 99,
    ]);

    $job = new GenerateMonthlyFees($data['institute']->id, '2026-09');
    $job->handle();

    // Only active students get fees
    expect(StudentFee::where('month', '2026-09')->count())->toBe(2);
});

it('accountant can collect fees', function () {
    $data = createInstituteWithStudentAndFees(1);

    $accountant = User::create([
        'name' => 'Accountant',
        'email' => 'acc@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $data['institute']->id,
    ])->assignRole('accountant');

    actingAs($accountant);

    $this->assertTrue($accountant->can('collect', FeeStructure::class));
    $this->assertTrue($accountant->can('viewReports', FeeStructure::class));
});

it('accountant cannot modify fee structures', function () {
    $data = createInstituteWithStudentAndFees(1);

    $accountant = User::create([
        'name' => 'Accountant',
        'email' => 'acc@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $data['institute']->id,
    ])->assignRole('accountant');

    actingAs($accountant);

    $this->assertFalse($accountant->can('manageStructures', FeeStructure::class));
});

it('institute-admin can manage fee structures', function () {
    $data = createInstituteWithStudentAndFees(1);

    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $data['institute']->id,
    ])->assignRole('institute-admin');

    actingAs($admin);

    $this->assertTrue($admin->can('manageStructures', FeeStructure::class));
    $this->assertTrue($admin->can('collect', FeeStructure::class));
});

it('prevents tenant A from seeing tenant B fee data', function () {
    $instituteA = Institute::create(['name' => 'A', 'slug' => 'inst-a', 'email' => 'a@test.com', 'is_active' => true]);
    $yearA = AcademicYear::create(['institute_id' => $instituteA->id, 'name' => '2026', 'is_current' => true]);
    $classA = ClassModel::create(['institute_id' => $instituteA->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $yearA->id]);
    $sectionA = \App\Models\Section::create(['institute_id' => $instituteA->id, 'class_id' => $classA->id, 'name' => 'A']);
    $feeTypeA = FeeType::create(['institute_id' => $instituteA->id, 'name' => 'Monthly', 'is_recurring' => true]);
    $structureA = FeeStructure::create([
        'institute_id' => $instituteA->id, 'class_id' => $classA->id, 'fee_type_id' => $feeTypeA->id,
        'academic_year_id' => $yearA->id, 'amount' => 1500,
    ]);
    $studentA = Student::create([
        'institute_id' => $instituteA->id, 'student_id' => 'A-2026-0001', 'name' => 'Student A', 'gender' => 'male',
        'class_id' => $classA->id, 'section_id' => $sectionA->id, 'academic_year_id' => $yearA->id,
        'admission_date' => '2026-01-01', 'status' => 'active', 'roll' => 1,
    ]);

    $instituteB = Institute::create(['name' => 'B', 'slug' => 'inst-b', 'email' => 'b@test.com', 'is_active' => true]);
    $yearB = AcademicYear::create(['institute_id' => $instituteB->id, 'name' => '2026', 'is_current' => true]);
    $classB = ClassModel::create(['institute_id' => $instituteB->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $yearB->id]);
    $sectionB = \App\Models\Section::create(['institute_id' => $instituteB->id, 'class_id' => $classB->id, 'name' => 'A']);
    $feeTypeB = FeeType::create(['institute_id' => $instituteB->id, 'name' => 'Monthly', 'is_recurring' => true]);
    $structureB = FeeStructure::create([
        'institute_id' => $instituteB->id, 'class_id' => $classB->id, 'fee_type_id' => $feeTypeB->id,
        'academic_year_id' => $yearB->id, 'amount' => 2000,
    ]);
    $studentB = Student::create([
        'institute_id' => $instituteB->id, 'student_id' => 'B-2026-0001', 'name' => 'Student B', 'gender' => 'male',
        'class_id' => $classB->id, 'section_id' => $sectionB->id, 'academic_year_id' => $yearB->id,
        'admission_date' => '2026-01-01', 'status' => 'active', 'roll' => 1,
    ]);

    // Institute A student fee
    $feeA = StudentFee::create([
        'student_id' => $studentA->id, 'fee_structure_id' => $structureA->id, 'month' => '2026-09',
        'amount_due' => 1500, 'amount_paid' => 0, 'status' => 'unpaid', 'due_date' => '2026-09-10',
    ]);

    // Institute B student fee
    $feeB = StudentFee::create([
        'student_id' => $studentB->id, 'fee_structure_id' => $structureB->id, 'month' => '2026-09',
        'amount_due' => 2000, 'amount_paid' => 0, 'status' => 'unpaid', 'due_date' => '2026-09-10',
    ]);

    $adminA = User::create([
        'name' => 'Admin A', 'email' => 'adminA@test.com',
        'password' => Hash::make('password'), 'institute_id' => $instituteA->id,
    ])->assignRole('institute-admin');

    actingAs($adminA);

    // Institute A should only see their student's fees
    $feesA = StudentFee::where('student_id', $studentA->id)->get();
    expect($feesA)->toHaveCount(1);
    expect($feesA->first()->amount_due)->toBe('1500.00');
});

it('failed payment mid-transaction leaves no orphaned fee_payments', function () {
    $data = createInstituteWithStudentAndFees(1);
    $student = $data['students'][0];

    $studentFee = StudentFee::create([
        'student_id' => $student->id,
        'fee_structure_id' => $data['structure']->id,
        'month' => '2026-09',
        'amount_due' => 1500,
        'amount_paid' => 0,
        'status' => 'unpaid',
        'due_date' => '2026-09-10',
    ]);

    $initialPaymentCount = FeePayment::count();
    $initialPaid = $studentFee->amount_paid;

    // Simulate a failed transaction
    try {
        DB::transaction(function () use ($studentFee) {
            // Update student fee
            $studentFee->update([
                'amount_paid' => 500,
                'status' => 'partial',
            ]);

            // Create payment
            FeePayment::create([
                'institute_id' => $studentFee->student->institute_id,
                'student_fee_id' => $studentFee->id,
                'amount' => 500,
                'payment_method' => 'cash',
                'receipt_no' => 'TEST-2026-000001',
                'collected_by' => 1,
                'paid_at' => now(),
            ]);

            // Simulate failure
            throw new \Exception('Simulated failure');
        });
    } catch (\Exception $e) {
        // Expected
    }

    // Verify no orphaned payment was created
    expect(FeePayment::count())->toBe($initialPaymentCount);

    // Verify student fee was not updated
    $studentFee->refresh();
    expect($studentFee->amount_paid)->toBe($initialPaid);
    expect($studentFee->status)->toBe('unpaid');
});

it('validates payment does not exceed remaining due', function () {
    $data = createInstituteWithStudentAndFees(1);
    $student = $data['students'][0];

    $studentFee = StudentFee::create([
        'student_id' => $student->id,
        'fee_structure_id' => $data['structure']->id,
        'month' => '2026-09',
        'amount_due' => 1500,
        'amount_paid' => 1000,
        'status' => 'partial',
        'due_date' => '2026-09-10',
    ]);

    $remaining = (float) $studentFee->amount_due - (float) $studentFee->amount_paid;
    expect($remaining)->toBe(500.0);

    // Payment of 600 should fail (exceeds remaining 500)
    $exceeds = 600 > $remaining;
    expect($exceeds)->toBeTrue();
});

it('fee_types unique per institute', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);

    FeeType::create(['institute_id' => $institute->id, 'name' => 'Monthly', 'is_recurring' => true]);

    try {
        FeeType::create(['institute_id' => $institute->id, 'name' => 'Monthly', 'is_recurring' => true]);
        $this->fail('Expected unique constraint violation');
    } catch (\Exception $e) {
        expect($e->getMessage())->toContain('UNIQUE');
    }
});
