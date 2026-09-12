<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstituteSettingPolicy
{
    use HandlesAuthorization;

    public function update(User $user): bool
    {
        return $user->hasRole(['super-admin', 'institute-admin']);
    }
}
