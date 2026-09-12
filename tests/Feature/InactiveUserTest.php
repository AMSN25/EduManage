<?php

use App\Models\Institute;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\post;

it('prevents inactive user from logging in', function () {
    $institute = Institute::create([
        'name' => 'Test School',
        'slug' => 'test-school',
        'email' => 'test@school.com',
        'is_active' => true,
    ]);

    User::create([
        'name' => 'Inactive User',
        'email' => 'inactive@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
        'is_active' => false,
    ]);

    $response = post('/login', [
        'email' => 'inactive@test.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $response->assertRedirect();

    // Verify user is not authenticated
    $this->assertGuest();
});

it('allows active user to log in', function () {
    $institute = Institute::create([
        'name' => 'Test School',
        'slug' => 'test-school',
        'email' => 'test@school.com',
        'is_active' => true,
    ]);

    User::create([
        'name' => 'Active User',
        'email' => 'active@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
        'is_active' => true,
    ]);

    $response = post('/login', [
        'email' => 'active@test.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});
