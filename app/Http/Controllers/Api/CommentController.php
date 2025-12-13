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
     */
    public function index(Request $request, $documentId)
    {
        $document = Document::findOrFail($documentId);
        $this->authorize('view', $document);

        $comments = Comment::where('document_id', $documentId)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    /**
     * Store a new comment
     */
    public function store(Request $request, $documentId)
    {
        $document = Document::findOrFail($documentId);
        $this->authorize('view', $document);

        $request->validate([
            'content' => 'required|string',
        ]);

        $comment = Comment::create([
            'document_id' => $documentId,
            'user_id' => $request->user()->id,
            'parent_id' => null,
            'content' => $request->content,
        ]);

        // Notify document uploader
        if ($document->uploaded_by !== $request->user()->id) {
            Notification::create([
                'user_id' => $document->uploaded_by,
                'type' => 'comment_added',
                'title' => 'New Comment',
                'message' => "{$request->user()->name} commented on {$document->name}",
                'data' => ['document_id' => $documentId, 'comment_id' => $comment->id],
            ]);
        }

        $this->auditLog->logCreate($comment, "Comment added to document: {$document->name}");

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $comment->load('user'),
        ], 201);
    }

    /**
     * Reply to a comment
     */
    public function reply(Request $request, $commentId)
    {
        $parentComment = Comment::findOrFail($commentId);
        $document = $parentComment->document;
        
        $this->authorize('view', $document);

        $request->validate([
            'content' => 'required|string',
        ]);

        $reply = Comment::create([
            'document_id' => $parentComment->document_id,
            'user_id' => $request->user()->id,
            'parent_id' => $commentId,
            'content' => $request->content,
        ]);

        // Notify parent comment author
        if ($parentComment->user_id !== $request->user()->id) {
            Notification::create([
                'user_id' => $parentComment->user_id,
                'type' => 'comment_reply',
                'title' => 'New Reply',
                'message' => "{$request->user()->name} replied to your comment",
                'data' => ['document_id' => $document->id, 'comment_id' => $reply->id],
            ]);
        }

        $this->auditLog->log(
            'comment_reply',
            "Reply added to comment on document: {$document->name}",
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
