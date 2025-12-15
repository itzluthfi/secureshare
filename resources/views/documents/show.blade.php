@extends('layouts.app')

@section('title', $document->name)

@section('content')
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <div>
        <div class="card">
            <h1>{{ $document->name }}</h1>
            <p style="color: #6B7280; margin: 0.5rem 0;">
                <strong>Project:</strong> <a href="/projects/{{ $document->project->encrypted_id }}" style="color: var(--primary);">{{ $document->project->name }}</a>
            </p>
            <p style="color: #9CA3AF; font-size: 0.9rem;">
                Uploaded by {{ $document->uploader->name }} on {{ $document->created_at->format('M d, Y') }}
            </p>
            <p style="color: #9CA3AF; font-size: 0.9rem;">
                Version: v{{ $document->current_version }} • Size: {{ $document->file_size_human ?? $document->file_size . ' bytes' }}
            </p>
            
            <div style="margin-top: 1.5rem;">
                <button class="btn btn-primary" onclick="downloadDocument({{ $document->id }})">⬇️ Download</button>
                <button class="btn btn-secondary" onclick="$('#versionModal').show()">📋 Versions</button>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="card" style="margin-top: 1.5rem;">
            <h2 style="margin-bottom: 1rem;">Comments</h2>
            
            <form id="commentForm" style="margin-bottom: 1.5rem;">
                <textarea id="commentContent" placeholder="Add a comment..." style="width: 100%; padding: 0.7rem; border: 1px solid var(--border); border-radius: 6px; min-height: 80px;"></textarea>
                <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">Post Comment</button>
            </form>

            <div id="commentsList">
                @forelse($document->comments as $comment)
                    <div class="comment" style="padding: 1rem; background: var(--light); border-radius: 6px; margin-bottom: 1rem;">
                        <strong>{{ $comment->user->name }}</strong>
                        <small style="color: #9CA3AF; margin-left: 0.5rem;">{{ $comment->created_at->diffForHumans() }}</small>
                        <p style="margin: 0.5rem 0;">{{ $comment->content }}</p>
                        
                        <button class="btn btn-sm" onclick="showReplyForm({{ $comment->id }})">Reply</button>

                        <div id="reply-form-{{ $comment->id }}" style="display: none; margin-top: 1rem;">
                            <textarea id="reply-content-{{ $comment->id }}" placeholder="Write a reply..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px;"></textarea>
                            <button class="btn btn-primary btn-sm" onclick="postReply({{ $comment->id }})">Post Reply</button>
                        </div>

                        @foreach($comment->replies as $reply)
                            <div style="margin-left: 2rem; margin-top: 0.7rem; padding: 0.7rem; background: white; border-left: 3px solid var(--primary); border-radius: 4px;">
                                <strong>{{ $reply->user->name }}</strong>
                                <small style="color: #9CA3AF; margin-left: 0.5rem;">{{ $reply->created_at->diffForHumans() }}</small>
                                <p style="margin: 0.5rem 0;">{{ $reply->content }}</p>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <p style="color: #6B7280;">No comments yet. Be the first!</p>
                @endforelse
            </div>
        </div>
    </div>

    <div>
        <!-- Document Info -->
        <div class="card">
            <h3>Document Information</h3>
            <hr style="margin: 1rem 0; border: 0; border-top: 1px solid var(--border);">
            <p><strong>Type:</strong> {{ $document->file_type }}</p>
            <p><strong>Original Name:</strong> {{ $document->original_name }}</p>
            <p><strong>Encryption:</strong> ✅ AES-256</p>
            <p><strong>Total Versions:</strong> {{ $document->versions->count() }}</p>
        </div>
    </div>
</div>

<!-- Versions Modal -->
<div id="versionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999;">
    <div style="max-width: 600px; margin: 3rem auto; background: white; padding: 2rem; border-radius: 8px; max-height: 80vh; overflow-y: auto;">
        <h2>Document Versions</h2>
        @foreach($document->versions as $version)
            <div style="padding: 1rem; border-bottom: 1px solid var(--border);">
                <strong>Version {{ $version->version_number }}</strong>
                <p style="color: #6B7280; font-size: 0.9rem;">{{ $version->change_notes ?? 'No notes' }}</p>
                <small>Uploaded by {{ $version->uploader->name }} on {{ $version->created_at->format('M d, Y H:i') }}</small>
                <br>
                <button class="btn btn-sm btn-primary" style="margin-top: 0.5rem;" onclick="downloadVersion({{ $document->id }}, {{ $version->version_number }})">Download</button>
            </div>
        @endforeach
        <button class="btn" onclick="$('#versionModal').hide()" style="margin-top: 1rem;">Close</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Post comment
$('#commentForm').submit(function(e) {
    e.preventDefault();
    
    const content = $('#commentContent').val();
    
    $.post('/api/v1/documents/{{ $document->id }}/comments', { content })
        .done(response => {
            showToast('Comment added!', 'success');
            location.reload();
        })
        .fail(error => {
            showToast('Failed to add comment', 'error');
        });
});

function showReplyForm(commentId) {
    $('#reply-form-' + commentId).toggle();
}

function postReply(commentId) {
    const content = $('#reply-content-' + commentId).val();
    
    $.post('/api/v1/comments/' + commentId + '/reply', { content })
        .done(response => {
            showToast('Reply added!', 'success');
            location.reload();
        })
        .fail(error => {
            showToast('Failed to add reply', 'error');
        });
}

function downloadDocument(docId) {
    const token = localStorage.getItem('token');
    
    fetch(`/api/v1/documents/${docId}/download`, {
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Download failed');
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'document';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        showToast('Document downloaded!', 'success');
    })
    .catch(error => {
        console.error('Download error:', error);
        showToast('Failed to download document', 'error');
    });
}

function downloadVersion(docId, versionNumber) {
    const token = localStorage.getItem('token');
    
    fetch(`/api/v1/documents/${docId}/versions/${versionNumber}/download`, {
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Download failed');
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `document-v${versionNumber}`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        showToast(`Version ${versionNumber} downloaded!`, 'success');
    })
    .catch(error => {
        console.error('Download error:', error);
        showToast('Failed to download version', 'error');
    });
}
</script>
@endpush
