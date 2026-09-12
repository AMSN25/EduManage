<?php

use App\Models\Institute;
use App\Models\InstituteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;

it('prevents a user from Institute A from seeing records scoped to Institute B', function () {
    // Create two institutes
    $instituteA = Institute::create([
        'name' => 'Institute A',
        'slug' => 'institute-a',
        'email' => 'a@test.com',
        'is_active' => true,
    ]);

    $instituteB = Institute::create([
        'name' => 'Institute B',
        'slug' => 'institute-b',
        'email' => 'b@test.com',
        'is_active' => true,
    ]);

    InstituteSetting::create([
        'institute_id' => $instituteA->id,
        'default_language' => 'bn',
        'currency' => 'BDT',
    ]);

    InstituteSetting::create([
        'institute_id' => $instituteB->id,
        'default_language' => 'en',
        'currency' => 'USD',
    ]);

    // Create users for each institute
    $userA = User::create([
        'name' => 'User A',
        'email' => 'usera@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $instituteA->id,
        'is_active' => true,
    ]);

    $userB = User::create([
        'name' => 'User B',
        'email' => 'userb@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $instituteB->id,
        'is_active' => true,
    ]);

    // User A tries to query Institute B's settings — the global scope should block it
    actingAs($userA);

    // InstituteSetting uses BelongsToInstitute trait — global scope should filter
    $allSettings = InstituteSetting::all();

    // User A should only see their own institute's settings
    expect($allSettings)->toHaveCount(1);
    expect($allSettings->first()->institute_id)->toBe($instituteA->id);

    // Explicitly query without global scope to verify both exist
    $allSettingsUnscoped = InstituteSetting::withoutGlobalScope('institute')->get();
    expect($allSettingsUnscoped)->toHaveCount(2);
});

it('super-admin can see all records (no global scope applied)', function () {
    $instituteA = Institute::create([
        'name' => 'Institute A',
        'slug' => 'institute-a',
        'email' => 'a@test.com',
        'is_active' => true,
    ]);

    $instituteB = Institute::create([
        'name' => 'Institute B',
        'slug' => 'institute-b',
        'email' => 'b@test.com',
        'is_active' => true,
    ]);

    InstituteSetting::create([
        'institute_id' => $instituteA->id,
        'default_language' => 'bn',
        'currency' => 'BDT',
    ]);

    InstituteSetting::create([
        'institute_id' => $instituteB->id,
        'default_language' => 'en',
        'currency' => 'USD',
    ]);

    $superAdmin = User::create([
        'name' => 'Super Admin',
        'email' => 'super@test.com',
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    $superAdmin->assignRole('super-admin');

    actingAs($superAdmin);

    // Super admin should see all institute settings (global scope bypassed)
    $settings = InstituteSetting::all();
    expect($settings)->toHaveCount(2);
});
