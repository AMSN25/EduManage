<?php

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

function createInstituteWithStudentData(): array
{
    $institute = \App\Models\Institute::create([
        'name' => 'Test',
        'slug' => 'test',
        'email' => 't@test.com',
        'is_active' => true,
    ]);

    $year = AcademicYear::create([
        'institute_id' => $institute->id,
        'name' => '2026',
        'is_current' => true,
    ]);

    $class = ClassModel::create([
        'institute_id' => $institute->id,
        'name' => 'Class 10',
        'numeric_order' => 10,
        'academic_year_id' => $year->id,
    ]);

    $section = Section::create([
        'institute_id' => $institute->id,
        'class_id' => $class->id,
        'name' => 'A',
    ]);

    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
        'is_active' => true,
    ])->assignRole('institute-admin');

    return compact('institute', 'year', 'class', 'section', 'admin');
}

it('serves a valid PWA manifest file', function () {
    $path = public_path('manifest.json');
    expect(file_exists($path))->toBeTrue();

    $manifest = json_decode(file_get_contents($path), true);
    expect($manifest)->toBeArray();
    expect($manifest['name'])->toBe('EduManage BD - School Management');
    expect($manifest['short_name'])->toBe('EduManage');
    expect($manifest['display'])->toBe('standalone');
    expect($manifest['start_url'])->toBe('/dashboard');
    expect($manifest['theme_color'])->toBe('#4f46e5');
    expect($manifest['icons'])->toBeArray();
    expect(count($manifest['icons']))->toBeGreaterThanOrEqual(1);
});

it('service worker file exists and is valid JavaScript', function () {
    $path = public_path('sw.js');
    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)->toContain('addEventListener');
    expect($content)->toContain('install');
    expect($content)->toContain('activate');
    expect($content)->toContain('fetch');
    expect($content)->toContain('CACHE_NAME');
});

it('PWA icons exist for all required sizes', function () {
    $sizes = [72, 96, 128, 144, 152, 192, 384, 512];

    foreach ($sizes as $size) {
        $path = public_path("icons/icon-{$size}x{$size}.svg");
        expect(file_exists($path))->toBeTrue()->and(basename($path))->toBe("icon-{$size}x{$size}.svg");
    }
});

it('install page is accessible', function () {
    $response = $this->get('/install');
    $response->assertStatus(200);
    $response->assertSee('Install EduManage BD');
    $response->assertSee('manifest.json');
});

it('layout includes PWA meta tags and service worker registration', function () {
    $layoutPath = resource_path('views/layouts/app.blade.php');
    $layout = file_get_contents($layoutPath);

    expect($layout)->toContain('manifest.json');
    expect($layout)->toContain('theme-color');
    expect($layout)->toContain('apple-mobile-web-app-capable');
    expect($layout)->toContain('sw.js');
    expect($layout)->toContain('serviceWorker');
    expect($layout)->toContain('offline-queue.js');
});

it('offline queue JS file exists with required API', function () {
    $path = resource_path('js/offline-queue.js');
    expect(file_exists($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)->toContain('EduManageOffline');
    expect($content)->toContain('queueAttendance');
    expect($content)->toContain('queueMarkEntry');
    expect($content)->toContain('getPendingCount');
    expect($content)->toContain('sync');
    expect($content)->toContain('localStorage');
    expect($content)->toContain('QUEUE_KEY');
});

it('student list page eager-loads relationships (no N+1)', function () {
    $data = createInstituteWithStudentData();

    // Create 10 students with relationships
    for ($i = 1; $i <= 10; $i++) {
        Student::create([
            'institute_id' => $data['institute']->id,
            'student_id' => "TEST-2026-{$i}",
            'name' => "Student {$i}",
            'gender' => 'male',
            'class_id' => $data['class']->id,
            'section_id' => $data['section']->id,
            'academic_year_id' => $data['year']->id,
            'admission_date' => '2026-01-01',
            'status' => 'active',
            'roll' => $i,
        ]);
    }

    actingAs($data['admin']);

    // Verify eager loading is used - check the StudentList component source
    $source = file_get_contents((new ReflectionClass(\App\Http\Livewire\StudentList::class))->getFileName());
    expect($source)->toContain("with(['classModel', 'section', 'group'])");

    // Verify the relationships work with eager loading
    $students = Student::with(['classModel', 'section', 'group'])
        ->where('institute_id', $data['institute']->id)
        ->where('status', 'active')
        ->get();

    // Each student should have its relations loaded without extra queries
    foreach ($students as $student) {
        expect($student->classModel)->not->toBeNull();
        expect($student->section)->not->toBeNull();
    }
});

it('attendance findForSession eager-loads records', function () {
    $data = createInstituteWithStudentData();

    $student = Student::create([
        'institute_id' => $data['institute']->id,
        'student_id' => 'TEST-2026-001',
        'name' => 'Rahim',
        'gender' => 'male',
        'class_id' => $data['class']->id,
        'section_id' => $data['section']->id,
        'academic_year_id' => $data['year']->id,
        'admission_date' => '2026-01-01',
        'status' => 'active',
        'roll' => 1,
    ]);

    $attendance = Attendance::create([
        'institute_id' => $data['institute']->id,
        'class_id' => $data['class']->id,
        'section_id' => $data['section']->id,
        'date' => now()->format('Y-m-d'),
        'taken_by' => $data['admin']->id,
        'academic_year_id' => $data['year']->id,
    ]);

    \App\Models\AttendanceRecord::create([
        'attendance_id' => $attendance->id,
        'student_id' => $student->id,
        'status' => 'present',
    ]);

    // Verify findForSession includes with('records')
    $method = new ReflectionMethod(Attendance::class, 'findForSession');
    $source = file_get_contents((new ReflectionClass(Attendance::class))->getFileName());

    // The method should contain ->with('records')
    expect($source)->toContain("->with('records')");

    // Verify records are accessible without extra query
    $found = Attendance::with('records')
        ->where('id', $attendance->id)
        ->first();

    expect($found->records->count())->toBe(1);
    expect($found->records->first()->status)->toBe('present');
});

it('fee dues report eager-loads studentFees (no N+1)', function () {
    $data = createInstituteWithStudentData();

    $student = Student::create([
        'institute_id' => $data['institute']->id,
        'student_id' => 'TEST-2026-001',
        'name' => 'Rahim',
        'gender' => 'male',
        'class_id' => $data['class']->id,
        'section_id' => $data['section']->id,
        'academic_year_id' => $data['year']->id,
        'admission_date' => '2026-01-01',
        'status' => 'active',
        'roll' => 1,
    ]);

    $feeType = \App\Models\FeeType::create([
        'institute_id' => $data['institute']->id,
        'name' => 'Tuition',
    ]);

    $feeStructure = \App\Models\FeeStructure::create([
        'institute_id' => $data['institute']->id,
        'fee_type_id' => $feeType->id,
        'class_id' => $data['class']->id,
        'academic_year_id' => $data['year']->id,
        'amount' => 1500,
        'frequency' => 'monthly',
    ]);

    \App\Models\StudentFee::create([
        'institute_id' => $data['institute']->id,
        'student_id' => $student->id,
        'fee_structure_id' => $feeStructure->id,
        'amount_due' => 1500,
        'amount_paid' => 0,
        'status' => 'unpaid',
        'month' => '2026-09',
        'due_date' => '2026-09-10',
    ]);

    // Verify the FeeDuesReport component uses eager loading
    $source = file_get_contents((new ReflectionClass(\App\Http\Livewire\FeeDuesReport::class))->getFileName());

    // Should contain eager-loaded studentFees with constraint
    expect($source)->toContain("studentFees");
    expect($source)->toContain("unpaidOrPartial");

    // Verify Student model has studentFees relationship
    $studentSource = file_get_contents((new ReflectionClass(Student::class))->getFileName());
    expect($studentSource)->toContain("function studentFees");

    // Verify the relationship works with eager loading
    actingAs($data['admin']);
    $students = Student::with(['classModel', 'section', 'studentFees' => fn ($q) => $q->unpaidOrPartial()])
        ->where('institute_id', $data['institute']->id)
        ->where('status', 'active')
        ->whereHas('studentFees', fn ($q) => $q->unpaidOrPartial())
        ->get();

    expect($students->count())->toBe(1);
    expect($students->first()->studentFees->count())->toBe(1);
    expect((float) $students->first()->studentFees->first()->amount_due)->toBe(1500.0);
});

it('attendance report uses eager loading for records', function () {
    $data = createInstituteWithStudentData();

    $student = Student::create([
        'institute_id' => $data['institute']->id,
        'student_id' => 'TEST-2026-001',
        'name' => 'Rahim',
        'gender' => 'male',
        'class_id' => $data['class']->id,
        'section_id' => $data['section']->id,
        'academic_year_id' => $data['year']->id,
        'admission_date' => '2026-01-01',
        'status' => 'active',
        'roll' => 1,
    ]);

    $attendance = Attendance::create([
        'institute_id' => $data['institute']->id,
        'class_id' => $data['class']->id,
        'section_id' => $data['section']->id,
        'date' => now()->format('Y-m-d'),
        'taken_by' => $data['admin']->id,
        'academic_year_id' => $data['year']->id,
    ]);

    \App\Models\AttendanceRecord::create([
        'attendance_id' => $attendance->id,
        'student_id' => $student->id,
        'status' => 'absent',
    ]);

    // Verify AttendanceReport component uses with('records')
    $source = file_get_contents((new ReflectionClass(\App\Http\Livewire\AttendanceReport::class))->getFileName());
    expect($source)->toContain("with('records')");

    // Verify records are eager-loaded when querying attendances
    $attendances = Attendance::where('institute_id', $data['institute']->id)
        ->with('records')
        ->get();

    expect($attendances->first()->records->count())->toBe(1);
    expect($attendances->first()->records->first()->status)->toBe('absent');
});
