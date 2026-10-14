<?php

namespace App\Support;

use App\Models\User;
use App\Models\userDivision as UserDivision;

class DashboardAccess
{
    private const SELF_ONLY_ROLES = [
        'Advertising',
        'Sosial Media Spesialis',
        'Content Creator',
        'Digital Marketing',
    ];

    public static function isSelfOnly(User $user): bool
    {
        if ($user->isAdmin) {
            return false;
        }

        $user->loadMissing('userDivisions.role');

        if (in_array(
            $user->userDivisions->first()?->role?->role_name,
            self::SELF_ONLY_ROLES,
            true
        )) {
            return true;
        }

        return ! UserDivision::where('parent_id', $user->id)->exists();
    }
}
