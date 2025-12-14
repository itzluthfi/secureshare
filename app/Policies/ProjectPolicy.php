<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view projects
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        // Admin can view all, others can view if they're a member
        return $user->isAdmin() || $project->hasMember($user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admin and manager can create projects
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        // Admin or global Manager or project creator
        if ($user->isAdmin() || $user->isManager() || $project->created_by === $user->id) {
            return true;
        }

        // Project manager (via pivot)
        $member = $project->members()->where('user_id', $user->id)->first();
        return $member && $member->pivot->role === 'manager';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        // Only admin or project creator
        return $user->isAdmin() || $project->created_by === $user->id;
    }

    /**
     * Determine whether the user can manage members.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        // Admin, creator, or project manager
        if ($user->isAdmin() || $project->created_by === $user->id) {
            return true;
        }

        $member = $project->members()->where('user_id', $user->id)->first();
        return $member && $member->pivot->role === 'manager';
    }
}
