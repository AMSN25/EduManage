<?php

use App\Models\Institute;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Models\AuditLog;
use App\Jobs\ExpireTrialSubscriptions;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->institute = Institute::create(['name' => 'Test Institute', 'slug' => 'test-inst', 'email' => 'test@inst.com', 'is_active' => true]);
    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole('super-admin');
    $this->instituteAdmin = User::factory()->create(['institute_id' => $this->institute->id]);
    $this->instituteAdmin->assignRole('institute-admin');
    $this->trialPlan = SubscriptionPlan::create([
        'name' => 'Free Trial',
        'price_monthly' => 0,
        'price_yearly' => 0,
        'max_students' => 50,
        'features' => ['attendance', 'exams', 'reports'],
        'is_active' => true,
    ]);
    $this->basicPlan = SubscriptionPlan::create([
        'name' => 'Basic',
        'price_monthly' => 500,
        'price_yearly' => 5000,
        'max_students' => 100,
        'features' => ['attendance', 'exams', 'reports', 'sms'],
        'is_active' => true,
    ]);
});

it('allows super admin to access dashboard', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('super-admin.dashboard'))
        ->assertOk();
});

it('returns 403 for institute admin on super admin dashboard', function () {
    $this->actingAs($this->instituteAdmin)
        ->get(route('super-admin.dashboard'))
        ->assertStatus(403);
});

it('returns 403 for regular user on super admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('teacher');

    $this->actingAs($user)
        ->get(route('super-admin.dashboard'))
        ->assertStatus(403);
});

it('allows super admin to view institutes list', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('super-admin.institutes'))
        ->assertOk();
});

it('allows super admin to view institute detail', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('super-admin.institute.detail', $this->institute))
        ->assertOk();
});

it('allows super admin to view institute payments', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('super-admin.institute.payments', $this->institute))
        ->assertOk();
});

it('returns 403 for institute admin on institute detail', function () {
    $this->actingAs($this->instituteAdmin)
        ->get(route('super-admin.institute.detail', $this->institute))
        ->assertStatus(403);
});

it('can update institute plan via livewire', function () {
    // Create a trial subscription
    $subscription = Subscription::create([
        'institute_id' => $this->institute->id,
        'subscription_plan_id' => $this->trialPlan->id,
        'status' => 'trial',
        'starts_at' => now(),
        'ends_at' => now()->addDays(30),
    ]);

    Livewire::actingAs($this->superAdmin)
        ->test(\App\Http\Livewire\SuperAdminInstitutes::class)
        ->call('startEdit', $this->institute->id)
        ->set('newPlanId', $this->basicPlan->id)
        ->set('newStatus', 'active')
        ->call('updateInstitute');

    $subscription->refresh();
    expect($subscription->status)->toBe('active');
    expect($subscription->subscription_plan_id)->toBe($this->basicPlan->id);
});

it('logs plan changes to audit_logs', function () {
    Subscription::create([
        'institute_id' => $this->institute->id,
        'subscription_plan_id' => $this->trialPlan->id,
        'status' => 'trial',
        'starts_at' => now(),
        'ends_at' => now()->addDays(30),
    ]);

    Livewire::actingAs($this->superAdmin)
        ->test(\App\Http\Livewire\SuperAdminInstitutes::class)
        ->call('startEdit', $this->institute->id)
        ->set('newPlanId', $this->basicPlan->id)
        ->set('newStatus', 'active')
        ->call('updateInstitute');

    $log = AuditLog::where('institute_id', $this->institute->id)
        ->where('action', 'super_admin.subscription_changed')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->user_id)->toBe($this->superAdmin->id);
    expect($log->changes['old_plan_id'])->not->toBe($this->basicPlan->id);
    expect($log->changes['new_plan_id'])->toBe($this->basicPlan->id);
});

it('extends subscription when extend_days is provided', function () {
    $subscription = Subscription::create([
        'institute_id' => $this->institute->id,
        'subscription_plan_id' => $this->basicPlan->id,
        'status' => 'active',
        'starts_at' => now()->subMonth(),
        'ends_at' => now()->addDays(10),
    ]);

    Livewire::actingAs($this->superAdmin)
        ->test(\App\Http\Livewire\SuperAdminInstitutes::class)
        ->call('startEdit', $this->institute->id)
        ->set('newPlanId', $this->basicPlan->id)
        ->set('newStatus', 'active')
        ->set('extendDays', '30')
        ->call('updateInstitute');

    $subscription->refresh();
    expect($subscription->ends_at->isAfter(now()->addDays(38)))->toBeTrue();
});

it('enforces max_students plan limit', function () {
    $plan = SubscriptionPlan::create([
        'name' => 'Limited',
        'price_monthly' => 500,
        'price_yearly' => 5000,
        'max_students' => 2,
        'features' => [],
        'is_active' => true,
    ]);

    Subscription::create([
        'institute_id' => $this->institute->id,
        'subscription_plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    // Create 2 students (at limit)
    $class = \App\Models\ClassModel::create([
        'name' => 'Class 1', 'institute_id' => $this->institute->id,
        'numeric_order' => 1, 'is_active' => true,
    ]);
    $section = \App\Models\Section::create([
        'name' => 'A', 'institute_id' => $this->institute->id, 'class_id' => $class->id,
    ]);
    $year = \App\Models\AcademicYear::create(['name' => '2026', 'institute_id' => $this->institute->id, 'is_current' => true]);
    \App\Models\Student::create([
        'institute_id' => $this->institute->id, 'class_id' => $class->id, 'section_id' => $section->id,
        'academic_year_id' => $year->id, 'student_id' => 'TST-2026-0001', 'name' => 'Student 1', 'gender' => 'male', 'status' => 'active',
        'admission_date' => now()->toDateString(),
    ]);
    \App\Models\Student::create([
        'institute_id' => $this->institute->id, 'class_id' => $class->id, 'section_id' => $section->id,
        'academic_year_id' => $year->id, 'student_id' => 'TST-2026-0002', 'name' => 'Student 2', 'gender' => 'male', 'status' => 'active',
        'admission_date' => now()->toDateString(),
    ]);

    $policy = new \App\Policies\PlanPolicy();
    $canAdd = $policy->canAddStudent($this->instituteAdmin, $this->institute);

    expect($canAdd)->toBeFalse();
});

it('allows adding students within plan limit', function () {
    Subscription::create([
        'institute_id' => $this->institute->id,
        'subscription_plan_id' => $this->basicPlan->id, // 100 students
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    // Create 1 student
    $class = \App\Models\ClassModel::create([
        'name' => 'Class 1', 'institute_id' => $this->institute->id,
        'numeric_order' => 1, 'is_active' => true,
    ]);
    $section = \App\Models\Section::create([
        'name' => 'A', 'institute_id' => $this->institute->id, 'class_id' => $class->id,
    ]);
    $year = \App\Models\AcademicYear::create(['name' => '2026', 'institute_id' => $this->institute->id, 'is_current' => true]);
    \App\Models\Student::create([
        'institute_id' => $this->institute->id, 'class_id' => $class->id, 'section_id' => $section->id,
        'academic_year_id' => $year->id, 'student_id' => 'TST-2026-0001', 'name' => 'Student 1', 'gender' => 'male', 'status' => 'active',
        'admission_date' => now()->toDateString(),
    ]);

    $policy = new \App\Policies\PlanPolicy();
    $canAdd = $policy->canAddStudent($this->instituteAdmin, $this->institute);

    expect($canAdd)->toBeTrue();
});

it('checks feature access based on plan', function () {
    Subscription::create([
        'institute_id' => $this->institute->id,
        'subscription_plan_id' => $this->trialPlan->id, // no sms feature
        'status' => 'trial',
        'starts_at' => now(),
        'ends_at' => now()->addDays(30),
    ]);

    $policy = new \App\Policies\PlanPolicy();
    expect($policy->canAccessFeature($this->instituteAdmin, $this->institute, 'sms'))->toBeFalse();
    expect($policy->canAccessFeature($this->instituteAdmin, $this->institute, 'attendance'))->toBeTrue();
});

it('denies feature access for expired subscription', function () {
    Subscription::create([
        'institute_id' => $this->institute->id,
        'subscription_plan_id' => $this->basicPlan->id,
        'status' => 'expired',
        'starts_at' => now()->subMonth(),
        'ends_at' => now()->subDays(5),
    ]);

    $policy = new \App\Policies\PlanPolicy();
    expect($policy->canAccessFeature($this->instituteAdmin, $this->institute, 'sms'))->toBeFalse();
});

it('returns correct student count info', function () {
    Subscription::create([
        'institute_id' => $this->institute->id,
        'subscription_plan_id' => $this->basicPlan->id, // 100 students
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    $policy = new \App\Policies\PlanPolicy();
    $info = $policy->studentCountInfo($this->institute);

    expect($info['current'])->toBe(0);
    expect($info['limit'])->toBe(100);
    expect($info['remaining'])->toBe(100);
    expect($info['is_full'])->toBeFalse();
});

it('expire trial subscriptions job works', function () {
    $expired = Subscription::create([
        'institute_id' => $this->institute->id,
        'subscription_plan_id' => $this->trialPlan->id,
        'status' => 'trial',
        'starts_at' => now()->subDays(60),
        'ends_at' => now()->subDays(30),
    ]);

    $active = Subscription::create([
        'institute_id' => $this->institute->id,
        'subscription_plan_id' => $this->basicPlan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    ExpireTrialSubscriptions::dispatchSync();

    $expired->refresh();
    expect($expired->status)->toBe('expired');
});

it('expire trial job deactivates institute with no active subscription', function () {
    $subscription = Subscription::create([
        'institute_id' => $this->institute->id,
        'subscription_plan_id' => $this->trialPlan->id,
        'status' => 'trial',
        'starts_at' => now()->subDays(60),
        'ends_at' => now()->subDays(30),
    ]);

    ExpireTrialSubscriptions::dispatchSync();

    $this->institute->refresh();
    expect($this->institute->is_active)->toBeFalse();
});

it('search filters institutes by name', function () {
    $other = Institute::create(['name' => 'Other School', 'slug' => 'other', 'email' => 'other@test.com', 'is_active' => true]);

    Livewire::actingAs($this->superAdmin)
        ->test(\App\Http\Livewire\SuperAdminInstitutes::class)
        ->set('search', $this->institute->name)
        ->assertSee($this->institute->name)
        ->assertDontSee($other->name);
});

it('filters institutes by status', function () {
    Subscription::create([
        'institute_id' => $this->institute->id,
        'subscription_plan_id' => $this->basicPlan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    Livewire::actingAs($this->superAdmin)
        ->test(\App\Http\Livewire\SuperAdminInstitutes::class)
        ->set('statusFilter', 'active')
        ->assertSee($this->institute->name);
});

it('creates new subscription when none exists', function () {
    Livewire::actingAs($this->superAdmin)
        ->test(\App\Http\Livewire\SuperAdminInstitutes::class)
        ->call('startEdit', $this->institute->id)
        ->set('newPlanId', $this->trialPlan->id)
        ->set('newStatus', 'trial')
        ->call('updateInstitute');

    $sub = Subscription::where('institute_id', $this->institute->id)->first();
    expect($sub)->not->toBeNull();
    expect($sub->status)->toBe('trial');
});

it('validates plan change input', function () {
    Livewire::actingAs($this->superAdmin)
        ->test(\App\Http\Livewire\SuperAdminInstitutes::class)
        ->call('startEdit', $this->institute->id)
        ->set('newPlanId', 99999)
        ->call('updateInstitute')
        ->assertHasErrors(['newPlanId']);
});

it('super admin dashboard shows stats', function () {
    Livewire::actingAs($this->superAdmin)
        ->test(\App\Http\Livewire\SuperAdminDashboard::class)
        ->assertSee('Total Institutes')
        ->assertSee('Revenue');
});

it('record payment component opens for institute', function () {
    Livewire::actingAs($this->superAdmin)
        ->test(\App\Http\Livewire\SuperAdminRecordPayment::class)
        ->call('openModal', $this->institute->id)
        ->assertSet('instituteId', $this->institute->id)
        ->assertSee($this->institute->name);
});

it('validates payment input', function () {
    Livewire::actingAs($this->superAdmin)
        ->test(\App\Http\Livewire\SuperAdminRecordPayment::class)
        ->call('openModal', $this->institute->id)
        ->set('amount', '')
        ->call('recordPayment')
        ->assertHasErrors(['amount']);
});

it('can record a payment successfully', function () {
    $subscription = Subscription::create([
        'institute_id' => $this->institute->id,
        'subscription_plan_id' => $this->basicPlan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    Livewire::actingAs($this->superAdmin)
        ->test(\App\Http\Livewire\SuperAdminRecordPayment::class)
        ->call('openModal', $this->institute->id)
        ->set('amount', '1000')
        ->set('paymentMethod', 'bkash')
        ->set('paidAt', now()->format('Y-m-d'))
        ->call('recordPayment');

    $payment = SubscriptionPayment::where('institute_id', $this->institute->id)->first();
    expect($payment)->not->toBeNull();
    expect($payment->amount)->toBe('1000.00');
    expect($payment->payment_method)->toBe('bkash');
    expect($payment->recorded_by)->toBe($this->superAdmin->id);

    $subscription->refresh();
    expect($subscription->status)->toBe('active');
});

it('payment logs to audit_logs', function () {
    Subscription::create([
        'institute_id' => $this->institute->id,
        'subscription_plan_id' => $this->basicPlan->id,
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
    ]);

    Livewire::actingAs($this->superAdmin)
        ->test(\App\Http\Livewire\SuperAdminRecordPayment::class)
        ->call('openModal', $this->institute->id)
        ->set('amount', '500')
        ->set('paymentMethod', 'manual')
        ->set('paidAt', now()->format('Y-m-d'))
        ->call('recordPayment');

    $log = AuditLog::where('action', 'super_admin.payment_recorded')->first();
    expect($log)->not->toBeNull();
    expect((float) $log->changes['amount'])->toBe(500.0);
});

it('institute admin cannot access super admin routes', function () {
    $routes = [
        route('super-admin.dashboard'),
        route('super-admin.institutes'),
        route('super-admin.institute.detail', $this->institute),
        route('super-admin.institute.payments', $this->institute),
    ];

    foreach ($routes as $url) {
        $this->actingAs($this->instituteAdmin)
            ->get($url)
            ->assertStatus(403);
    }
});
