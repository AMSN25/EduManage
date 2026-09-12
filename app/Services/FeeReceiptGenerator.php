<?php

namespace App\Services;

use App\Models\FeeCounter;
use App\Models\Institute;
use Illuminate\Support\Facades\DB;

class FeeReceiptGenerator
{
    /**
     * Generate a unique receipt number for the given institute.
     * Format: {INSTITUTE_CODE}-{YEAR}-{SEQUENTIAL_6DIGIT}
     * e.g. DEMO-2026-000001
     *
     * Uses a database lock to handle concurrent collection safely.
     */
    public function generate(Institute $institute): string
    {
        $year = (int) date('Y');
        $prefix = strtoupper(substr($institute->slug, 0, 5));

        return DB::transaction(function () use ($institute, $year, $prefix) {
            $counter = FeeCounter::lockForUpdate()
                ->firstOrCreate(
                    ['institute_id' => $institute->id, 'year' => $year],
                    ['counter' => 0]
                );

            $counter->increment('counter');
            $sequence = str_pad($counter->counter, 6, '0', STR_PAD_LEFT);

            return "{$prefix}-{$year}-{$sequence}";
        });
    }
}
