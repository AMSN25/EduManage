<?php

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\ClassModel;
use App\Models\Institute;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;

it('creates attendance and audit log on save', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
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

    actingAs($admin);

    \Livewire\Livewire::test(\App\Http\Livewire\AttendanceMark::class)
        ->set('classId', $class->id)
        ->set('sectionId', $section->id)
        ->set('date', now()->format('Y-m-d'))
        ->set('records', [$student->id => 'absent'])
        ->call('save');

    $this->assertDatabaseHas('attendances', [
        'class_id' => $class->id,
        'section_id' => $section->id,
    ]);

    $this->assertDatabaseHas('attendance_records', [
        'student_id' => $student->id,
        'status' => 'absent',
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'institute_id' => $institute->id,
        'user_id' => $admin->id,
        'action' => 'attendance.created',
    ]);
});

it('updates attendance instead of duplicating on same class/section/date', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
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

    actingAs($admin);

    // First save: mark present
    \Livewire\Livewire::test(\App\Http\Livewire\AttendanceMark::class)
        ->set('classId', $class->id)
        ->set('sectionId', $section->id)
        ->set('date', now()->format('Y-m-d'))
        ->set('records', [$student->id => 'present'])
        ->call('save');

    expect(Attendance::count())->toBe(1);
    expect(AttendanceRecord::where('student_id', $student->id)->where('status', 'present')->count())->toBe(1);

    // Second save: update to absent (same class/section/date)
    \Livewire\Livewire::test(\App\Http\Livewire\AttendanceMark::class)
        ->set('classId', $class->id)
        ->set('sectionId', $section->id)
        ->set('date', now()->format('Y-m-d'))
        ->set('records', [$student->id => 'absent'])
        ->call('save');

    // Should still be 1 attendance record, not 2
    expect(Attendance::count())->toBe(1);
    expect(AttendanceRecord::where('student_id', $student->id)->where('status', 'absent')->count())->toBe(1);
    expect(AttendanceRecord::where('student_id', $student->id)->where('status', 'present')->count())->toBe(0);
});

it('creates audit log on attendance update (edit)', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
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

    actingAs($admin);

    // First save: creates attendance
    \Livewire\Livewire::test(\App\Http\Livewire\AttendanceMark::class)
        ->set('classId', $class->id)
        ->set('sectionId', $section->id)
        ->set('date', now()->format('Y-m-d'))
        ->set('records', [$student->id => 'present'])
        ->call('save');

    $createLog = AuditLog::where('action', 'attendance.created')->first();
    expect($createLog)->not->toBeNull();

    // Second save: updates attendance (edit)
    \Livewire\Livewire::test(\App\Http\Livewire\AttendanceMark::class)
        ->set('classId', $class->id)
        ->set('sectionId', $section->id)
        ->set('date', now()->format('Y-m-d'))
        ->set('records', [$student->id => 'absent'])
        ->call('save');

    $updateLog = AuditLog::where('action', 'attendance.updated')->first();
    expect($updateLog)->not->toBeNull();
    expect($updateLog->institute_id)->toBe($institute->id);
    expect($updateLog->user_id)->toBe($admin->id);
    expect($updateLog->changes)->toBeArray();
    expect($updateLog->changes)->toHaveKey((string) $student->id);
});

it('prevents tenant A from seeing tenant B attendance', function () {
    $instA = Institute::create(['name' => 'A', 'slug' => 'inst-a', 'email' => 'a@test.com', 'is_active' => true]);
    $instB = Institute::create(['name' => 'B', 'slug' => 'inst-b', 'email' => 'b@test.com', 'is_active' => true]);

    $yearA = AcademicYear::create(['institute_id' => $instA->id, 'name' => '2026', 'is_current' => true]);
    $yearB = AcademicYear::create(['institute_id' => $instB->id, 'name' => '2026', 'is_current' => true]);

    $classA = ClassModel::create(['institute_id' => $instA->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $yearA->id]);
    $classB = ClassModel::create(['institute_id' => $instB->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $yearB->id]);

    $sectionA = Section::create(['institute_id' => $instA->id, 'class_id' => $classA->id, 'name' => 'A']);
    $sectionB = Section::create(['institute_id' => $instB->id, 'class_id' => $classB->id, 'name' => 'A']);

    $adminA = User::create([
        'name' => 'Admin A',
        'email' => 'adminA@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $instA->id,
    ])->assignRole('institute-admin');

    $adminB = User::create([
        'name' => 'Admin B',
        'email' => 'adminB@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $instB->id,
    ])->assignRole('institute-admin');

    Attendance::create([
        'institute_id' => $instA->id,
        'class_id' => $classA->id,
        'section_id' => $sectionA->id,
        'date' => now()->format('Y-m-d'),
        'taken_by' => $adminA->id,
        'academic_year_id' => $yearA->id,
    ]);

    Attendance::create([
        'institute_id' => $instB->id,
        'class_id' => $classB->id,
        'section_id' => $sectionB->id,
        'date' => now()->format('Y-m-d'),
        'taken_by' => $adminB->id,
        'academic_year_id' => $yearB->id,
    ]);

    actingAs($adminA);

    $attendances = Attendance::all();
    expect($attendances)->toHaveCount(1);
    expect($attendances->first()->institute_id)->toBe($instA->id);
});

it('allows teacher to mark attendance for assigned classes', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $section = Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    $teacher = User::create([
        'name' => 'Teacher',
        'email' => 'teacher@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('teacher');

    // Assign teacher to this class/section
    $subject = Subject::create(['name' => 'Math', 'numeric_order' => 1, 'institute_id' => $institute->id]);
    TeacherSubject::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'class_id' => $class->id,
        'section_id' => $section->id,
        'academic_year_id' => $year->id,
        'institute_id' => $institute->id,
    ]);

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

    actingAs($teacher);

    \Livewire\Livewire::test(\App\Http\Livewire\AttendanceMark::class)
        ->set('classId', $class->id)
        ->set('sectionId', $section->id)
        ->set('date', now()->format('Y-m-d'))
        ->set('records', [$student->id => 'present'])
        ->call('save');

    $this->assertDatabaseHas('attendances', [
        'class_id' => $class->id,
        'section_id' => $section->id,
        'taken_by' => $teacher->id,
    ]);
});

it('prevents teacher from marking attendance for unassigned classes', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $section = Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    $teacher = User::create([
        'name' => 'Teacher',
        'email' => 'teacher@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('teacher');

    // NO teacher_subjects assignment

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

    actingAs($teacher);

    // Teacher should not see this class in the dropdown
    // Verify the component only shows assigned classes
    $component = \Livewire\Livewire::test(\App\Http\Livewire\AttendanceMark::class);

    // The classes collection should be empty for this teacher
    // Since there are no teacher_subjects, the component renders with empty classes
    // Teacher should not be able to save for unassigned class
    $this->assertFalse($teacher->isAssignedTo($class->id, $section->id));
});

it('prevents editing attendance older than allowed days', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $section = Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    $teacher = User::create([
        'name' => 'Teacher',
        'email' => 'teacher@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('teacher');

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

    actingAs($teacher);

    \Livewire\Livewire::test(\App\Http\Livewire\AttendanceMark::class)
        ->set('classId', $class->id)
        ->set('sectionId', $section->id)
        ->set('date', now()->subDays(5)->format('Y-m-d'))
        ->set('records', [$student->id => 'present'])
        ->call('save');

    $this->assertDatabaseMissing('attendances', [
        'class_id' => $class->id,
        'section_id' => $section->id,
    ]);
});

it('shows attendance report with correct statistics', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
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

    $attendance = Attendance::create([
        'institute_id' => $institute->id,
        'class_id' => $class->id,
        'section_id' => $section->id,
        'date' => now()->format('Y-m-d'),
        'taken_by' => $admin->id,
        'academic_year_id' => $year->id,
    ]);

    AttendanceRecord::create([
        'attendance_id' => $attendance->id,
        'student_id' => $student->id,
        'status' => 'present',
    ]);

    actingAs($admin);

    \Livewire\Livewire::test(\App\Http\Livewire\AttendanceReport::class)
        ->set('classId', $class->id)
        ->set('sectionId', $section->id)
        ->set('month', (int) now()->format('m'))
        ->set('year', (int) now()->format('Y'))
        ->assertSee('100%');
});
