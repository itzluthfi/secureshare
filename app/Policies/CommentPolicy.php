<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comment $comment): bool
    {
        // Admin or project member
        $document = $comment->document;
        return $user->isAdmin() || $user->canAccessProject($document->project_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, $documentId): bool
    {
        // Must be a project member (check via document)
        $document = \App\Models\Document::find($documentId);
        if (!$document) {
            return false;
        }
        return $user->isAdmin() || $user->canAccessProject($document->project_id);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        // Only the comment author or admin
        return $user->isAdmin() || $comment->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Comment author, admin, or project owner
        if ($user->isAdmin() || $comment->user_id === $user->id) {
            return true;
        }

        $document = $comment->document;
        $project = $document->project;
        return $project->created_by === $user->id;
    }
}
