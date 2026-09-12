<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamRoom;
use App\Models\Institute;
use App\Models\Result;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfGenerationService
{
    /**
     * Generate a single student's admit card as PDF bytes.
     */
    public function generateAdmitCard(Student $student, Exam $exam, Institute $institute): string
    {
        $view = view('pdf.admit-card', [
            'student' => $student,
            'exam' => $exam,
            'institute' => $institute,
        ]);

        $pdf = Pdf::loadHTML($view->render())
            ->setPaper('a5', 'portrait')
            ->setWarnings(false);

        return $pdf->output();
    }

    /**
     * Generate bulk admit cards for a class (all students on separate pages).
     */
    public function generateBulkAdmitCards(int $classId, Exam $exam, Institute $institute): string
    {
        $students = Student::where('institute_id', $institute->id)
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->orderBy('roll')
            ->get();

        $view = view('pdf.admit-cards-bulk', [
            'students' => $students,
            'exam' => $exam,
            'institute' => $institute,
        ]);

        $pdf = Pdf::loadHTML($view->render())
            ->setPaper('a5', 'portrait')
            ->setWarnings(false);

        return $pdf->output();
    }

    /**
     * Generate seat plan PDF for a room (seating chart grid).
     */
    public function generateRoomSeatPlan(ExamRoom $room, Exam $exam, Institute $institute): string
    {
        $service = new SeatAllocationService();
        $chart = $service->getSeatingChart($room);

        $view = view('pdf.seat-plan-room', [
            'room' => $room,
            'exam' => $exam,
            'institute' => $institute,
            'chart' => $chart,
        ]);

        $pdf = Pdf::loadHTML($view->render())
            ->setPaper('a4', 'landscape')
            ->setWarnings(false);

        return $pdf->output();
    }

    /**
     * Generate seat slips for all students in a room.
     */
    public function generateSeatSlips(ExamRoom $room, Exam $exam, Institute $institute): string
    {
        $assignments = $room->assignments()
            ->with('student')
            ->orderBy('seat_no')
            ->get();

        $view = view('pdf.seat-slips', [
            'room' => $room,
            'exam' => $exam,
            'institute' => $institute,
            'assignments' => $assignments,
        ]);

        $pdf = Pdf::loadHTML($view->render())
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        return $pdf->output();
    }

    /**
     * Generate a single student's marksheet as PDF bytes.
     */
    public function generateMarksheet(Result $result, Institute $institute): string
    {
        $result->load(['student.classModel', 'student.section', 'student.group', 'exam', 'breakdowns.subject']);

        $breakdowns = $result->breakdowns;

        $view = view('pdf.marksheet', [
            'student' => $result->student,
            'result' => $result,
            'breakdowns' => $breakdowns,
            'exam' => $result->exam,
            'institute' => $institute,
        ]);

        $pdf = Pdf::loadHTML($view->render())
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        return $pdf->output();
    }

    /**
     * Generate bulk marksheets for a class (all students on separate pages).
     */
    public function generateBulkMarksheets(int $classId, Exam $exam, Institute $institute): string
    {
        $students = Student::where('institute_id', $institute->id)
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->orderBy('roll')
            ->get();

        $view = view('pdf.marksheet-bulk', [
            'students' => $students,
            'exam' => $exam,
            'institute' => $institute,
        ]);

        $pdf = Pdf::loadHTML($view->render())
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        return $pdf->output();
    }

    /**
     * Generate tabulation sheet for a class, pulling from results tables.
     */
    public function generateTabulationSheet(int $classId, Exam $exam, Institute $institute): string
    {
        $students = Student::where('institute_id', $institute->id)
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->orderBy('roll')
            ->get();

        $examSubjects = $exam->subjects()->where('class_id', $classId)->with('subject')->get();

        $results = Result::where('exam_id', $exam->id)
            ->where('institute_id', $institute->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $breakdowns = [];
        if ($results->isNotEmpty()) {
            $allBreakdowns = \App\Models\ResultSubjectBreakdown::whereIn('result_id', $results->pluck('id'))
                ->with('subject')
                ->get();

            foreach ($allBreakdowns as $bd) {
                $breakdowns[$bd->result_id][$bd->subject_id] = $bd;
            }
        }

        $view = view('pdf.tabulation-sheet', [
            'students' => $students,
            'exam' => $exam,
            'examSubjects' => $examSubjects,
            'results' => $results,
            'breakdowns' => $breakdowns,
            'institute' => $institute,
        ]);

        $pdf = Pdf::loadHTML($view->render())
            ->setPaper('a4', 'landscape')
            ->setWarnings(false);

        return $pdf->output();
    }

    /**
     * Generate bulk tabulation sheets for all classes in an exam.
     */
    public function generateBulkTabulationSheets(Exam $exam, Institute $institute): string
    {
        $classIds = $exam->subjects->pluck('class_id')->unique();

        $view = view('pdf.tabulation-sheet-bulk', [
            'classIds' => $classIds,
            'exam' => $exam,
            'institute' => $institute,
        ]);

        $pdf = Pdf::loadHTML($view->render())
            ->setPaper('a4', 'landscape')
            ->setWarnings(false);

        return $pdf->output();
    }

    /**
     * Save PDF to storage and return the path.
     */
    public function saveToStorage(string $pdfContent, string $filename): string
    {
        $path = "pdfs/{$filename}";
        Storage::disk('local')->put($path, $pdfContent);
        return $path;
    }
}
