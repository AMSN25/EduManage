<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Institute admin can mark attendance for any class.
     * Teacher can only mark attendance for their assigned classes.
     */
    public function mark(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if ($user->hasRole('institute-admin')) {
            return true;
        }

        if ($user->hasRole('teacher')) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can edit attendance for a given class/section/date.
     * Teachers restricted to their assigned classes; all users blocked beyond edit window.
     */
    public function edit(User $user, Attendance $attendance): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if ($user->hasRole('institute-admin')) {
            return true;
        }

        // Teachers: check class assignment
        if ($user->hasRole('teacher')) {
            if (!$user->isAssignedTo($attendance->class_id, $attendance->section_id)) {
                return false;
            }

            $allowedDays = config('attendance.edit_allowed_days', 3);
            $cutoffDate = now()->subDays($allowedDays)->startOfDay();

            return !$attendance->date->lt($cutoffDate);
        }

        return false;
    }

    /**
     * Check if user can mark attendance for a specific class/section.
     * Used by AttendanceMark component for real-time validation.
     */
    public function markFor(User $user, int $classId, int $sectionId): bool
    {
        if ($user->hasRole('super-admin') || $user->hasRole('institute-admin')) {
            return true;
        }

        if ($user->hasRole('teacher')) {
            return $user->isAssignedTo($classId, $sectionId);
        }

        return false;
    }

    /**
     * View attendance report.
     */
    public function viewReport(User $user): bool
    {
        return $user->hasRole('super-admin')
            || $user->hasRole('institute-admin')
            || $user->hasRole('teacher');
    }
}
