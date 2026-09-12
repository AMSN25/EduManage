<?php

use App\Jobs\BiometricPunchResolutionJob;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\BiometricDevice;
use App\Models\BiometricPunch;
use App\Models\ClassModel;
use App\Models\DeviceUserMapping;
use App\Models\Institute;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;

function createBiometricTestData(array $overrides = []): array
{
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test-bio', 'email' => 'bio@test.com', 'is_active' => true]);
    $year = AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true, 'start_date' => '2026-01-01', 'end_date' => '2026-12-31']);
    $class = ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $section = Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin-bio@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    $student = Student::create([
        'institute_id' => $institute->id,
        'student_id' => 'BIO-2026-0001',
        'name' => 'Bio Student',
        'gender' => 'male',
        'class_id' => $class->id,
        'section_id' => $section->id,
        'academic_year_id' => $year->id,
        'admission_date' => '2026-01-01',
        'status' => 'active',
    ]);

    $device = BiometricDevice::create([
        'institute_id' => $institute->id,
        'serial_number' => 'SN-TEST-001',
        'name' => 'Front Gate',
        'model' => 'ZKTeco',
        'status' => $overrides['device_status'] ?? 'active',
    ]);

    $mapping = DeviceUserMapping::create([
        'institute_id' => $institute->id,
        'biometric_device_id' => $device->id,
        'device_user_id' => 101,
        'student_id' => $student->id,
    ]);

    return compact('institute', 'year', 'class', 'section', 'admin', 'student', 'device', 'mapping');
}

it('returns 404 for unregistered device serial', function () {
    $payload = [
        'SN' => 'UNKNOWN-SERIAL',
        'table' => [
            ['SN' => 'UNKNOWN-SERIAL', 'PIN' => 1, 'T' => now()->format('Y-m-d\TH:i:s'), 'ST' => '1'],
        ],
    ];

    $this->postJson('/api/adms/punch', $payload)
        ->assertStatus(404);
});

it('stores punch with device_pending status for pending device', function () {
    $data = createBiometricTestData(['device_status' => 'pending']);

    Queue::fake();

    $punchTime = now()->format('Y-m-d\TH:i:s');
    $payload = [
        'SN' => $data['device']->serial_number,
        'table' => [
            ['SN' => $data['device']->serial_number, 'PIN' => 101, 'T' => $punchTime, 'ST' => '1'],
        ],
    ];

    $this->postJson('/api/adms/punch', $payload)->assertOk();

    $punch = BiometricPunch::where('biometric_device_id', $data['device']->id)->first();
    expect($punch)->not->toBeNull();
    expect($punch->resolution_status)->toBe('pending');

    // Manually resolve to verify device_pending path
    $resolver = new \App\Services\BiometricAttendanceResolver();
    try {
        $resolver->resolvePunch($punch->fresh());
    } catch (\RuntimeException $e) {
        // Expected: device is pending
    }

    expect($punch->fresh()->resolution_status)->toBe('device_pending');
});

it('creates attendance record with source=device for active device punch', function () {
    $data = createBiometricTestData();

    $punchTime = now()->format('Y-m-d\TH:i:s');
    $payload = [
        'SN' => $data['device']->serial_number,
        'table' => [
            ['SN' => $data['device']->serial_number, 'PIN' => 101, 'T' => $punchTime, 'ST' => '1'],
        ],
    ];

    $this->postJson('/api/adms/punch', $payload)->assertOk();

    // Dispatch and run the job synchronously
    BiometricPunchResolutionJob::dispatchSync(
        BiometricPunch::pluck('id')->toArray()
    );

    $this->assertDatabaseHas('attendance_records', [
        'student_id' => $data['student']->id,
        'status' => 'present',
        'source' => 'device',
    ]);

    $punch = BiometricPunch::first();
    expect($punch->resolution_status)->toBe('resolved');
    expect($punch->resolved_at)->not->toBeNull();
});

it('does not overwrite manual attendance with device punch', function () {
    $data = createBiometricTestData();

    // Create manual attendance first
    $attendance = Attendance::create([
        'institute_id' => $data['institute']->id,
        'class_id' => $data['class']->id,
        'section_id' => $data['section']->id,
        'date' => now()->format('Y-m-d'),
        'taken_by' => $data['admin']->id,
        'academic_year_id' => $data['year']->id,
    ]);

    AttendanceRecord::create([
        'attendance_id' => $attendance->id,
        'student_id' => $data['student']->id,
        'status' => 'absent',
        'source' => 'manual',
    ]);

    // Now send a device punch
    $punchTime = now()->format('Y-m-d\TH:i:s');
    $payload = [
        'SN' => $data['device']->serial_number,
        'table' => [
            ['SN' => $data['device']->serial_number, 'PIN' => 101, 'T' => $punchTime, 'ST' => '1'],
        ],
    ];

    $this->postJson('/api/adms/punch', $payload)->assertOk();

    BiometricPunchResolutionJob::dispatchSync(
        BiometricPunch::pluck('id')->toArray()
    );

    // Manual record should remain unchanged
    $record = AttendanceRecord::where('student_id', $data['student']->id)->first();
    expect($record->status)->toBe('absent');
    expect($record->source)->toBe('manual');
});

it('does not change already-resolved status on second punch same day', function () {
    $data = createBiometricTestData();

    // First punch
    $punch1Time = now()->format('Y-m-d\TH:i:s');
    $payload1 = [
        'SN' => $data['device']->serial_number,
        'table' => [
            ['SN' => $data['device']->serial_number, 'PIN' => 101, 'T' => $punch1Time, 'ST' => '1'],
        ],
    ];

    $this->postJson('/api/adms/punch', $payload1)->assertOk();

    BiometricPunchResolutionJob::dispatchSync(
        BiometricPunch::pluck('id')->toArray()
    );

    $recordAfterFirst = AttendanceRecord::where('student_id', $data['student']->id)->first();
    expect($recordAfterFirst->status)->toBe('present');

    // Second punch later the same day
    $punch2Time = now()->addHours(4)->format('Y-m-d\TH:i:s');
    $payload2 = [
        'SN' => $data['device']->serial_number,
        'table' => [
            ['SN' => $data['device']->serial_number, 'PIN' => 101, 'T' => $punch2Time, 'ST' => '0'],
        ],
    ];

    $this->postJson('/api/adms/punch', $payload2)->assertOk();

    BiometricPunchResolutionJob::dispatchSync(
        BiometricPunch::where('resolution_status', 'pending')->pluck('id')->toArray()
    );

    // Status should still be 'present' from first punch, not overwritten
    $recordAfterSecond = AttendanceRecord::where('student_id', $data['student']->id)->first();
    expect($recordAfterSecond->status)->toBe('present');
    expect($recordAfterSecond->source)->toBe('device');
});

it('device management livewire renders', function () {
    $data = createBiometricTestData();
    actingAs($data['admin']);

    \Livewire\Livewire::test(\App\Http\Livewire\DeviceManagement::class)
        ->assertSee('Biometric Devices')
        ->assertSee($data['device']->serial_number);
});
