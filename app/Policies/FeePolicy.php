<?php

namespace App\Policies;

use App\Models\FeeStructure;
use App\Models\User;

class FeePolicy
{
    /**
     * Institute admin and accountant can collect fees and view reports.
     */
    public function collect(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('institute-admin') || $user->hasRole('accountant');
    }

    /**
     * Only institute-admin can manage fee structures (create/edit/delete).
     */
    public function manageStructures(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('institute-admin');
    }

    /**
     * Institute admin and accountant can view fee reports.
     */
    public function viewReports(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('institute-admin') || $user->hasRole('accountant');
    }

    /**
     * Institute admin and accountant can generate monthly fees.
     */
    public function generateMonthly(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->hasRole('institute-admin');
    }
}
