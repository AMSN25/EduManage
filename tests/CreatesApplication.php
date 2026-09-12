<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

trait CreatesApplication
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles for every test
        $roles = ['super-admin', 'institute-admin', 'teacher', 'accountant', 'guardian'];
        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }
    }
}
