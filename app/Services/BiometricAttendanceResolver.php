<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\BiometricDevice;
use App\Models\BiometricPunch;
use App\Models\DeviceUserMapping;
use Illuminate\Support\Facades\DB;

class BiometricAttendanceResolver
{
    public function resolvePunches(array $punchIds): array
    {
        $results = ['resolved' => 0, 'skipped' => 0, 'failed' => 0];

        $punches = BiometricPunch::whereIn('id', $punchIds)
            ->where('resolution_status', 'pending')
            ->get();

        foreach ($punches as $punch) {
            try {
                $this->resolvePunch($punch);
                $results['resolved']++;
            } catch (\RuntimeException $e) {
                $results['skipped']++;
            } catch (\Throwable $e) {
                $results['failed']++;
            }
        }

        return $results;
    }

    public function resolvePunch(BiometricPunch $punch): void
    {
        $device = BiometricDevice::withoutGlobalScopes()->find($punch->biometric_device_id);

        if (!$device) {
            $punch->resolve('no_mapping');
            throw new \RuntimeException("Device not found: {$punch->biometric_device_id}");
        }

        if ($device->status === 'pending') {
            $punch->resolve('device_pending');
            throw new \RuntimeException("Device pending: {$device->serial_number}");
        }

        $mapping = DeviceUserMapping::withoutGlobalScopes()
            ->where('biometric_device_id', $device->id)
            ->where('device_user_id', $punch->device_user_id)
            ->first();

        if (!$mapping) {
            $punch->resolve('no_mapping');
            throw new \RuntimeException(
                "No mapping for device_user_id {$punch->device_user_id} on device {$device->serial_number}"
            );
        }

        $punch->update(['institute_id' => $mapping->institute_id]);

        $student = $mapping->student;
        if (!$student || $student->status !== 'active') {
            $punch->resolve('no_mapping');
            throw new \RuntimeException("Student not active or not found for mapping {$mapping->id}");
        }

        $punchDate = $punch->punch_time->toDateString();

        $academicYear = AcademicYear::withoutGlobalScopes()
            ->where('institute_id', $mapping->institute_id)
            ->where('start_date', '<=', $punchDate)
            ->where('end_date', '>=', $punchDate)
            ->first();

        if (!$academicYear) {
            $punch->resolve('no_mapping');
            throw new \RuntimeException("No active academic year for date {$punchDate}");
        }

        $attendance = $this->findOrCreateAttendance(
            $mapping->institute_id,
            $student->class_id,
            $student->section_id,
            $punchDate,
            $academicYear->id
        );

        $existingRecord = $attendance->records()
            ->where('student_id', $student->id)
            ->first();

        if ($existingRecord && $existingRecord->source === 'manual') {
            $punch->resolve('resolved');
            return;
        }

        if ($existingRecord && $existingRecord->source === 'device') {
            $punch->resolve('resolved');
            return;
        }

        DB::transaction(function () use ($attendance, $student, $punch, $punchDate) {
            $status = $this->determineStatus($punch, $punchDate);

            AttendanceRecord::withoutGlobalScopes()->updateOrCreate(
                [
                    'attendance_id' => $attendance->id,
                    'student_id' => $student->id,
                ],
                [
                    'status' => $status,
                    'source' => 'device',
                    'remarks' => "Punch at {$punch->punch_time->format('H:i:s')}",
                ]
            );
        });

        $punch->resolve('resolved');
    }

    protected function determineStatus(BiometricPunch $punch, string $punchDate): string
    {
        $punchHour = (int) $punch->punch_time->format('H');
        $punchMinute = (int) $punch->punch_time->format('i');

        $minutesSinceMidnight = $punchHour * 60 + $punchMinute;

        if ($minutesSinceMidnight <= 9 * 60 + 30) {
            return 'present';
        } elseif ($minutesSinceMidnight <= 10 * 60 + 30) {
            return 'late';
        }

        return 'present';
    }

    protected function findOrCreateAttendance(
        int $instituteId,
        int $classId,
        int $sectionId,
        string $date,
        int $academicYearId
    ): Attendance {
        $attendance = Attendance::withoutGlobalScopes()
            ->where('institute_id', $instituteId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereDate('date', $date)
            ->first();

        if ($attendance) {
            return $attendance;
        }

        $systemUser = \App\Models\User::where('institute_id', $instituteId)
            ->where('is_active', true)
            ->first();

        return Attendance::withoutGlobalScopes()->create([
            'institute_id' => $instituteId,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'date' => $date,
            'taken_by' => $systemUser?->id ?? 1,
            'academic_year_id' => $academicYearId,
        ]);
    }
}
