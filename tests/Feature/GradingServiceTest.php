<?php

use App\Models\GradeRange;
use App\Models\GradingSystem;
use App\Models\Institute;
use App\Services\GradingService;

it('returns A+ for 80 percent (BD grading)', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);

    $gradingSystem = GradingSystem::create([
        'institute_id' => $institute->id,
        'name' => 'BD Grading',
        'is_default' => true,
    ]);

    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 80, 'max_percent' => 100, 'grade' => 'A+', 'gpa_point' => 5.0]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 70, 'max_percent' => 79.99, 'grade' => 'A', 'gpa_point' => 4.0]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 60, 'max_percent' => 69.99, 'grade' => 'A-', 'gpa_point' => 3.5]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 50, 'max_percent' => 59.99, 'grade' => 'B', 'gpa_point' => 3.0]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 40, 'max_percent' => 49.99, 'grade' => 'C', 'gpa_point' => 2.0]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 33, 'max_percent' => 39.99, 'grade' => 'D', 'gpa_point' => 1.0]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 0, 'max_percent' => 32.99, 'grade' => 'F', 'gpa_point' => 0.0]);

    $service = new GradingService();

    // Test exact boundary: 80% = A+
    $result = $service->calculate(80, 100, $gradingSystem);
    expect($result['grade'])->toBe('A+');
    expect($result['gpa_point'])->toBe(5.0);

    // Test just below: 79.99% = A
    $result = $service->calculate(79.99, 100, $gradingSystem);
    expect($result['grade'])->toBe('A');
    expect($result['gpa_point'])->toBe(4.0);

    // Test 70% = A
    $result = $service->calculate(70, 100, $gradingSystem);
    expect($result['grade'])->toBe('A');
    expect($result['gpa_point'])->toBe(4.0);

    // Test 69.99% = A-
    $result = $service->calculate(69.99, 100, $gradingSystem);
    expect($result['grade'])->toBe('A-');
    expect($result['gpa_point'])->toBe(3.5);

    // Test 60% = A-
    $result = $service->calculate(60, 100, $gradingSystem);
    expect($result['grade'])->toBe('A-');
    expect($result['gpa_point'])->toBe(3.5);

    // Test 59.99% = B
    $result = $service->calculate(59.99, 100, $gradingSystem);
    expect($result['grade'])->toBe('B');
    expect($result['gpa_point'])->toBe(3.0);

    // Test 50% = B
    $result = $service->calculate(50, 100, $gradingSystem);
    expect($result['grade'])->toBe('B');
    expect($result['gpa_point'])->toBe(3.0);

    // Test 49.99% = C
    $result = $service->calculate(49.99, 100, $gradingSystem);
    expect($result['grade'])->toBe('C');
    expect($result['gpa_point'])->toBe(2.0);

    // Test 40% = C
    $result = $service->calculate(40, 100, $gradingSystem);
    expect($result['grade'])->toBe('C');
    expect($result['gpa_point'])->toBe(2.0);

    // Test 39.99% = D
    $result = $service->calculate(39.99, 100, $gradingSystem);
    expect($result['grade'])->toBe('D');
    expect($result['gpa_point'])->toBe(1.0);

    // Test 33% = D
    $result = $service->calculate(33, 100, $gradingSystem);
    expect($result['grade'])->toBe('D');
    expect($result['gpa_point'])->toBe(1.0);

    // Test 32.99% = F
    $result = $service->calculate(32.99, 100, $gradingSystem);
    expect($result['grade'])->toBe('F');
    expect($result['gpa_point'])->toBe(0.0);

    // Test 0% = F
    $result = $service->calculate(0, 100, $gradingSystem);
    expect($result['grade'])->toBe('F');
    expect($result['gpa_point'])->toBe(0.0);
});

it('calculates GPA correctly for multiple subjects', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);

    $gradingSystem = GradingSystem::create([
        'institute_id' => $institute->id,
        'name' => 'BD Grading',
        'is_default' => true,
    ]);

    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 80, 'max_percent' => 100, 'grade' => 'A+', 'gpa_point' => 5.0]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 70, 'max_percent' => 79.99, 'grade' => 'A', 'gpa_point' => 4.0]);
    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 0, 'max_percent' => 32.99, 'grade' => 'F', 'gpa_point' => 0.0]);

    $service = new GradingService();

    // Subject 1: 90/100 = 90% = A+ (5.0)
    // Subject 2: 75/100 = 75% = A (4.0)
    // GPA = (5.0 + 4.0) / 2 = 4.5
    $result = $service->calculateGpa([
        1 => ['obtained_marks' => 90, 'full_marks' => 100],
        2 => ['obtained_marks' => 75, 'full_marks' => 100],
    ], $gradingSystem);

    expect($result['gpa'])->toBe(4.5);
    expect($result['subjects'][1]['grade'])->toBe('A+');
    expect($result['subjects'][2]['grade'])->toBe('A');
});

it('returns F for zero full_marks', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);

    $gradingSystem = GradingSystem::create([
        'institute_id' => $institute->id,
        'name' => 'BD Grading',
        'is_default' => true,
    ]);

    GradeRange::create(['grading_system_id' => $gradingSystem->id, 'min_percent' => 80, 'max_percent' => 100, 'grade' => 'A+', 'gpa_point' => 5.0]);

    $service = new GradingService();

    $result = $service->calculate(50, 0, $gradingSystem);
    expect($result['grade'])->toBe('F');
    expect($result['gpa_point'])->toBe(0.0);
});
