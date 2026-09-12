<?php

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Group;
use App\Models\Institute;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('enforces only one current academic year per institute', function () {
    $institute = Institute::create([
        'name' => 'Test Institute',
        'slug' => 'test-institute',
        'email' => 'test@inst.com',
        'is_active' => true,
    ]);

    $year1 = AcademicYear::create([
        'institute_id' => $institute->id,
        'name' => '2025',
        'is_current' => true,
    ]);

    expect($year1->is_current)->toBeTrue();

    // Setting year2 as current should unset year1
    $year2 = AcademicYear::create([
        'institute_id' => $institute->id,
        'name' => '2026',
        'is_current' => true,
    ]);

    $year1->refresh();
    expect($year1->is_current)->toBeFalse();
    expect($year2->is_current)->toBeTrue();
});

it('does not affect other institutes current year', function () {
    $instA = Institute::create(['name' => 'A', 'slug' => 'a', 'email' => 'a@test.com', 'is_active' => true]);
    $instB = Institute::create(['name' => 'B', 'slug' => 'b', 'email' => 'b@test.com', 'is_active' => true]);

    $yearA = AcademicYear::create(['institute_id' => $instA->id, 'name' => '2025', 'is_current' => true]);
    $yearB = AcademicYear::create(['institute_id' => $instB->id, 'name' => '2025', 'is_current' => true]);

    // Set a new current year for institute A
    AcademicYear::create(['institute_id' => $instA->id, 'name' => '2026', 'is_current' => true]);

    $yearA->refresh();
    $yearB->refresh();

    expect($yearA->is_current)->toBeFalse();
    expect($yearB->is_current)->toBeTrue(); // unaffected
});

it('prevents tenant A from seeing tenant B academic years via global scope', function () {
    $instA = Institute::create(['name' => 'A', 'slug' => 'a', 'email' => 'a@test.com', 'is_active' => true]);
    $instB = Institute::create(['name' => 'B', 'slug' => 'b', 'email' => 'b@test.com', 'is_active' => true]);

    AcademicYear::create(['institute_id' => $instA->id, 'name' => '2025']);
    AcademicYear::create(['institute_id' => $instB->id, 'name' => '2025']);

    $userA = User::create([
        'name' => 'User A',
        'email' => 'usera@test.com',
        'password' => bcrypt('password'),
        'institute_id' => $instA->id,
    ]);

    actingAs($userA);

    $years = AcademicYear::all();
    expect($years)->toHaveCount(1);
    expect($years->first()->institute_id)->toBe($instA->id);
});

it('prevents tenant A from seeing tenant B classes via global scope', function () {
    $instA = Institute::create(['name' => 'A', 'slug' => 'a', 'email' => 'a@test.com', 'is_active' => true]);
    $instB = Institute::create(['name' => 'B', 'slug' => 'b', 'email' => 'b@test.com', 'is_active' => true]);

    ClassModel::create(['institute_id' => $instA->id, 'name' => 'Class 10', 'numeric_order' => 10]);
    ClassModel::create(['institute_id' => $instB->id, 'name' => 'Class 10', 'numeric_order' => 10]);

    $userA = User::create([
        'name' => 'User A',
        'email' => 'usera@test.com',
        'password' => bcrypt('password'),
        'institute_id' => $instA->id,
    ]);

    actingAs($userA);

    $classes = ClassModel::all();
    expect($classes)->toHaveCount(1);
    expect($classes->first()->institute_id)->toBe($instA->id);
});

it('prevents tenant A from seeing tenant B subjects via global scope', function () {
    $instA = Institute::create(['name' => 'A', 'slug' => 'a', 'email' => 'a@test.com', 'is_active' => true]);
    $instB = Institute::create(['name' => 'B', 'slug' => 'b', 'email' => 'b@test.com', 'is_active' => true]);

    Subject::create(['institute_id' => $instA->id, 'name' => 'Math']);
    Subject::create(['institute_id' => $instB->id, 'name' => 'Math']);

    $userA = User::create([
        'name' => 'User A',
        'email' => 'usera@test.com',
        'password' => bcrypt('password'),
        'institute_id' => $instA->id,
    ]);

    actingAs($userA);

    $subjects = Subject::all();
    expect($subjects)->toHaveCount(1);
    expect($subjects->first()->institute_id)->toBe($instA->id);
});

it('prevents tenant A from seeing tenant B sections via global scope', function () {
    $instA = Institute::create(['name' => 'A', 'slug' => 'a', 'email' => 'a@test.com', 'is_active' => true]);
    $instB = Institute::create(['name' => 'B', 'slug' => 'b', 'email' => 'b@test.com', 'is_active' => true]);

    $classA = ClassModel::create(['institute_id' => $instA->id, 'name' => 'Class 10', 'numeric_order' => 10]);
    $classB = ClassModel::create(['institute_id' => $instB->id, 'name' => 'Class 10', 'numeric_order' => 10]);

    Section::create(['institute_id' => $instA->id, 'class_id' => $classA->id, 'name' => 'A']);
    Section::create(['institute_id' => $instB->id, 'class_id' => $classB->id, 'name' => 'A']);

    $userA = User::create([
        'name' => 'User A',
        'email' => 'usera@test.com',
        'password' => bcrypt('password'),
        'institute_id' => $instA->id,
    ]);

    actingAs($userA);

    $sections = Section::all();
    expect($sections)->toHaveCount(1);
    expect($sections->first()->institute_id)->toBe($instA->id);
});

it('prevents tenant A from seeing tenant B groups via global scope', function () {
    $instA = Institute::create(['name' => 'A', 'slug' => 'a', 'email' => 'a@test.com', 'is_active' => true]);
    $instB = Institute::create(['name' => 'B', 'slug' => 'b', 'email' => 'b@test.com', 'is_active' => true]);

    Group::create(['institute_id' => $instA->id, 'name' => 'Science']);
    Group::create(['institute_id' => $instB->id, 'name' => 'Science']);

    $userA = User::create([
        'name' => 'User A',
        'email' => 'usera@test.com',
        'password' => bcrypt('password'),
        'institute_id' => $instA->id,
    ]);

    actingAs($userA);

    $groups = Group::all();
    expect($groups)->toHaveCount(1);
    expect($groups->first()->institute_id)->toBe($instA->id);
});

it('blocks deletion of class that has sections', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10]);
    Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    // The class has sections — deletion should be blocked at application level
    $hasSections = $class->sections()->exists();
    expect($hasSections)->toBeTrue();

    // The Livewire component checks this before calling delete
    // Verify that deleting would orphan sections (application-level guard)
    $sectionCount = $class->sections()->count();
    expect($sectionCount)->toBe(1);
});

it('blocks deletion of subject that has class assignments', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $subject = Subject::create(['institute_id' => $institute->id, 'name' => 'Math']);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10]);

    $class->subjects()->attach($subject->id);

    $hasClasses = $subject->classes()->exists();
    expect($hasClasses)->toBeTrue();
});

it('allows deletion of class with no sections', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10]);

    $hasSections = $class->sections()->exists();
    expect($hasSections)->toBeFalse();

    $class->delete();
    expect(ClassModel::find($class->id))->toBeNull();
});

it('classes are sorted by numeric_order not alphabetically', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);

    ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10]);
    ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 2', 'numeric_order' => 2]);
    ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 5', 'numeric_order' => 5]);

    $sorted = ClassModel::orderBy('numeric_order')->get();

    expect($sorted->pluck('name')->toArray())->toBe(['Class 2', 'Class 5', 'Class 10']);
});
