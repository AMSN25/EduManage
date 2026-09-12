<?php

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\GradeRange;
use App\Models\GradingSystem;
use App\Models\Institute;
use App\Models\Mark;
use App\Models\Result;
use App\Models\ResultSubjectBreakdown;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\ResultCalculationService;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;

function createExamWithMarks(int $studentCount = 3): array
{
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $section = Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    $gradingSystem = GradingSystem::create(['institute_id' => $institute->id, 'name' => 'BD Grading', 'is_default' => true]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 80, 'max_percent' => 100, 'grade' => 'A+', 'gpa_point' => 5.0]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 70, 'max_percent' => 79.99, 'grade' => 'A', 'gpa_point' => 4.0]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 60, 'max_percent' => 69.99, 'grade' => 'A-', 'gpa_point' => 3.5]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 50, 'max_percent' => 59.99, 'grade' => 'B', 'gpa_point' => 3.0]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 40, 'max_percent' => 49.99, 'grade' => 'C', 'gpa_point' => 2.0]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 33, 'max_percent' => 39.99, 'grade' => 'D', 'gpa_point' => 1.0]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 0, 'max_percent' => 32.99, 'grade' => 'F', 'gpa_point' => 0.0]);

    $subjects = [];
    for ($i = 1; $i <= 3; $i++) {
        $subjects[] = Subject::create(['institute_id' => $institute->id, 'name' => "Subject $i", 'code' => "SUB$i"]);
    }

    $exam = Exam::create([
        'institute_id' => $institute->id,
        'academic_year_id' => $year->id,
        'name' => 'Final Exam',
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-10',
        'status' => 'completed',
    ]);

    $examSubjects = [];
    foreach ($subjects as $subject) {
        $examSubjects[] = ExamSubject::create([
            'exam_id' => $exam->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'full_marks' => 100,
            'pass_marks' => 33,
        ]);
    }

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

    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    return compact('institute', 'year', 'class', 'section', 'gradingSystem', 'subjects', 'exam', 'examSubjects', 'students', 'admin');
}

it('calculates results for all students in an exam', function () {
    $data = createExamWithMarks(3);
    actingAs($data['admin']);

    // Create submitted marks for all students
    // Student 1: 90, 80, 70 → high scores
    // Student 2: 60, 50, 40 → medium scores
    // Student 3: 30, 20, 10 → low scores
    $marksData = [
        1 => [90, 80, 70],
        2 => [60, 50, 40],
        3 => [30, 20, 10],
    ];

    foreach ($data['students'] as $student) {
        foreach ($data['examSubjects'] as $index => $es) {
            Mark::create([
                'exam_subject_id' => $es->id,
                'student_id' => $student->id,
                'obtained_marks' => $marksData[$student->roll][$index],
                'entered_by' => $data['admin']->id,
                'status' => 'submitted',
            ]);
        }
    }

    $service = new ResultCalculationService();
    $service->calculateForExam($data['exam']);

    // Verify Result rows created
    expect(Result::where('exam_id', $data['exam']->id)->count())->toBe(3);

    // Verify student 1 has high GPA
    $result1 = Result::where('exam_id', $data['exam']->id)->where('student_id', $data['students'][0]->id)->first();
    expect($result1->total_obtained)->toBe('240.00');
    expect((float) $result1->gpa)->toBeGreaterThan(3.0);

    // Verify student 3 has low GPA
    $result3 = Result::where('exam_id', $data['exam']->id)->where('student_id', $data['students'][2]->id)->first();
    expect((float) $result3->gpa)->toBe(0.0);
    expect($result3->grade)->toBe('F');
});

it('calculates subject breakdowns correctly', function () {
    $data = createExamWithMarks(1);
    actingAs($data['admin']);

    Mark::create([
        'exam_subject_id' => $data['examSubjects'][0]->id,
        'student_id' => $data['students'][0]->id,
        'obtained_marks' => 85,
        'entered_by' => $data['admin']->id,
        'status' => 'submitted',
    ]);

    Mark::create([
        'exam_subject_id' => $data['examSubjects'][1]->id,
        'student_id' => $data['students'][0]->id,
        'obtained_marks' => 75,
        'entered_by' => $data['admin']->id,
        'status' => 'submitted',
    ]);

    Mark::create([
        'exam_subject_id' => $data['examSubjects'][2]->id,
        'student_id' => $data['students'][0]->id,
        'obtained_marks' => 65,
        'entered_by' => $data['admin']->id,
        'status' => 'submitted',
    ]);

    $service = new ResultCalculationService();
    $service->calculateForExam($data['exam']);

    $result = Result::where('exam_id', $data['exam']->id)->first();
    $breakdowns = $result->breakdowns()->with('subject')->get();

    expect($breakdowns)->toHaveCount(3);

    // Subject 1: 85/100 = 85% → A+ (5.0)
    $bd1 = $breakdowns->firstWhere('subject_id', $data['examSubjects'][0]->subject_id);
    expect($bd1->obtained)->toBe('85.00');
    expect($bd1->grade)->toBe('A+');
    expect((float) $bd1->gpa_point)->toBe(5.0);

    // Subject 2: 75/100 = 75% → A (4.0)
    $bd2 = $breakdowns->firstWhere('subject_id', $data['examSubjects'][1]->subject_id);
    expect($bd2->grade)->toBe('A');
    expect((float) $bd2->gpa_point)->toBe(4.0);

    // Subject 3: 65/100 = 65% → A- (3.5)
    $bd3 = $breakdowns->firstWhere('subject_id', $data['examSubjects'][2]->subject_id);
    expect($bd3->grade)->toBe('A-');
    expect((float) $bd3->gpa_point)->toBe(3.5);
});

it('assigns dense rank positions correctly', function () {
    $data = createExamWithMarks(3);
    actingAs($data['admin']);

    // Student 1: 90, 90, 90 → 90% → A+ (5.0)
    // Student 2: 90, 90, 90 → 90% → A+ (5.0) — tied with student 1
    // Student 3: 50, 50, 50 → 50% → B (3.0)
    // Dense rank: 1, 1, 2
    foreach ($data['students'] as $studentIndex => $student) {
        $score = $studentIndex < 2 ? 90 : 50;
        foreach ($data['examSubjects'] as $es) {
            Mark::create([
                'exam_subject_id' => $es->id,
                'student_id' => $student->id,
                'obtained_marks' => $score,
                'entered_by' => $data['admin']->id,
                'status' => 'submitted',
            ]);
        }
    }

    $service = new ResultCalculationService();
    $service->calculateForExam($data['exam']);

    $results = Result::where('exam_id', $data['exam']->id)
        ->orderBy('position')
        ->get();

    expect($results)->toHaveCount(3);
    expect($results[0]->position)->toBe(1);
    expect($results[1]->position)->toBe(1);
    expect($results[2]->position)->toBe(2);
});

it('excludes incomplete students from ranking', function () {
    $data = createExamWithMarks(2);
    actingAs($data['admin']);

    // Student 1: marks for all 3 subjects
    foreach ($data['examSubjects'] as $es) {
        Mark::create([
            'exam_subject_id' => $es->id,
            'student_id' => $data['students'][0]->id,
            'obtained_marks' => 80,
            'entered_by' => $data['admin']->id,
            'status' => 'submitted',
        ]);
    }

    // Student 2: marks for only 2 subjects (missing subject 3)
    Mark::create([
        'exam_subject_id' => $data['examSubjects'][0]->id,
        'student_id' => $data['students'][1]->id,
        'obtained_marks' => 90,
        'entered_by' => $data['admin']->id,
        'status' => 'submitted',
    ]);
    Mark::create([
        'exam_subject_id' => $data['examSubjects'][1]->id,
        'student_id' => $data['students'][1]->id,
        'obtained_marks' => 90,
        'entered_by' => $data['admin']->id,
        'status' => 'submitted',
    ]);

    $service = new ResultCalculationService();
    $service->calculateForExam($data['exam']);

    // Student 1 should be ranked
    $result1 = Result::where('exam_id', $data['exam']->id)
        ->where('student_id', $data['students'][0]->id)
        ->first();
    expect($result1->position)->toBe(1);

    // Student 2 should NOT be ranked (position null)
    $result2 = Result::where('exam_id', $data['exam']->id)
        ->where('student_id', $data['students'][1]->id)
        ->first();
    expect($result2->position)->toBeNull();
    expect((float) $result2->gpa)->toBe(0.0);
    expect($result2->grade)->toBe('F');
});

it('is idempotent when re-running calculation', function () {
    $data = createExamWithMarks(2);
    actingAs($data['admin']);

    foreach ($data['students'] as $student) {
        foreach ($data['examSubjects'] as $es) {
            Mark::create([
                'exam_subject_id' => $es->id,
                'student_id' => $student->id,
                'obtained_marks' => 75,
                'entered_by' => $data['admin']->id,
                'status' => 'submitted',
            ]);
        }
    }

    $service = new ResultCalculationService();

    // Run once
    $service->calculateForExam($data['exam']);
    $count1 = Result::where('exam_id', $data['exam']->id)->count();
    $breakdownCount1 = ResultSubjectBreakdown::count();
    expect($count1)->toBe(2);

    // Run again — should not duplicate
    $service->calculateForExam($data['exam']);
    $count2 = Result::where('exam_id', $data['exam']->id)->count();
    $breakdownCount2 = ResultSubjectBreakdown::count();
    expect($count2)->toBe(2);
    expect($breakdownCount2)->toBe($breakdownCount1);
});

it('prevents publish unless all subjects have submitted marks', function () {
    $data = createExamWithMarks(2);
    actingAs($data['admin']);

    // Only create marks for student 1, all 3 subjects
    foreach ($data['examSubjects'] as $es) {
        Mark::create([
            'exam_subject_id' => $es->id,
            'student_id' => $data['students'][0]->id,
            'obtained_marks' => 80,
            'entered_by' => $data['admin']->id,
            'status' => 'submitted',
        ]);
    }

    // Student 2 missing marks entirely

    $service = new ResultCalculationService();
    expect($service->canPublish($data['exam']))->toBeFalse();
});

it('allows publish when all subjects have submitted marks', function () {
    $data = createExamWithMarks(2);
    actingAs($data['admin']);

    foreach ($data['students'] as $student) {
        foreach ($data['examSubjects'] as $es) {
            Mark::create([
                'exam_subject_id' => $es->id,
                'student_id' => $student->id,
                'obtained_marks' => 80,
                'entered_by' => $data['admin']->id,
                'status' => 'submitted',
            ]);
        }
    }

    $service = new ResultCalculationService();
    expect($service->canPublish($data['exam']))->toBeTrue();
});

it('institute-admin can publish results via livewire', function () {
    $data = createExamWithMarks(2);

    foreach ($data['students'] as $student) {
        foreach ($data['examSubjects'] as $es) {
            Mark::create([
                'exam_subject_id' => $es->id,
                'student_id' => $student->id,
                'obtained_marks' => 80,
                'entered_by' => $data['admin']->id,
                'status' => 'submitted',
            ]);
        }
    }

    actingAs($data['admin']);

    \Livewire\Livewire::test(\App\Http\Livewire\ExamList::class)
        ->call('publishResults', $data['exam']->id);

    $data['exam']->refresh();
    expect($data['exam']->status)->toBe('published');

    // Results should be published too
    $results = Result::where('exam_id', $data['exam']->id)->get();
    expect($results->every(fn ($r) => $r->status === 'published'))->toBeTrue();
});

it('teacher cannot publish results', function () {
    $data = createExamWithMarks(1);

    foreach ($data['examSubjects'] as $es) {
        Mark::create([
            'exam_subject_id' => $es->id,
            'student_id' => $data['students'][0]->id,
            'obtained_marks' => 80,
            'entered_by' => $data['admin']->id,
            'status' => 'submitted',
        ]);
    }

    $teacher = User::create([
        'name' => 'Teacher',
        'email' => 'teacher@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $data['institute']->id,
    ])->assignRole('teacher');

    actingAs($teacher);

    \Livewire\Livewire::test(\App\Http\Livewire\ExamList::class)
        ->call('publishResults', $data['exam']->id)
        ->assertForbidden();
});

it('prevents tenant A from seeing tenant B results', function () {
    // Institute A
    $instituteA = Institute::create(['name' => 'A', 'slug' => 'inst-a', 'email' => 'a@test.com', 'is_active' => true]);
    $yearA = AcademicYear::create(['institute_id' => $instituteA->id, 'name' => '2026', 'is_current' => true]);
    $classA = ClassModel::create(['institute_id' => $instituteA->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $yearA->id]);
    $sectionA = Section::create(['institute_id' => $instituteA->id, 'class_id' => $classA->id, 'name' => 'A']);
    $subjectA = Subject::create(['institute_id' => $instituteA->id, 'name' => 'Math', 'code' => 'MATH']);
    $gradingSystemA = GradingSystem::create(['institute_id' => $instituteA->id, 'name' => 'BD', 'is_default' => true]);
    GradeRange::create(['grading_system_id' => $gradingSystemA->id, 'min_percent' => 0, 'max_percent' => 100, 'grade' => 'A+', 'gpa_point' => 5.0]);
    $examA = Exam::create(['institute_id' => $instituteA->id, 'academic_year_id' => $yearA->id, 'name' => 'Exam A', 'start_date' => '2026-06-01', 'end_date' => '2026-06-10', 'status' => 'completed']);
    $examSubjectA = ExamSubject::create(['exam_id' => $examA->id, 'class_id' => $classA->id, 'subject_id' => $subjectA->id, 'full_marks' => 100, 'pass_marks' => 33]);
    $studentA = Student::create(['institute_id' => $instituteA->id, 'student_id' => 'A-001', 'name' => 'Student A', 'gender' => 'male', 'class_id' => $classA->id, 'section_id' => $sectionA->id, 'academic_year_id' => $yearA->id, 'admission_date' => '2026-01-01', 'status' => 'active', 'roll' => 1]);

    // Institute B
    $instituteB = Institute::create(['name' => 'B', 'slug' => 'inst-b', 'email' => 'b@test.com', 'is_active' => true]);
    $yearB = AcademicYear::create(['institute_id' => $instituteB->id, 'name' => '2026', 'is_current' => true]);
    $classB = ClassModel::create(['institute_id' => $instituteB->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $yearB->id]);
    $sectionB = Section::create(['institute_id' => $instituteB->id, 'class_id' => $classB->id, 'name' => 'A']);
    $subjectB = Subject::create(['institute_id' => $instituteB->id, 'name' => 'Math', 'code' => 'MATH']);
    $gradingSystemB = GradingSystem::create(['institute_id' => $instituteB->id, 'name' => 'BD', 'is_default' => true]);
    GradeRange::create(['grading_system_id' => $gradingSystemB->id, 'min_percent' => 0, 'max_percent' => 100, 'grade' => 'A+', 'gpa_point' => 5.0]);
    $examB = Exam::create(['institute_id' => $instituteB->id, 'academic_year_id' => $yearB->id, 'name' => 'Exam B', 'start_date' => '2026-06-01', 'end_date' => '2026-06-10', 'status' => 'completed']);
    $examSubjectB = ExamSubject::create(['exam_id' => $examB->id, 'class_id' => $classB->id, 'subject_id' => $subjectB->id, 'full_marks' => 100, 'pass_marks' => 33]);
    $studentB = Student::create(['institute_id' => $instituteB->id, 'student_id' => 'B-001', 'name' => 'Student B', 'gender' => 'male', 'class_id' => $classB->id, 'section_id' => $sectionB->id, 'academic_year_id' => $yearB->id, 'admission_date' => '2026-01-01', 'status' => 'active', 'roll' => 1]);

    // Create marks for both
    $adminA = User::create(['name' => 'Admin A', 'email' => 'adminA@test.com', 'password' => Hash::make('password'), 'institute_id' => $instituteA->id])->assignRole('institute-admin');
    $adminB = User::create(['name' => 'Admin B', 'email' => 'adminB@test.com', 'password' => Hash::make('password'), 'institute_id' => $instituteB->id])->assignRole('institute-admin');

    Mark::create(['exam_subject_id' => $examSubjectA->id, 'student_id' => $studentA->id, 'obtained_marks' => 90, 'entered_by' => $adminA->id, 'status' => 'submitted']);
    Mark::create(['exam_subject_id' => $examSubjectB->id, 'student_id' => $studentB->id, 'obtained_marks' => 80, 'entered_by' => $adminB->id, 'status' => 'submitted']);

    // Calculate results for both — act as each admin for their own institute's grading system
    $service = new ResultCalculationService();
    actingAs($adminA);
    $service->calculateForExam($examA);
    actingAs($adminB);
    $service->calculateForExam($examB);

    // Login as admin A — should only see institute A results
    actingAs($adminA);

    $results = Result::all();
    expect($results)->toHaveCount(1);
    expect($results->first()->institute_id)->toBe($instituteA->id);

    // Unscoped query should find both
    $allResults = Result::withoutGlobalScope('institute')->get();
    expect($allResults)->toHaveCount(2);
});

it('student profile shows only published results', function () {
    $data = createExamWithMarks(1);
    actingAs($data['admin']);

    // Create marks
    foreach ($data['examSubjects'] as $es) {
        Mark::create([
            'exam_subject_id' => $es->id,
            'student_id' => $data['students'][0]->id,
            'obtained_marks' => 80,
            'entered_by' => $data['admin']->id,
            'status' => 'submitted',
        ]);
    }

    $service = new ResultCalculationService();
    $service->calculateForExam($data['exam']);

    // Publish
    $service->publishResults($data['exam']);
    $data['exam']->update(['status' => 'published']);

    actingAs($data['admin']);

    // Student profile should show published results
    $this->get(route('students.profile', $data['students'][0]->id))
        ->assertOk();
});
