<?php

namespace Database\Seeders;

use App\Models\GradeRange;
use App\Models\GradingSystem;
use App\Models\Institute;
use Illuminate\Database\Seeder;

class BdGradingSystemSeeder extends Seeder
{
    public function run(): void
    {
        $institutes = Institute::all();

        foreach ($institutes as $institute) {
            $gradingSystem = GradingSystem::create([
                'institute_id' => $institute->id,
                'name' => 'Bangladesh Grading System',
                'is_default' => true,
            ]);

            $ranges = [
                ['min_percent' => 80, 'max_percent' => 100, 'grade' => 'A+', 'gpa_point' => 5.0],
                ['min_percent' => 70, 'max_percent' => 79.99, 'grade' => 'A', 'gpa_point' => 4.0],
                ['min_percent' => 60, 'max_percent' => 69.99, 'grade' => 'A-', 'gpa_point' => 3.5],
                ['min_percent' => 50, 'max_percent' => 59.99, 'grade' => 'B', 'gpa_point' => 3.0],
                ['min_percent' => 40, 'max_percent' => 49.99, 'grade' => 'C', 'gpa_point' => 2.0],
                ['min_percent' => 33, 'max_percent' => 39.99, 'grade' => 'D', 'gpa_point' => 1.0],
                ['min_percent' => 0, 'max_percent' => 32.99, 'grade' => 'F', 'gpa_point' => 0.0],
            ];

            foreach ($ranges as $range) {
                GradeRange::create(array_merge($range, [
                    'grading_system_id' => $gradingSystem->id,
                ]));
            }
        }
    }
}
