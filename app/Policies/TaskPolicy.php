<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Admin or project member
        return $user->isAdmin() || $user->canAccessProject($task->project_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, $projectId): bool
    {
        // Must be a member of the project
        return $user->isAdmin() || $user->canAccessProject($projectId);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Admin, creator, assignee, or project manager
        if ($user->isAdmin() || $task->created_by === $user->id || $task->assigned_to === $user->id) {
            return true;
        }

        $project = $task->project;
        $member = $project->members()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['manager', 'owner']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Admin, creator, or project owner
        if ($user->isAdmin() || $task->created_by === $user->id) {
            return true;
        }

        $project = $task->project;
        return $project->created_by === $user->id;
    }
}
