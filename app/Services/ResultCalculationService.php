<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\GradingSystem;
use App\Models\Mark;
use App\Models\Result;
use App\Models\ResultSubjectBreakdown;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ResultCalculationService
{
    private GradingService $gradingService;

    public function __construct()
    {
        $this->gradingService = new GradingService();
    }

    /**
     * Calculate results for all students across all classes in an exam.
     * Idempotent: re-running for the same exam updates existing rows, never duplicates.
     *
     * Tie-break rule: Students with identical GPA AND percentage share the same
     * dense rank position. No alphabetical or roll-number tie-break — ties are
     * genuine ties. Dense ranking means positions are 1, 1, 2, 3 (no gaps after ties).
     */
    public function calculateForExam(Exam $exam): void
    {
        $gradingSystem = GradingSystem::getDefault();
        if (!$gradingSystem) {
            throw new \RuntimeException('No default grading system configured for this institute.');
        }

        DB::transaction(function () use ($exam, $gradingSystem) {
            $classIds = $exam->subjects->pluck('class_id')->unique();

            foreach ($classIds as $classId) {
                $this->calculateForClass($exam, (int) $classId, $gradingSystem);
            }
        });
    }

    /**
     * Calculate results for a single class within an exam.
     */
    private function calculateForClass(Exam $exam, int $classId, GradingSystem $gradingSystem): void
    {
        $students = Student::where('institute_id', $exam->institute_id)
            ->where('class_id', $classId)
            ->active()
            ->get();

        $examSubjects = $exam->subjects()
            ->where('class_id', $classId)
            ->with('subject')
            ->get();

        if ($examSubjects->isEmpty()) {
            return;
        }

        $studentResults = [];

        foreach ($students as $student) {
            $result = $this->calculateForStudent($exam, $student, $examSubjects, $gradingSystem);
            $studentResults[] = $result;
        }

        // Dense ranking for complete students only
        $completeStudents = collect($studentResults)->where('is_complete', true);
        $sorted = $completeStudents->sortByDesc('gpa')->sortByDesc('percentage')->values();

        $position = 0;
        $lastGpa = null;
        $lastPercentage = null;
        $rankedPositions = [];

        foreach ($sorted as $index => $entry) {
            $currentGpa = $entry['gpa'];
            $currentPercentage = $entry['percentage'];

            // Round to 2 decimals before comparing to guard against floating-point
            // representation differences when the same value is computed via
            // independent code paths (e.g. different subject combinations).
            // GradingService already rounds to 2 decimals; this re-rounding
            // explicitly documents the tolerance and is a no-op for identical floats.
            $currentGpaR = round($currentGpa, 2);
            $currentPctR = round($currentPercentage, 2);
            $lastGpaR = $lastGpa !== null ? round($lastGpa, 2) : null;
            $lastPctR = $lastPercentage !== null ? round($lastPercentage, 2) : null;

            if ($lastGpaR === null || $currentGpaR !== $lastGpaR || $currentPctR !== $lastPctR) {
                $position++;
            }

            $rankedPositions[$entry['student_id']] = $position;
            $lastGpa = $currentGpa;
            $lastPercentage = $currentPercentage;
        }

        // Upsert all results
        foreach ($studentResults as $entry) {
            $entry['position'] = $rankedPositions[$entry['student_id']] ?? null;

            $resultRow = Result::updateOrCreate(
                [
                    'institute_id' => $exam->institute_id,
                    'exam_id' => $exam->id,
                    'student_id' => $entry['student_id'],
                ],
                [
                    'total_obtained' => $entry['total_obtained'],
                    'total_full' => $entry['total_full'],
                    'percentage' => $entry['percentage'],
                    'gpa' => $entry['gpa'],
                    'grade' => $entry['grade'],
                    'position' => $entry['position'],
                    'status' => 'draft',
                ]
            );

            // Upsert subject breakdowns
            foreach ($entry['breakdowns'] as $breakdown) {
                ResultSubjectBreakdown::updateOrCreate(
                    [
                        'result_id' => $resultRow->id,
                        'subject_id' => $breakdown['subject_id'],
                    ],
                    [
                        'obtained' => $breakdown['obtained'],
                        'full' => $breakdown['full'],
                        'grade' => $breakdown['grade'],
                        'gpa_point' => $breakdown['gpa_point'],
                    ]
                );
            }
        }
    }

    /**
     * Calculate results for a single student across all exam subjects.
     */
    private function calculateForStudent(
        Exam $exam,
        Student $student,
        Collection $examSubjects,
        GradingSystem $gradingSystem
    ): array {
        $totalObtained = 0;
        $totalFull = 0;
        $breakdowns = [];
        $isComplete = true;

        foreach ($examSubjects as $es) {
            $mark = $es->marks()
                ->where('student_id', $student->id)
                ->whereIn('status', ['submitted', 'locked'])
                ->first();

            if (!$mark) {
                $isComplete = false;
                $breakdowns[] = [
                    'subject_id' => $es->subject_id,
                    'obtained' => 0,
                    'full' => $es->full_marks,
                    'grade' => 'F',
                    'gpa_point' => 0.0,
                ];
                continue;
            }

            $obtained = $mark->is_absent ? 0 : $mark->obtained_marks;
            $full = $es->full_marks;

            $subjectResult = $this->gradingService->calculate($obtained, $full, $gradingSystem);

            $totalObtained += $obtained;
            $totalFull += $full;

            $breakdowns[] = [
                'subject_id' => $es->subject_id,
                'obtained' => $obtained,
                'full' => $full,
                'grade' => $subjectResult['grade'],
                'gpa_point' => $subjectResult['gpa_point'],
            ];
        }

        if ($isComplete && $totalFull > 0) {
            $overallResult = $this->gradingService->calculate($totalObtained, $totalFull, $gradingSystem);
            $percentage = $overallResult['percentage'];
            $gpa = $overallResult['gpa_point'];
            $grade = $overallResult['grade'];
        } else {
            $percentage = $totalFull > 0 ? round(($totalObtained / $totalFull) * 100, 2) : 0;
            $gpa = 0.0;
            $grade = 'F';
        }

        return [
            'student_id' => $student->id,
            'total_obtained' => $totalObtained,
            'total_full' => $totalFull,
            'percentage' => $percentage,
            'gpa' => $gpa,
            'grade' => $grade,
            'is_complete' => $isComplete,
            'breakdowns' => $breakdowns,
        ];
    }

    /**
     * Check if all exam subjects have fully submitted marks for all active students.
     * Used to gate the Publish action.
     */
    public function canPublish(Exam $exam): bool
    {
        $classIds = $exam->subjects->pluck('class_id')->unique();

        foreach ($classIds as $classId) {
            $examSubjects = $exam->subjects()->where('class_id', $classId)->get();
            $students = Student::where('institute_id', $exam->institute_id)
                ->where('class_id', $classId)
                ->active()
                ->get();

            foreach ($students as $student) {
                foreach ($examSubjects as $es) {
                    $hasMark = $es->marks()
                        ->where('student_id', $student->id)
                        ->whereIn('status', ['submitted', 'locked'])
                        ->exists();

                    if (!$hasMark) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Publish all draft results for an exam.
     */
    public function publishResults(Exam $exam): void
    {
        Result::where('exam_id', $exam->id)
            ->where('institute_id', $exam->institute_id)
            ->where('status', 'draft')
            ->update(['status' => 'published']);
    }

    /**
     * Unpublish all published results for an exam, reverting to draft.
     */
    public function unpublishResults(Exam $exam): void
    {
        Result::where('exam_id', $exam->id)
            ->where('institute_id', $exam->institute_id)
            ->where('status', 'published')
            ->update(['status' => 'draft']);
    }
}
