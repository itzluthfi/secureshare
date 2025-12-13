<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Can view if member of the project
        return $user->isAdmin() || $task->project->hasMember($user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users (including members) can create tasks
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Admin can update any
        if ($user->isAdmin()) {
            return true;
        }
        
        // Project creator/manager can update
        if ($task->created_by === $user->id || $task->project->created_by === $user->id) {
            return true;
        }
        
        // Member can update if assigned to them (status only)
        return $task->assigned_to === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Only admin, project owner, or task creator can delete
        return $user->isAdmin() || 
               $task->project->created_by === $user->id ||
               $task->created_by === $user->id;
    }
}
