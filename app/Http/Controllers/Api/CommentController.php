<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Document;
use App\Models\Notification;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CommentController extends Controller
{
    use AuthorizesRequests;
    
    protected $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    /**
     * Display comments for a document
     * 
     * @OA\Get(
     *     path="/documents/{documentId}/comments",
     *     tags={"Comments"},
     *     summary="Get document comments",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="documentId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Comments list")
     * )
     */
    public function indexDocument(Request $request, $documentId)
    {
        return $this->getComments(Document::class, $documentId);
    }

    /**
     * Display comments for a project
     * 
     * @OA\Get(
     *     path="/projects/{projectId}/comments",
     *     tags={"Comments"},
     *     summary="Get project comments",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="projectId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Comments list")
     * )
     */
    public function indexProject(Request $request, $projectId)
    {
        return $this->getComments(\App\Models\Project::class, $projectId);
    }

    /**
     * Helper to get comments
     */
    private function getComments($modelClass, $id)
    {
        $model = $modelClass::findOrFail($id);
        $this->authorize('view', $model);

        $comments = $model->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->get();

        return response()->json($comments);
    }

    /**
     * Store a new comment for document
     * 
     * @OA\Post(
     *     path="/documents/{documentId}/comments",
     *     tags={"Comments"},
     *     summary="Add comment to document",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="documentId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Comment added")
     * )
     */
    public function storeDocument(Request $request, $documentId)
    {
        return $this->storeComment($request, Document::class, $documentId);
    }

    /**
     * Store a new comment for project
     * 
     * @OA\Post(
     *     path="/projects/{projectId}/comments",
     *     tags={"Comments"},
     *     summary="Add comment to project",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="projectId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Comment added")
     * )
     */
    public function storeProject(Request $request, $projectId)
    {
        return $this->storeComment($request, \App\Models\Project::class, $projectId);
    }

    /**
     * Helper to store comment
     */
    private function storeComment(Request $request, $modelClass, $id)
    {
        $model = $modelClass::findOrFail($id);
        $this->authorize('view', $model);

        $request->validate([
            'content' => 'required|string',
        ]);

        $comment = $model->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        // Audit log
        $type = class_basename($modelClass);
        $this->auditLog->logCreate($comment, "Comment added to {$type}: {$model->name}");

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $comment->load('user'),
        ], 201);
    }

    /**
     * Reply to a comment
     */
    /**
     * Reply to a comment
     * 
     * @OA\Post(
     *     path="/comments/{commentId}/reply",
     *     tags={"Comments"},
     *     summary="Reply to comment",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="commentId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Reply added")
     * )
     */
    public function reply(Request $request, $commentId)
    {
        $parentComment = Comment::findOrFail($commentId);
        $commentable = $parentComment->commentable;
        
        $this->authorize('view', $commentable);

        $request->validate([
            'content' => 'required|string',
        ]);

        $reply = Comment::create([
            'commentable_id' => $parentComment->commentable_id,
            'commentable_type' => $parentComment->commentable_type,
            'user_id' => $request->user()->id,
            'parent_id' => $commentId,
            'content' => $request->content,
        ]);

        // Notify parent comment author
        if ($parentComment->user_id !== $request->user()->id) {
            $data = [
                'comment_id' => $reply->id,
                'commentable_id' => $parentComment->commentable_id,
                'commentable_type' => $parentComment->commentable_type
            ];
            
            // Backwards compatibility for frontend if needed
            if ($parentComment->commentable_type === Document::class) {
                $data['document_id'] = $parentComment->commentable_id;
            } elseif ($parentComment->commentable_type === \App\Models\Project::class) {
                $data['project_id'] = $parentComment->commentable_id;
            }

            Notification::create([
                'user_id' => $parentComment->user_id,
                'type' => 'comment_reply',
                'title' => 'New Reply',
                'message' => "{$request->user()->name} replied to your comment",
                'data' => $data,
            ]);
        }

        $type = class_basename($parentComment->commentable_type);
        $this->auditLog->log(
            'comment_reply',
            "Reply added to comment on {$type}: {$commentable->name}",
            'App\Models\Comment',
            $reply->id
        );

        return response()->json([
            'message' => 'Reply added successfully',
            'comment' => $reply->load('user'),
        ], 201);
    }

    /**
     * Update the specified comment
     * 
     * @OA\Put(
     *     path="/comments/{id}",
     *     tags={"Comments"},
     *     summary="Update comment",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Comment updated")
     * )
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update', $comment);

        $oldValues = $comment->toArray();

        $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update(['content' => $request->content]);

        $this->auditLog->logUpdate($comment, $oldValues, "Comment updated");

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $comment,
        ]);
    }

    /**
     * Remove the specified comment
     * 
     * @OA\Delete(
     *     path="/comments/{id}",
     *     tags={"Comments"},
     *     summary="Delete comment",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Comment deleted")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('delete', $comment);

        $this->auditLog->logDelete($comment, "Comment deleted");
        
        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }
}
