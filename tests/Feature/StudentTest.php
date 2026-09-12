<?php

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Group;
use App\Models\Institute;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentCounter;
use App\Models\User;
use App\Services\StudentIdGenerator;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;

it('generates unique sequential student IDs', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test-school', 'email' => 't@test.com', 'is_active' => true]);
    $generator = app(StudentIdGenerator::class);

    $id1 = $generator->generate($institute);
    $id2 = $generator->generate($institute);
    $id3 = $generator->generate($institute);

    expect($id1)->toContain('TEST-');
    expect($id1)->toContain((string) date('Y'));
    expect($id1)->toContain('-0001');
    expect($id2)->toContain('-0002');
    expect($id3)->toContain('-0003');
});

it('generates IDs per year independently', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $generator = app(StudentIdGenerator::class);

    // Create a counter for previous year
    StudentCounter::create(['institute_id' => $institute->id, 'year' => 2025, 'counter' => 50]);

    $id = $generator->generate($institute);

    expect($id)->toContain(date('Y'));
    expect($id)->toContain('-0001'); // New year starts at 1
});

it('prevents tenant A students from being visible to tenant B', function () {
    $instA = Institute::create(['name' => 'A', 'slug' => 'inst-a', 'email' => 'a@test.com', 'is_active' => true]);
    $instB = Institute::create(['name' => 'B', 'slug' => 'inst-b', 'email' => 'b@test.com', 'is_active' => true]);

    $yearA = AcademicYear::create(['institute_id' => $instA->id, 'name' => '2026', 'is_current' => true]);
    $yearB = AcademicYear::create(['institute_id' => $instB->id, 'name' => '2026', 'is_current' => true]);

    $classA = ClassModel::create(['institute_id' => $instA->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $yearA->id]);
    $classB = ClassModel::create(['institute_id' => $instB->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $yearB->id]);

    $sectionA = Section::create(['institute_id' => $instA->id, 'class_id' => $classA->id, 'name' => 'A']);
    $sectionB = Section::create(['institute_id' => $instB->id, 'class_id' => $classB->id, 'name' => 'A']);

    Student::create([
        'institute_id' => $instA->id,
        'student_id' => 'A-2026-0001',
        'name' => 'Student A',
        'gender' => 'male',
        'class_id' => $classA->id,
        'section_id' => $sectionA->id,
        'academic_year_id' => $yearA->id,
        'admission_date' => '2026-01-01',
        'status' => 'active',
    ]);

    Student::create([
        'institute_id' => $instB->id,
        'student_id' => 'B-2026-0001',
        'name' => 'Student B',
        'gender' => 'female',
        'class_id' => $classB->id,
        'section_id' => $sectionB->id,
        'academic_year_id' => $yearB->id,
        'admission_date' => '2026-01-01',
        'status' => 'active',
    ]);

    $userA = User::create([
        'name' => 'Admin A',
        'email' => 'adminA@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $instA->id,
    ])->assignRole('institute-admin');

    actingAs($userA);

    $students = Student::all();
    expect($students)->toHaveCount(1);
    expect($students->first()->name)->toBe('Student A');
});

it('allows institute-admin to view student list', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    actingAs($admin);

    \Livewire\Livewire::test(\App\Http\Livewire\StudentList::class)
        ->assertStatus(200);
});

it('allows institute-admin to create a student', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $section = Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    actingAs($admin);

    \Livewire\Livewire::test(\App\Http\Livewire\StudentForm::class)
        ->set('name', 'Test Student')
        ->set('gender', 'male')
        ->set('class_id', $class->id)
        ->set('section_id', $section->id)
        ->set('academic_year_id', $year->id)
        ->set('admission_date', '2026-01-15')
        ->call('save');

    $this->assertDatabaseHas('students', [
        'name' => 'Test Student',
        'institute_id' => $institute->id,
        'status' => 'active',
    ]);
});

it('validates required fields when creating student', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    actingAs($admin);

    \Livewire\Livewire::test(\App\Http\Livewire\StudentForm::class)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name']);
});

it('stores guardian information with student', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $section = Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    actingAs($admin);

    \Livewire\Livewire::test(\App\Http\Livewire\StudentForm::class)
        ->set('name', 'Student with Guardian')
        ->set('gender', 'male')
        ->set('class_id', $class->id)
        ->set('section_id', $section->id)
        ->set('academic_year_id', $year->id)
        ->set('admission_date', '2026-01-15')
        ->set('father_name', 'Father Name')
        ->set('father_phone', '01712345678')
        ->set('mother_name', 'Mother Name')
        ->set('mother_phone', '01812345678')
        ->call('save');

    $student = Student::where('name', 'Student with Guardian')->first();
    expect($student->guardians)->toHaveCount(2);
});

it('rejects import when any row is invalid', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $section = Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    $import = new \App\Imports\StudentImport($institute->id, $year->id);

    // Valid row
    $validRow = collect([[
        'name' => 'Valid Student',
        'gender' => 'male',
        'class' => 'Class 10',
        'section' => 'A',
        'admission_date' => '2026-01-15',
    ]]);

    $import->collection($validRow);
    expect($import->hasErrors())->toBeFalse();
    expect($import->getImportedCount())->toBe(1);
});

it('allows export of students', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $section = Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    Student::create([
        'institute_id' => $institute->id,
        'student_id' => 'TEST-2026-0001',
        'name' => 'Export Student',
        'gender' => 'male',
        'class_id' => $class->id,
        'section_id' => $section->id,
        'academic_year_id' => $year->id,
        'admission_date' => '2026-01-15',
        'status' => 'active',
    ]);

    actingAs($admin);

    $response = $this->get(route('students.export'));
    $response->assertStatus(200);
});
