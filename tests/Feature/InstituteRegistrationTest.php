<?php

use App\Models\Institute;
use App\Models\InstituteSetting;
use App\Models\User;

use function Pest\Laravel\post;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseCount;

it('registers institute + settings + admin user + assigns institute-admin role in a transaction', function () {
    $response = post('/register', [
        'institute_name' => 'Test School',
        'institute_email' => 'test@school.com',
        'institute_phone' => '+8801712345678',
        'institute_address' => 'Dhaka',
        'admin_name' => 'Test Admin',
        'admin_email' => 'admin@test.com',
        'admin_phone' => '+8801712345679',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));

    // Institute created
    assertDatabaseHas('institutes', [
        'name' => 'Test School',
        'email' => 'test@school.com',
        'is_active' => true,
    ]);

    // Settings created
    $institute = Institute::withoutGlobalScope('institute')->where('email', 'test@school.com')->first();
    assertDatabaseHas('institute_settings', [
        'institute_id' => $institute->id,
        'default_language' => 'bn',
        'currency' => 'BDT',
    ]);

    // Admin user created
    assertDatabaseHas('users', [
        'email' => 'admin@test.com',
        'institute_id' => $institute->id,
        'is_active' => true,
    ]);

    // Role assigned
    $admin = User::withoutGlobalScope('institute')->where('email', 'admin@test.com')->first();
    expect($admin->hasRole('institute-admin'))->toBeTrue();
});

it('rolls back all changes if any step fails during registration', function () {
    // Try to register with duplicate institute email to trigger failure
    Institute::create([
        'name' => 'Existing School',
        'slug' => 'existing-school',
        'email' => 'duplicate@school.com',
        'is_active' => true,
    ]);

    $response = post('/register', [
        'institute_name' => 'Should Fail School',
        'institute_email' => 'duplicate@school.com',
        'admin_name' => 'Should Fail Admin',
        'admin_email' => 'shouldfail@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('institute_email');

    // None of these should exist because of rollback
    assertDatabaseCount('institutes', 1);
    assertDatabaseCount('users', 0);
    assertDatabaseCount('institute_settings', 0);
});
