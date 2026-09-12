<?php

use App\Models\Institute;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;

it('allows institute-admin to list users in their institute', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    User::create([
        'name' => 'Teacher 1',
        'email' => 'teacher@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('teacher');

    actingAs($admin);

    \Livewire\Livewire::test(\App\Http\Livewire\UserManagement::class)
        ->assertStatus(200);
});

it('prevents institute-admin from assigning super-admin role', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    actingAs($admin);

    \Livewire\Livewire::test(\App\Http\Livewire\UserManagement::class)
        ->set('name', 'Bad User')
        ->set('email', 'bad@test.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('selectedRole', 'super-admin')
        ->call('createUser');

    $this->assertDatabaseMissing('users', ['email' => 'bad@test.com']);
});

it('allows institute-admin to create user with valid role', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    actingAs($admin);

    \Livewire\Livewire::test(\App\Http\Livewire\UserManagement::class)
        ->set('name', 'New Teacher')
        ->set('email', 'newteacher@test.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('selectedRole', 'teacher')
        ->call('createUser');

    $this->assertDatabaseHas('users', [
        'email' => 'newteacher@test.com',
        'institute_id' => $institute->id,
    ]);

    $user = User::where('email', 'newteacher@test.com')->first();
    expect($user->hasRole('teacher'))->toBeTrue();
});

it('prevents institute-admin from deactivating themselves', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    actingAs($admin);

    \Livewire\Livewire::test(\App\Http\Livewire\UserManagement::class)
        ->call('toggleActive', $admin->id);

    $admin->refresh();
    expect($admin->is_active)->toBeTrue();
});

it('allows institute-admin to deactivate other users', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    $teacher = User::create([
        'name' => 'Teacher',
        'email' => 'teacher@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
        'is_active' => true,
    ])->assignRole('teacher');

    actingAs($admin);

    \Livewire\Livewire::test(\App\Http\Livewire\UserManagement::class)
        ->call('toggleActive', $teacher->id);

    $teacher->refresh();
    expect($teacher->is_active)->toBeFalse();
});

it('prevents institute-admin from seeing users from other institutes', function () {
    $instA = Institute::create(['name' => 'A', 'slug' => 'a', 'email' => 'a@test.com', 'is_active' => true]);
    $instB = Institute::create(['name' => 'B', 'slug' => 'b', 'email' => 'b@test.com', 'is_active' => true]);

    $adminA = User::create([
        'name' => 'Admin A',
        'email' => 'adminA@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $instA->id,
    ])->assignRole('institute-admin');

    User::create([
        'name' => 'Teacher B',
        'email' => 'teacherB@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $instB->id,
    ])->assignRole('teacher');

    actingAs($adminA);

    \Livewire\Livewire::test(\App\Http\Livewire\UserManagement::class)
        ->assertSee('Admin A')
        ->assertDontSee('Teacher B');
});

it('allows user to update their own profile', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $user = User::create([
        'name' => 'Original Name',
        'email' => 'original@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('teacher');

    actingAs($user);

    \Livewire\Livewire::test(\App\Http\Livewire\UserProfile::class)
        ->set('name', 'Updated Name')
        ->set('email', 'updated@test.com')
        ->call('updateProfile');

    $user->refresh();
    expect($user->name)->toBe('Updated Name');
    expect($user->email)->toBe('updated@test.com');
});

it('allows user to change their password with correct current password', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $user = User::create([
        'name' => 'User',
        'email' => 'user@test.com',
        'password' => Hash::make('old-password'),
        'institute_id' => $institute->id,
    ])->assignRole('teacher');

    actingAs($user);

    \Livewire\Livewire::test(\App\Http\Livewire\UserProfile::class)
        ->set('current_password', 'old-password')
        ->set('new_password', 'new-password')
        ->set('new_password_confirmation', 'new-password')
        ->call('changePassword');

    $user->refresh();
    expect(Hash::check('new-password', $user->password))->toBeTrue();
});

it('rejects password change with incorrect current password', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $user = User::create([
        'name' => 'User',
        'email' => 'user@test.com',
        'password' => Hash::make('correct-password'),
        'institute_id' => $institute->id,
    ])->assignRole('teacher');

    actingAs($user);

    \Livewire\Livewire::test(\App\Http\Livewire\UserProfile::class)
        ->set('current_password', 'wrong-password')
        ->set('new_password', 'new-password')
        ->set('new_password_confirmation', 'new-password')
        ->call('changePassword');

    $user->refresh();
    expect(Hash::check('correct-password', $user->password))->toBeTrue();
    expect(Hash::check('new-password', $user->password))->toBeFalse();
});

it('super-admin role is excluded from available roles for institute users', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
    ])->assignRole('institute-admin');

    actingAs($admin);

    \Livewire\Livewire::test(\App\Http\Livewire\UserManagement::class)
        ->assertDontSee('super-admin');
});
