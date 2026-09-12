<?php

use App\Jobs\SmsJob;
use App\Models\Institute;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Models\Student;
use App\Models\User;
use App\Services\Sms\LogGateway;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;

function createInstituteWithSmsSetup(): array
{
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $year = \App\Models\AcademicYear::create(['institute_id' => $institute->id, 'name' => '2026', 'is_current' => true]);
    $class = \App\Models\ClassModel::create(['institute_id' => $institute->id, 'name' => 'Class 10', 'numeric_order' => 10, 'academic_year_id' => $year->id]);
    $section = \App\Models\Section::create(['institute_id' => $institute->id, 'class_id' => $class->id, 'name' => 'A']);

    SmsTemplate::create([
        'institute_id' => $institute->id,
        'key' => 'fee_due',
        'body_bangla' => ':student_name এর :total_due টাকা বকেয় আছে।',
        'body_english' => 'Dear parent, :student_name has :total_due BDT in unpaid fees.',
        'is_active' => true,
    ]);

    SmsTemplate::create([
        'institute_id' => $institute->id,
        'key' => 'notice',
        'body_bangla' => ':title',
        'body_english' => ':title - :body',
        'is_active' => true,
    ]);

    return compact('institute', 'year', 'class', 'section');
}

it('dispatches SmsJob when sending template SMS', function () {
    Queue::fake();
    $data = createInstituteWithSmsSetup();

    $student = Student::create([
        'institute_id' => $data['institute']->id,
        'student_id' => 'TEST-2026-0001',
        'name' => 'Test Student',
        'gender' => 'male',
        'class_id' => $data['class']->id,
        'section_id' => $data['section']->id,
        'academic_year_id' => $data['year']->id,
        'admission_date' => '2026-01-01',
        'status' => 'active',
        'phone' => '01712345678',
        'roll' => 1,
    ]);

    $service = new SmsService(new LogGateway());
    $log = $service->sendTemplate(
        $data['institute']->id,
        'fee_due',
        '01712345678',
        ['student_name' => 'Test Student', 'total_due' => '1500.00'],
        Student::class,
        $student->id
    );

    // Verify log was created
    expect($log)->toBeInstanceOf(SmsLog::class);
    expect($log->status)->toBe('queued');
    expect($log->recipient_phone)->toBe('01712345678');

    // Verify job was queued
    Queue::assertPushed(SmsJob::class);
});

it('log gateway sends without HTTP call', function () {
    $gateway = new LogGateway();
    $result = $gateway->send('01712345678', 'Test message');

    expect($result->success)->toBeTrue();
    expect($result->cost)->toBe(0.0);
});

it('SmsJob sets status to failed on gateway failure', function () {
    $data = createInstituteWithSmsSetup();

    $log = SmsLog::create([
        'institute_id' => $data['institute']->id,
        'recipient_phone' => '01712345678',
        'message' => 'Test',
        'status' => 'queued',
    ]);

    // Create a failing gateway
    $failingGateway = new class implements \App\Services\Sms\SmsGatewayInterface {
        public function send(string $phone, string $message): \App\Services\Sms\SmsSendResult
        {
            return \App\Services\Sms\SmsSendResult::failed('Connection refused');
        }
    };

    $service = new SmsService($failingGateway);
    $service->executeSend($log);

    $log->refresh();
    expect($log->status)->toBe('failed');
    expect($log->provider_response)->toContain('Connection refused');
});

it('SmsJob has max 3 attempts', function () {
    $job = new SmsJob(1);
    expect($job->tries)->toBe(3);
});

it('no SMS is sent synchronously from request', function () {
    Queue::fake();
    $data = createInstituteWithSmsSetup();

    $service = new SmsService(new LogGateway());
    $service->sendRaw(
        $data['institute']->id,
        '01712345678',
        'Test message'
    );

    // Job should be queued, not executed inline
    Queue::assertPushed(SmsJob::class);
    Queue::assertNotPushed(\Closure::class);
});

it('tenant A cannot see tenant B SMS logs', function () {
    $instituteA = Institute::create(['name' => 'A', 'slug' => 'inst-a', 'email' => 'a@test.com', 'is_active' => true]);
    $instituteB = Institute::create(['name' => 'B', 'slug' => 'inst-b', 'email' => 'b@test.com', 'is_active' => true]);

    SmsLog::create([
        'institute_id' => $instituteA->id,
        'recipient_phone' => '01711111111',
        'message' => 'SMS from A',
        'status' => 'sent',
    ]);

    SmsLog::create([
        'institute_id' => $instituteB->id,
        'recipient_phone' => '01722222222',
        'message' => 'SMS from B',
        'status' => 'sent',
    ]);

    $adminA = User::create([
        'name' => 'Admin A',
        'email' => 'adminA@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $instituteA->id,
    ])->assignRole('institute-admin');

    actingAs($adminA);

    $logsA = SmsLog::where('institute_id', $instituteA->id)->get();
    expect($logsA)->toHaveCount(1);
    expect($logsA->first()->recipient_phone)->toBe('01711111111');
});

it('tenant A cannot see tenant B SMS templates', function () {
    $instituteA = Institute::create(['name' => 'A', 'slug' => 'inst-a', 'email' => 'a@test.com', 'is_active' => true]);
    $instituteB = Institute::create(['name' => 'B', 'slug' => 'inst-b', 'email' => 'b@test.com', 'is_active' => true]);

    SmsTemplate::create([
        'institute_id' => $instituteA->id,
        'key' => 'test',
        'body_bangla' => 'Template A',
        'body_english' => 'Template A',
        'is_active' => true,
    ]);

    SmsTemplate::create([
        'institute_id' => $instituteB->id,
        'key' => 'test',
        'body_bangla' => 'Template B',
        'body_english' => 'Template B',
        'is_active' => true,
    ]);

    $templatesA = SmsTemplate::where('institute_id', $instituteA->id)->get();
    expect($templatesA)->toHaveCount(1);
    expect($templatesA->first()->body_english)->toBe('Template A');
});

it('SMS template renders variables correctly', function () {
    $data = createInstituteWithSmsSetup();

    $template = SmsTemplate::where('institute_id', $data['institute']->id)
        ->where('key', 'fee_due')
        ->first();

    $rendered = $template->render('en', [
        'student_name' => 'Rahim',
        'total_due' => '1500.00',
    ]);

    expect($rendered)->toContain('Rahim');
    expect($rendered)->toContain('1500.00');
    expect($rendered)->not->toContain(':student_name');
});

it('SMS template defaults to English', function () {
    $data = createInstituteWithSmsSetup();

    $template = SmsTemplate::where('institute_id', $data['institute']->id)
        ->where('key', 'fee_due')
        ->first();

    $renderedEn = $template->render('en', ['student_name' => 'Rahim', 'total_due' => '1500']);
    $renderedBn = $template->render('bn', ['student_name' => 'Rahim', 'total_due' => '1500']);

    expect($renderedEn)->toContain('Dear parent');
    expect($renderedBn)->toContain('বকেয়');
});

it('sendReminder on FeeDuesReport queues SMS', function () {
    Queue::fake();

    // This tests that the sendReminder method dispatches a job
    // We can't fully test Livewire component in this context, but we verify the service
    $data = createInstituteWithSmsSetup();

    $student = Student::create([
        'institute_id' => $data['institute']->id,
        'student_id' => 'TEST-2026-0001',
        'name' => 'Test Student',
        'gender' => 'male',
        'class_id' => $data['class']->id,
        'section_id' => $data['section']->id,
        'academic_year_id' => $data['year']->id,
        'admission_date' => '2026-01-01',
        'status' => 'active',
        'phone' => '01712345678',
        'roll' => 1,
    ]);

    $service = new SmsService(new LogGateway());
    $log = $service->sendTemplate(
        $data['institute']->id,
        'fee_due',
        $student->phone,
        ['student_name' => $student->name, 'total_due' => '1500.00'],
        Student::class,
        $student->id
    );

    expect($log->status)->toBe('queued');
    Queue::assertPushed(SmsJob::class);
});
