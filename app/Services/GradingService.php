<?php

namespace App\Services;

use App\Models\GradeRange;
use App\Models\GradingSystem;

class GradingService
{
    /**
     * Calculate grade and GPA point for a given percentage and grading system.
     *
     * @param float $obtainedMarks
     * @param float $fullMarks
     * @param GradingSystem $gradingSystem
     * @return array{grade: string, gpa_point: float, percentage: float}
     */
    public function calculate(float $obtainedMarks, float $fullMarks, GradingSystem $gradingSystem): array
    {
        if ($fullMarks <= 0) {
            return ['grade' => 'F', 'gpa_point' => 0.0, 'percentage' => 0.0];
        }

        $percentage = round(($obtainedMarks / $fullMarks) * 100, 2);

        $ranges = $gradingSystem->ranges()
            ->orderByDesc('min_percent')
            ->get();

        foreach ($ranges as $range) {
            if ($percentage >= $range->min_percent && $percentage <= $range->max_percent) {
                return [
                    'grade' => $range->grade,
                    'gpa_point' => (float) $range->gpa_point,
                    'percentage' => $percentage,
                ];
            }
        }

        // Below all ranges = F
        return ['grade' => 'F', 'gpa_point' => 0.0, 'percentage' => $percentage];
    }

    /**
     * Calculate GPA using the standard BD formula: average of all subject GPAs.
     *
     * @param array<int, array{obtained_marks: float, full_marks: float}> $subjectMarks
     * @param GradingSystem $gradingSystem
     * @return array{gpa: float, subjects: array}
     */
    public function calculateGpa(array $subjectMarks, GradingSystem $gradingSystem): array
    {
        $results = [];
        $totalGpa = 0;
        $count = 0;

        foreach ($subjectMarks as $subjectId => $marks) {
            $result = $this->calculate(
                $marks['obtained_marks'],
                $marks['full_marks'],
                $gradingSystem
            );

            $results[$subjectId] = $result;
            $totalGpa += $result['gpa_point'];
            $count++;
        }

        $gpa = $count > 0 ? round($totalGpa / $count, 2) : 0.0;

        return [
            'gpa' => $gpa,
            'subjects' => $results,
        ];
    }
}
