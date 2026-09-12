<?php

namespace Database\Seeders;

use App\Models\Institute;
use App\Models\InstituteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles first
        $this->call(RolesSeeder::class);

        // Create a super-admin (no institute)
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@edumanagebd.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ])->assignRole('super-admin');

        // Create demo institute
        $institute = Institute::create([
            'name' => 'Demo School',
            'slug' => 'demo-school',
            'email' => 'demo@school.com',
            'phone' => '+8801712345678',
            'address' => 'Dhaka, Bangladesh',
            'is_active' => true,
            'trial_ends_at' => now()->addDays(30),
        ]);

        InstituteSetting::create([
            'institute_id' => $institute->id,
            'default_language' => 'bn',
            'currency' => 'BDT',
            'current_academic_year' => '2026',
        ]);

        User::create([
            'name' => 'Institute Admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            'institute_id' => $institute->id,
            'is_active' => true,
        ])->assignRole('institute-admin');

        // Seed demo academic structure (local/testing only)
        $this->call(DemoAcademicStructureSeeder::class);

        // Seed BD grading system for all institutes
        $this->call(BdGradingSystemSeeder::class);

        // Seed SMS templates for all institutes
        $this->call(SmsTemplateSeeder::class);

        // Seed subscription plans
        $this->call(SubscriptionPlanSeeder::class);

        // Create default trial subscription for demo institute
        $trialPlan = \App\Models\SubscriptionPlan::where('name', 'Free Trial')->first();
        if ($trialPlan && $institute) {
            \App\Models\Subscription::create([
                'institute_id' => $institute->id,
                'subscription_plan_id' => $trialPlan->id,
                'status' => 'trial',
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
                'auto_renew' => false,
            ]);
        }
    }
}
