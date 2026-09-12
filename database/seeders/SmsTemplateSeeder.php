<?php

namespace Database\Seeders;

use App\Models\Institute;
use App\Models\SmsTemplate;
use Illuminate\Database\Seeder;

class SmsTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            'absent' => [
                'body_bangla' => ':student_name (:class/:section) আজ :date তারিখে অনুপস্থিত ছিলেন। অনুগ্রহ করে যোগাযোগ করুন।',
                'body_english' => 'Dear parent, :student_name (:class/:section) was absent on :date. Please contact the institute.',
            ],
            'fee_due' => [
                'body_bangla' => ':student_name এর :total_due টাকা বকেয় আছে। অনুগ্রহ করে বকেয় পরিশোধ করুন।',
                'body_english' => 'Dear parent, :student_name has :total_due BDT in unpaid fees. Please clear the dues.',
            ],
            'result_published' => [
                'body_bangla' => ':exam এর ফলাফল প্রকাশিত হয়েছে। দয়া করে চেক করুন।',
                'body_english' => 'Results for :exam have been published. Please check.',
            ],
            'notice' => [
                'body_bangla' => ':title - :body',
                'body_english' => ':title - :body',
            ],
        ];

        foreach (Institute::where('is_active', true)->get() as $institute) {
            foreach ($templates as $key => $texts) {
                SmsTemplate::updateOrCreate(
                    ['institute_id' => $institute->id, 'key' => $key],
                    [
                        'body_bangla' => $texts['body_bangla'],
                        'body_english' => $texts['body_english'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
