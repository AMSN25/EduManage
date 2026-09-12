<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Trial',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'max_students' => 50,
                'features' => ['attendance', 'exams', 'reports'],
                'is_active' => true,
            ],
            [
                'name' => 'Basic',
                'price_monthly' => 500,
                'price_yearly' => 5000,
                'max_students' => 100,
                'features' => ['attendance', 'exams', 'reports', 'sms'],
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'price_monthly' => 1500,
                'price_yearly' => 15000,
                'max_students' => 500,
                'features' => ['attendance', 'exams', 'reports', 'sms', 'bulk_sms', 'advanced_reports'],
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'price_monthly' => 3000,
                'price_yearly' => 30000,
                'max_students' => 99999,
                'features' => ['attendance', 'exams', 'reports', 'sms', 'bulk_sms', 'advanced_reports', 'api', 'custom_domain'],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
