<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamRoom;
use App\Models\SeatAssignment;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class SeatAllocationService
{
    /**
     * Allocate students to exam rooms.
     *
     * Allocation strategy:
     * - 'sequential': students assigned by roll number order across rooms
     * - 'shuffled': students randomized then distributed (reduces cross-copying)
     *
     * Tie-breaking rule for position ranking (dense ranking):
     * Students with identical marks receive the same rank.
     * The next rank after a tie skips no numbers (dense: 1,1,2,3 not 1,1,3,4).
     *
     * @param Exam $exam
     * @param array<int, int> $roomIds
     * @param int $instituteId
     * @param string $strategy 'sequential' or 'shuffled'
     * @return array{success: bool, message: string, allocated: int}
     * @throws \RuntimeException
     */
    public function allocate(Exam $exam, array $roomIds, int $instituteId, string $strategy = 'sequential'): array
    {
        $rooms = ExamRoom::whereIn('id', $roomIds)
            ->where('institute_id', $instituteId)
            ->where('exam_id', $exam->id)
            ->get();

        if ($rooms->isEmpty()) {
            throw new \RuntimeException(__('exams.no_rooms_found'));
        }

        $totalCapacity = $rooms->sum('capacity');

        // Get all active students for this exam's classes
        $classIds = $exam->subjects->pluck('class_id')->unique();
        $students = Student::where('institute_id', $instituteId)
            ->whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->orderBy('roll')
            ->get();

        if ($students->isEmpty()) {
            throw new \RuntimeException(__('exams.no_students_found'));
        }

        if ($totalCapacity < $students->count()) {
            throw new \RuntimeException(__('exams.insufficient_capacity', [
                'capacity' => $totalCapacity,
                'students' => $students->count(),
            ]));
        }

        // Apply strategy
        if ($strategy === 'shuffled') {
            $students = $students->shuffle();
        }

        DB::transaction(function () use ($exam, $rooms, $students) {
            // Clear existing assignments only for the selected rooms
            $roomIds = $rooms->pluck('id');
            SeatAssignment::whereIn('exam_room_id', $roomIds)->delete();

            $seatNo = 1;
            $roomIndex = 0;
            $currentRoom = $rooms[$roomIndex];
            $assignedInRoom = 0;

            foreach ($students as $student) {
                // Move to next room if current is full
                while ($assignedInRoom >= $currentRoom->capacity) {
                    $roomIndex++;
                    $currentRoom = $rooms[$roomIndex];
                    $assignedInRoom = 0;
                    $seatNo = 1;
                }

                SeatAssignment::create([
                    'exam_room_id' => $currentRoom->id,
                    'student_id' => $student->id,
                    'seat_no' => $seatNo,
                ]);

                $seatNo++;
                $assignedInRoom++;
            }
        });

        return [
            'success' => true,
            'message' => __('exams.seats_allocated', ['count' => $students->count()]),
            'allocated' => $students->count(),
        ];
    }

    /**
     * Get seating chart for a room.
     */
    public function getSeatingChart(ExamRoom $room): array
    {
        $assignments = $room->assignments()
            ->with('student')
            ->orderBy('seat_no')
            ->get();

        $chart = [];
        foreach ($assignments as $assignment) {
            $row = (int) (($assignment->seat_no - 1) / $room->columns);
            $col = ($assignment->seat_no - 1) % $room->columns;
            $chart[$row][$col] = [
                'seat_no' => $assignment->seat_no,
                'student' => $assignment->student,
            ];
        }

        return $chart;
    }
}
