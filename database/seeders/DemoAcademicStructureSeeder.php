<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Group;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class DemoAcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local', 'testing')) {
            $this->command?->warn('DemoAcademicStructureSeeder skipped — not in local/testing environment.');
            return;
        }

        $instituteId = 1; // Demo School from DatabaseSeeder

        // Academic Year
        $year = AcademicYear::create([
            'institute_id' => $instituteId,
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        // Groups
        $science = Group::create(['institute_id' => $instituteId, 'name' => 'Science']);
        $humanities = Group::create(['institute_id' => $instituteId, 'name' => 'Humanities']);
        $business = Group::create(['institute_id' => $instituteId, 'name' => 'Business Studies']);

        // Subjects (Bangla curriculum standard)
        $subjects = [
            ['name' => 'Bangla', 'name_bangla' => 'বাংলা', 'code' => 'BAN', 'is_optional' => false],
            ['name' => 'English', 'name_bangla' => 'ইংরেজি', 'code' => 'ENG', 'is_optional' => false],
            ['name' => 'Mathematics', 'name_bangla' => 'গণিত', 'code' => 'MATH', 'is_optional' => false],
            ['name' => 'Science', 'name_bangla' => 'বিজ্ঞান', 'code' => 'SCI', 'is_optional' => false],
            ['name' => 'ICT', 'name_bangla' => 'তথ্য ও যোগাযোগ প্রযুক্তি', 'code' => 'ICT', 'is_optional' => false],
            ['name' => 'Religion', 'name_bangla' => 'ধর্ম', 'code' => 'REL', 'is_optional' => false],
            ['name' => 'Social Science', 'name_bangla' => 'সমাজবিজ্ঞান', 'code' => 'SOC', 'is_optional' => false],
            ['name' => 'Higher Math', 'name_bangla' => 'উচ্চতর গণিত', 'code' => 'HMC', 'is_optional' => true],
            ['name' => 'Chemistry', 'name_bangla' => 'রসায়ন', 'code' => 'CHE', 'is_optional' => true],
            ['name' => 'Physics', 'name_bangla' => 'পদার্থবিজ্ঞান', 'code' => 'PHY', 'is_optional' => true],
            ['name' => 'Biology', 'name_bangla' => 'জীববিজ্ঞান', 'code' => 'BIO', 'is_optional' => true],
            ['name' => 'Accounting', 'name_bangla' => 'হিসাববিজ্ঞান', 'code' => 'ACC', 'is_optional' => true],
            ['name' => 'Business Ent.', 'name_bangla' => 'ব্যবসায় উদ্যোগ', 'code' => 'BEE', 'is_optional' => true],
            ['name' => 'Geography', 'name_bangla' => 'ভূগোল', 'code' => 'GEO', 'is_optional' => true],
        ];

        $subjectModels = collect($subjects)->map(fn ($s) => Subject::create(array_merge(['institute_id' => $instituteId], $s)));

        // Classes 6-10 with sections A/B
        foreach (range(6, 10) as $classNum) {
            $class = ClassModel::create([
                'institute_id' => $instituteId,
                'academic_year_id' => $year->id,
                'name' => "Class {$classNum}",
                'numeric_order' => $classNum,
            ]);

            Section::create(['institute_id' => $instituteId, 'class_id' => $class->id, 'name' => 'A']);
            Section::create(['institute_id' => $instituteId, 'class_id' => $class->id, 'name' => 'B']);

            // Core subjects for all classes
            $coreSubjects = $subjectModels->filter(fn ($s) => !in_array($s->code, ['HMC', 'CHE', 'PHY', 'BIO', 'ACC', 'BEE', 'GEO']));
            $class->subjects()->attach($coreSubjects->pluck('id'));

            // Optional subjects for classes 9-10
            if ($classNum >= 9) {
                $optionalSubjects = $subjectModels->filter(fn ($s) => in_array($s->code, ['HMC', 'CHE', 'PHY', 'BIO', 'ACC', 'BEE', 'GEO']));
                $class->subjects()->attach($optionalSubjects->pluck('id'));
            }
        }
    }
}
