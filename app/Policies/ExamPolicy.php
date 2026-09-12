<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\User;

class ExamPolicy
{
    /**
     * Institute admin can manage exams; teachers cannot create exams.
     */
    public function manage(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('institute-admin');
    }

    /**
     * Check if user can enter marks for a specific exam subject.
     * Teachers restricted to their assigned classes/subjects via teacher_subjects.
     */
    public function enterMarks(User $user, Exam $exam): bool
    {
        if ($user->hasRole('super-admin') || $user->hasRole('institute-admin')) {
            return true;
        }

        if (!$user->hasRole('teacher')) {
            return false;
        }

        // Teacher must have at least one assignment for this exam's classes
        $assignedClassIds = $user->assignedClassIds();
        $examClassIds = $exam->subjects->pluck('class_id')->unique();

        return $assignedClassIds->intersect($examClassIds)->isNotEmpty();
    }

    /**
     * Check if user can enter marks for a specific exam_subject.
     * Teachers must be assigned to the specific class/subject.
     */
    public function enterMarksForSubject(User $user, \App\Models\ExamSubject $examSubject): bool
    {
        if ($user->hasRole('super-admin') || $user->hasRole('institute-admin')) {
            return true;
        }

        if (!$user->hasRole('teacher')) {
            return false;
        }

        return $user->teacherSubjects()
            ->withoutGlobalScope('institute')
            ->where('class_id', $examSubject->class_id)
            ->where('subject_id', $examSubject->subject_id)
            ->exists();
    }

    /**
     * Check if user can unlock (edit) submitted/locked marks.
     * Only institute-admin can unlock.
     */
    public function unlockMarks(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('institute-admin');
    }

    /**
     * Check if user can view marks for an exam.
     */
    public function viewMarks(User $user, Exam $exam): bool
    {
        if ($user->hasRole('super-admin') || $user->hasRole('institute-admin')) {
            return true;
        }

        if ($user->hasRole('teacher')) {
            return $this->enterMarks($user, $exam);
        }

        return false;
    }
}
