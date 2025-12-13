<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
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
    public function view(User $user, Document $document): bool
    {
        // Admin or project member
        return $user->isAdmin() || $user->canAccessProject($document->project_id);
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
    public function update(User $user, Document $document): bool
    {
        // Admin, uploader, or project manager
        if ($user->isAdmin() || $document->uploaded_by === $user->id) {
            return true;
        }

        $project = $document->project;
        $member = $project->members()->where('user_id', $user->id)->first();
        return $member && in_array($member->pivot->role, ['manager', 'owner']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Document $document): bool
    {
        // Admin, uploader, or project owner
        if ($user->isAdmin() || $document->uploaded_by === $user->id) {
            return true;
        }

        $project = $document->project;
        return $project->created_by === $user->id;
    }

    /**
     * Determine whether the user can download the model.
     */
    public function download(User $user, Document $document): bool
    {
        // Same as view
        return $this->view($user, $document);
    }
}
