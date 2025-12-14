<?php

namespace App\Policies;

use App\Models\User;

class CalendarPolicy
{
    /**
     * Determine whether the user can create milestones.
     */
    public function createMilestone(User $user): bool
    {
        // Only admin and manager can create calendar milestones
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can update the milestone.
     */
    public function updateMilestone(User $user, $milestone): bool
    {
        // Admin or milestone creator
        return $user->isAdmin() || $milestone->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the milestone.
     */
    public function deleteMilestone(User $user, $milestone): bool
    {
        // Admin or milestone creator
        return $user->isAdmin() || $milestone->created_by === $user->id;
    }
}
