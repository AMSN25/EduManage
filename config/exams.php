<?php

return [
    'default_full_marks' => (int) env('EXAM_DEFAULT_FULL_MARKS', 100),
    'default_pass_marks' => (int) env('EXAM_DEFAULT_PASS_MARKS', 33),
    'default_cq_marks' => env('EXAM_DEFAULT_CQ_MARKS'),
    'default_mcq_marks' => env('EXAM_DEFAULT_MCQ_MARKS'),
    'default_practical_marks' => env('EXAM_DEFAULT_PRACTICAL_MARKS'),

    'pdf' => [
        'queue_threshold' => (int) env('PDF_QUEUE_THRESHOLD', 50),
        'storage_disk' => env('PDF_STORAGE_DISK', 'local'),
    ],

    'seat_allocation' => [
        'default_strategy' => env('SEAT_ALLOCATION_STRATEGY', 'sequential'),
    ],
];
