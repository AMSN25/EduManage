<?php

namespace App\Services;

use App\Models\Institute;
use App\Models\StudentCounter;
use Illuminate\Support\Facades\DB;

class StudentIdGenerator
{
    /**
     * Generate a unique student ID for the given institute.
     * Format: {INSTITUTE_CODE}-{YEAR}-{SEQUENTIAL_4DIGIT}
     * e.g. DEMO-2026-0001
     *
     * Uses a database lock to handle concurrent creation safely.
     */
    public function generate(Institute $institute): string
    {
        $year = (int) date('Y');
        $prefix = strtoupper(substr($institute->slug, 0, 5));

        return DB::transaction(function () use ($institute, $year, $prefix) {
            $counter = StudentCounter::lockForUpdate()
                ->firstOrCreate(
                    ['institute_id' => $institute->id, 'year' => $year],
                    ['counter' => 0]
                );

            $counter->increment('counter');
            $sequence = str_pad($counter->counter, 4, '0', STR_PAD_LEFT);

            return "{$prefix}-{$year}-{$sequence}";
        });
    }
}
