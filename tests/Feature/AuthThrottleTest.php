<?php

use App\Models\Institute;
use App\Models\InstituteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('throttles repeated login attempts', function () {
    $institute = Institute::create(['name' => 'Test', 'slug' => 'test', 'email' => 't@test.com', 'is_active' => true]);
    InstituteSetting::create(['institute_id' => $institute->id, 'default_language' => 'en', 'currency' => 'BDT']);

    User::create([
        'name' => 'User',
        'email' => 'user@test.com',
        'password' => Hash::make('password'),
        'institute_id' => $institute->id,
        'is_active' => true,
    ]);

    // Hit the login endpoint 6 times (limit is 5 per minute)
    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', ['email' => 'user@test.com', 'password' => 'wrong'])
            ->assertRedirect();
    }

    // 6th attempt should be throttled (429)
    $this->post('/login', ['email' => 'user@test.com', 'password' => 'wrong'])
        ->assertStatus(429);
});

it('throttles repeated registration attempts', function () {
    // Use invalid data that fails validation so no user/session is created
    // and the throttle counter persists across requests
    for ($i = 0; $i < 3; $i++) {
        $this->post('/register', [
            'institute_name' => '',
            'institute_email' => '',
            'admin_name' => '',
            'admin_email' => '',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(); // 302 back with validation errors
    }

    // 4th attempt should be throttled (429)
    $this->post('/register', [
        'institute_name' => '',
        'institute_email' => '',
        'admin_name' => '',
        'admin_email' => '',
        'password' => '',
        'password_confirmation' => '',
    ])->assertStatus(429);
});
