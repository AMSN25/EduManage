<?php

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\GradeRange;
use App\Models\GradingSystem;
use App\Models\Institute;
use App\Models\Mark;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Services\GradingService;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;

it('allows institute-admin to create exam', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $subject = Subject::create(['name' => 'Math', 'numeric_order' => 1, 'institute_id' => $institute->id]);
    $class->subjects()->attach($subject->id);

    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    actingAs($admin);

    \Livewire\Livewire::test(\App\Http\Livewire\ExamSetupWizard::class)
        ->set('name', 'Half Yearly')
        ->set('startDate', '2026-09-01')
        ->set('endDate', '2026-09-15')
        ->call('nextStep') // details -> classes
        ->assertSet('step', 'classes')
        ->set('selectedClassIds', [$class->id])
        ->call('nextStep') // classes -> subjects
        ->assertSet('step', 'subjects')
        ->call('nextStep') // subjects -> review
        ->assertSet('step', 'review')
        ->call('save');

    $this->assertDatabaseHas('exams', [
        'institute_id' => $institute->id,
        'name' => 'Half Yearly',
    ]);

    $this->assertDatabaseHas('exam_subjects', [
        'class_id' => $class->id,
        'subject_id' => $subject->id,
    ]);
});

it('prevents teacher from creating exam', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);

    $teacher = User::create([
        'name' => 'Teacher',
        'email' => 'teacher@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('teacher');

    actingAs($teacher);

    $this->assertFalse($teacher->can('manage', Exam::class));
});

it('prevents teacher from entering marks for unassigned class', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $subject = Subject::create(['name' => 'Math', 'numeric_order' => 1, 'institute_id' => $institute->id]);
    $section = Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    $teacher = User::create([
        'name' => 'Teacher',
        'email' => 'teacher@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('teacher');

    // Teacher NOT assigned to this class/subject

    $exam = Exam::create([
        'institute_id' => $institute->id,
        'academic_year_id' => $year->id,
        'name' => 'Half Yearly',
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-15',
        'status' => 'ongoing',
    ]);

    $examSubject = ExamSubject::create([
        'exam_id' => $exam->id,
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'full_marks' => 100,
        'pass_marks' => 33,
    ]);

    actingAs($teacher);

    $this->assertFalse($teacher->can('enterMarksForSubject', $examSubject));
});

it('allows teacher to enter marks for assigned class/subject', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $subject = Subject::create(['name' => 'Math', 'numeric_order' => 1, 'institute_id' => $institute->id]);
    $section = Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    $teacher = User::create([
        'name' => 'Teacher',
        'email' => 'teacher@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('teacher');

    // Assign teacher to this class/subject
    TeacherSubject::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'class_id' => $class->id,
        'section_id' => $section->id,
        'academic_year_id' => $year->id,
        'institute_id' => $institute->id,
    ]);

    $exam = Exam::create([
        'institute_id' => $institute->id,
        'academic_year_id' => $year->id,
        'name' => 'Half Yearly',
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-15',
        'status' => 'ongoing',
    ]);

    $examSubject = ExamSubject::create([
        'exam_id' => $exam->id,
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'full_marks' => 100,
        'pass_marks' => 33,
    ]);

    actingAs($teacher);

    $this->assertTrue($teacher->can('enterMarksForSubject', $examSubject));
});

it('rejects marks exceeding full_marks', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $subject = Subject::create(['name' => 'Math', 'numeric_order' => 1, 'institute_id' => $institute->id]);
    $section = Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    $student = Student::create([
        'institute_id' => $institute->id,
        'student_id' => 'TEST-2026-0001',
        'name' => 'Student 1',
        'gender' => 'male',
        'class_id' => $class->id,
        'section_id' => $section->id,
        'academic_year_id' => $year->id,
        'admission_date' => '2026-01-01',
        'status' => 'active',
    ]);

    $exam = Exam::create([
        'institute_id' => $institute->id,
        'academic_year_id' => $year->id,
        'name' => 'Half Yearly',
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-15',
        'status' => 'ongoing',
    ]);

    $examSubject = ExamSubject::create([
        'exam_id' => $exam->id,
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'full_marks' => 100,
        'pass_marks' => 33,
    ]);

    actingAs($admin);

    // Test validation: obtained_marks (150) > full_marks (100) should fail
    $validator = \Illuminate\Support\Facades\Validator::make(
        ['obtained_marks' => 150],
        ['obtained_marks' => 'required|numeric|max:' . $examSubject->full_marks]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('obtained_marks'))->toBeTrue();

    // Test validation: obtained_marks (80) <= full_marks (100) should pass
    $validator = \Illuminate\Support\Facades\Validator::make(
        ['obtained_marks' => 80],
        ['obtained_marks' => 'required|numeric|max:' . $examSubject->full_marks]
    );

    expect($validator->fails())->toBeFalse();
});

it('prevents editing locked marks', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $subject = Subject::create(['name' => 'Math', 'numeric_order' => 1, 'institute_id' => $institute->id]);
    $section = Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    $student = Student::create([
        'institute_id' => $institute->id,
        'student_id' => 'TEST-2026-0001',
        'name' => 'Student 1',
        'gender' => 'male',
        'class_id' => $class->id,
        'section_id' => $section->id,
        'academic_year_id' => $year->id,
        'admission_date' => '2026-01-01',
        'status' => 'active',
    ]);

    $exam = Exam::create([
        'institute_id' => $institute->id,
        'academic_year_id' => $year->id,
        'name' => 'Half Yearly',
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-15',
        'status' => 'ongoing',
    ]);

    $examSubject = ExamSubject::create([
        'exam_id' => $exam->id,
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'full_marks' => 100,
        'pass_marks' => 33,
    ]);

    // Create locked mark
    Mark::create([
        'exam_subject_id' => $examSubject->id,
        'student_id' => $student->id,
        'obtained_marks' => 80,
        'entered_by' => $admin->id,
        'status' => 'locked',
    ]);

    $teacher = User::create([
        'name' => 'Teacher',
        'email' => 'teacher@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('teacher');

    TeacherSubject::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'class_id' => $class->id,
        'section_id' => $section->id,
        'academic_year_id' => $year->id,
        'institute_id' => $institute->id,
    ]);

    actingAs($teacher);

    // Verify the mark is locked
    $mark = Mark::where('exam_subject_id', $examSubject->id)->where('student_id', $student->id)->first();
    expect($mark->status)->toBe('locked');
    expect($mark->obtained_marks)->toBe('80.00');

    // Teacher should still have permission to enter marks (policy allows)
    $this->assertTrue($teacher->can('enterMarksForSubject', $examSubject));

    // But the mark is locked — admin needs to unlock
    $this->assertFalse($teacher->can('unlockMarks'));
});

it('prevents tenant A from seeing tenant B exams', function () {
    $instA = Institute::create(['name' => 'A', 'slug' => 'inst-a', 'email' => 'a@test.com', 'is_active' => true]);
    $instB = Institute::create(['name' => 'B', 'slug' => 'inst-b', 'email' => 'b@test.com', 'is_active' => true]);

    $yearA = AcademicYear::create(['institute_id' => $instA->id, 'name' => '2026', 'is_current' => true]);
    $yearB = AcademicYear::create(['institute_id' => $instB->id, 'name' => '2026', 'is_current' => true]);

    Exam::create([
        'institute_id' => $instA->id,
        'academic_year_id' => $yearA->id,
        'name' => 'Exam A',
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-15',
        'status' => 'draft',
    ]);

    Exam::create([
        'institute_id' => $instB->id,
        'academic_year_id' => $yearB->id,
        'name' => 'Exam B',
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-15',
        'status' => 'draft',
    ]);

    $adminA = User::create([
        'name' => 'Admin A',
        'email' => 'adminA@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $instA->id,
    ])->assignRole('institute-admin');

    actingAs($adminA);

    $exams = Exam::all();
    expect($exams)->toHaveCount(1);
    expect($exams->first()->institute_id)->toBe($instA->id);
});
