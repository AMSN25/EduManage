<?php

use App\Jobs\GeneratePdfJob;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\ExamRoom;
use App\Models\ExamSubject;
use App\Models\Institute;
use App\Models\SeatAssignment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\SeatAllocationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

function createInstituteWithExamAndStudents(int $studentCount = 5): array
{
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $section = \App\Models\Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);
    $subject = Subject::create(['name' => 'Math', 'numeric_order' => 1, 'institute_id' => $institute->id]);
    $class->subjects()->attach($subject->id);

    $exam = Exam::create([
        'institute_id' => $institute->id,
        'academic_year_id' => $year->id,
        'name' => 'Half Yearly',
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-15',
        'status' => 'ongoing',
    ]);

    ExamSubject::create([
        'exam_id' => $exam->id,
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'full_marks' => 100,
        'pass_marks' => 33,
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

    return compact('institute', 'year', 'class', 'section', 'subject', 'exam', 'students');
}

it('allocates students without double-booking seats', function () {
    $data = createInstituteWithExamAndStudents(10);

    $room1 = ExamRoom::create([
        'institute_id' => $data['institute']->id,
        'exam_id' => $data['exam']->id,
        'room_name' => 'Room 1',
        'capacity' => 6,
        'rows' => 2,
        'columns' => 3,
    ]);

    $room2 = ExamRoom::create([
        'institute_id' => $data['institute']->id,
        'exam_id' => $data['exam']->id,
        'room_name' => 'Room 2',
        'capacity' => 6,
        'rows' => 2,
        'columns' => 3,
    ]);

    $service = new SeatAllocationService();
    $result = $service->allocate(
        $data['exam'],
        [$room1->id, $room2->id],
        $data['institute']->id,
        'sequential'
    );

    expect($result['success'])->toBeTrue();
    expect($result['allocated'])->toBe(10);

    // Verify no student has duplicate seat assignments
    $allAssignments = SeatAssignment::all();
    $studentIds = $allAssignments->pluck('student_id')->toArray();
    expect($studentIds)->toHaveCount(10);
    expect(array_unique($studentIds))->toHaveCount(10);

    // Verify no seat is double-booked
    $room1Seats = SeatAssignment::where('exam_room_id', $room1->id)->pluck('seat_no')->toArray();
    $room2Seats = SeatAssignment::where('exam_room_id', $room2->id)->pluck('seat_no')->toArray();
    expect(array_unique($room1Seats))->toHaveCount(count($room1Seats));
    expect(array_unique($room2Seats))->toHaveCount(count($room2Seats));
});

it('fails clearly when capacity is insufficient', function () {
    $data = createInstituteWithExamAndStudents(10);

    $room = ExamRoom::create([
        'institute_id' => $data['institute']->id,
        'exam_id' => $data['exam']->id,
        'room_name' => 'Small Room',
        'capacity' => 5,
        'rows' => 2,
        'columns' => 3,
    ]);

    $service = new SeatAllocationService();

    try {
        $service->allocate(
            $data['exam'],
            [$room->id],
            $data['institute']->id,
            'sequential'
        );
        $this->fail('Expected RuntimeException was not thrown');
    } catch (\RuntimeException $e) {
        expect($e->getMessage())->toContain('5');
        expect($e->getMessage())->toContain('10');
    }
});

it('distributes students sequentially by roll', function () {
    $data = createInstituteWithExamAndStudents(5);

    $room = ExamRoom::create([
        'institute_id' => $data['institute']->id,
        'exam_id' => $data['exam']->id,
        'room_name' => 'Room 1',
        'capacity' => 5,
        'rows' => 2,
        'columns' => 3,
    ]);

    $service = new SeatAllocationService();
    $service->allocate(
        $data['exam'],
        [$room->id],
        $data['institute']->id,
        'sequential'
    );

    $assignments = SeatAssignment::where('exam_room_id', $room->id)
        ->with('student')
        ->orderBy('seat_no')
        ->get();

    // Students should be in roll order
    expect($assignments->count())->toBe(5);
    for ($i = 0; $i < 4; $i++) {
        expect($assignments[$i]->student->roll)->toBeLessThan($assignments[$i + 1]->student->roll);
    }
});

it('distributes students shuffled when strategy is shuffled', function () {
    $data = createInstituteWithExamAndStudents(5);

    $room = ExamRoom::create([
        'institute_id' => $data['institute']->id,
        'exam_id' => $data['exam']->id,
        'room_name' => 'Room 1',
        'capacity' => 5,
        'rows' => 2,
        'columns' => 3,
    ]);

    $service = new SeatAllocationService();
    $service->allocate(
        $data['exam'],
        [$room->id],
        $data['institute']->id,
        'shuffled'
    );

    $assignments = SeatAssignment::where('exam_room_id', $room->id)->count();
    expect($assignments)->toBe(5);
});

it('clears previous allocations before reallocating', function () {
    $data = createInstituteWithExamAndStudents(5);

    $room = ExamRoom::create([
        'institute_id' => $data['institute']->id,
        'exam_id' => $data['exam']->id,
        'room_name' => 'Room 1',
        'capacity' => 5,
        'rows' => 2,
        'columns' => 3,
    ]);

    $service = new SeatAllocationService();

    // First allocation
    $service->allocate($data['exam'], [$room->id], $data['institute']->id, 'sequential');
    expect(SeatAssignment::count())->toBe(5);

    // Second allocation — should replace, not duplicate
    $service->allocate($data['exam'], [$room->id], $data['institute']->id, 'sequential');
    expect(SeatAssignment::count())->toBe(5);
});

it('does not allocate students from other institutes', function () {
    $dataA = createInstituteWithExamAndStudents(3);

    $instituteB = Institute::create(['name' => 'B', 'slug' => 'inst-b', 'email' => 'b@test.com', 'is_active' => true]);
    $yearB = AcademicYear::create(['institute_id' => $instituteB->id, 'name' => '2026', 'is_current' => true]);
    $classB = ClassModel::create(['institute_id' => $instituteB->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $yearB->id]);
    $sectionB = \App\Models\Section::create(['institute_id' => $instituteB->id, 'class_id' => $classB->id, 'name' => 'A']);

    Student::create([
        'institute_id' => $instituteB->id,
        'student_id' => 'OTHER-2026-0001',
        'name' => 'Other Student',
        'gender' => 'male',
        'class_id' => $classB->id,
        'section_id' => $sectionB->id,
        'academic_year_id' => $yearB->id,
        'admission_date' => '2026-01-01',
        'status' => 'active',
        'roll' => 1,
    ]);

    $room = ExamRoom::create([
        'institute_id' => $dataA['institute']->id,
        'exam_id' => $dataA['exam']->id,
        'room_name' => 'Room 1',
        'capacity' => 20,
        'rows' => 4,
        'columns' => 5,
    ]);

    $service = new SeatAllocationService();
    $service->allocate($dataA['exam'], [$room->id], $dataA['institute']->id, 'sequential');

    // Only institute A students should be allocated
    $studentIds = SeatAssignment::pluck('student_id')->toArray();
    foreach ($studentIds as $sid) {
        $student = Student::find($sid);
        expect($student->institute_id)->toBe($dataA['institute']->id);
    }
});

it('generates pdf job that produces downloadable file', function () {
    Storage::fake('local');

    $data = createInstituteWithExamAndStudents(2);

    $job = new GeneratePdfJob(
        'admit-cards',
        $data['exam']->id,
        $data['class']->id,
        null,
        $data['institute']->id,
        'test_admit_cards.pdf'
    );

    // Execute the job
    $job->handle(new \App\Services\PdfGenerationService());

    Storage::disk('local')->assertExists('pdfs/test_admit_cards.pdf');
    Storage::disk('local')->assertExists('pdfs/test_admit_cards.pdf.meta');
});

it('validates room exists for institute before allocation', function () {
    $data = createInstituteWithExamAndStudents(3);

    $instituteB = Institute::create(['name' => 'B', 'slug' => 'inst-b', 'email' => 'b@test.com', 'is_active' => true]);

    $roomB = ExamRoom::create([
        'institute_id' => $instituteB->id,
        'exam_id' => $data['exam']->id,
        'room_name' => 'Room B',
        'capacity' => 20,
        'rows' => 4,
        'columns' => 5,
    ]);

    $service = new SeatAllocationService();

    try {
        $service->allocate(
            $data['exam'],
            [$roomB->id],
            $data['institute']->id,
            'sequential'
        );
        $this->fail('Expected RuntimeException was not thrown');
    } catch (\RuntimeException $e) {
        // Room not found for this institute
        expect($e->getMessage())->toContain(__('exams.no_rooms_found'));
    }
});

it('fails when no rooms are provided', function () {
    $data = createInstituteWithExamAndStudents(3);

    $service = new SeatAllocationService();

    try {
        $service->allocate(
            $data['exam'],
            [],
            $data['institute']->id,
            'sequential'
        );
        $this->fail('Expected RuntimeException was not thrown');
    } catch (\RuntimeException $e) {
        expect($e->getMessage())->toContain(__('exams.no_rooms_found'));
    }
});

it('prevents tenant A from seeing tenant B seat assignments', function () {
    $dataA = createInstituteWithExamAndStudents(3);

    $instituteB = Institute::create(['name' => 'B', 'slug' => 'inst-b', 'email' => 'b@test.com', 'is_active' => true]);
    $yearB = AcademicYear::create(['institute_id' => $instituteB->id, 'name' => '2026', 'is_current' => true]);
    $classB = ClassModel::create(['institute_id' => $instituteB->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $yearB->id]);
    $sectionB = \App\Models\Section::create(['institute_id' => $instituteB->id, 'class_id' => $classB->id, 'name' => 'A']);

    $studentB = Student::create([
        'institute_id' => $instituteB->id,
        'student_id' => 'B-2026-0001',
        'name' => 'Student B',
        'gender' => 'male',
        'class_id' => $classB->id,
        'section_id' => $sectionB->id,
        'academic_year_id' => $yearB->id,
        'admission_date' => '2026-01-01',
        'status' => 'active',
        'roll' => 1,
    ]);

    $examB = Exam::create([
        'institute_id' => $instituteB->id,
        'academic_year_id' => $yearB->id,
        'name' => 'Exam B',
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-15',
        'status' => 'ongoing',
    ]);

    $roomB = ExamRoom::create([
        'institute_id' => $instituteB->id,
        'exam_id' => $examB->id,
        'room_name' => 'Room B',
        'capacity' => 5,
        'rows' => 2,
        'columns' => 3,
    ]);

    SeatAssignment::create([
        'exam_room_id' => $roomB->id,
        'student_id' => $studentB->id,
        'seat_no' => 1,
    ]);

    $adminA = User::create([
        'name' => 'Admin A',
        'email' => 'adminA@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $dataA['institute']->id,
    ])->assignRole('institute-admin');

    actingAs($adminA);

    // Institute A should not see Institute B's room or assignments
    $roomsA = ExamRoom::where('exam_id', $dataA['exam']->id)->get();
    expect($roomsA->pluck('id'))->not->toContain($roomB->id);

    $assignmentsA = SeatAssignment::whereHas('examRoom', fn($q) => $q->where('exam_id', $dataA['exam']->id))->get();
    expect($assignmentsA->pluck('student_id'))->not->toContain($studentB->id);
});
