@extends('layouts.app')

@section('title', 'All Documents')

@section('content')
<div class="page-header">
    <h1 class="page-title">All Documents</h1>
    <p class="page-subtitle">Browse all your documents across projects</p>
</div>

<div style="margin-bottom: 1.5rem;">
    <input type="text" id="search-docs" class="form-input" placeholder="Search documents..." style="max-width: 400px;">
</div>

<div class="card">
    <div id="documents-list">
        <p style="text-align: center; padding: 3rem; color: var(--text-muted);">
            <i class="fas fa-file-alt" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
            Loading documents...
        </p>
    </div>
</div>
@endsection

@push('styles')
<style>
.form-input {
    padding: 0.7rem 1rem;
    background: var(--bg-dark);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 0.95rem;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-blue);
}

.document-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    border-bottom: 1px solid var(--border);
    transition: background 0.3s;
    cursor: pointer;
}

.document-item:hover {
    background: var(--bg-card-hover);
}

.document-item:last-child {
    border-bottom: none;
}

.document-icon {
    width: 45px;
    height: 45px;
    border-radius: 8px;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

.document-info {
    flex: 1;
}

.document-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.document-project {
    font-size: 0.85rem;
    color: var(--primary-blue);
    margin-bottom: 0.25rem;
}

.document-meta {
    font-size: 0.85rem;
    color: var(--text-muted);
}
</style>
@endpush

@push('scripts')
<script>
let allDocuments = [];

$(document).ready(function() {
    loadAllDocuments();
    
    $('#search-docs').on('input', function() {
        const search = $(this).val().toLowerCase();
        const filtered = allDocuments.filter(doc => 
            doc.name.toLowerCase().includes(search) ||
            doc.project?.name.toLowerCase().includes(search)
        );
        renderDocuments(filtered);
    });
});

function loadAllDocuments() {
    // Load all projects first, then get their documents
    $.get('/api/v1/projects')
        .done(function(response) {
            const projects = response.data || response;
            let promises = [];
            
            projects.forEach(project => {
                promises.push(
                    $.get(`/api/v1/projects/${project.id}/documents`)
                        .then(docResponse => ({
                            project: project,
                            documents: docResponse.data || docResponse
                        }))
                );
            });
            
            Promise.all(promises).then(results => {
                allDocuments = [];
                results.forEach(result => {
                    result.documents.forEach(doc => {
                        doc.project = result.project;
                        allDocuments.push(doc);
                    });
                });
                
                renderDocuments(allDocuments);
            });
        })
        .fail(function(xhr) {
            console.error('Failed to load documents:', xhr);
            $('#documents-list').html('<p style="text-align: center; padding: 3rem; color: var(--danger);">Failed to load documents</p>');
        });
}

function renderDocuments(documents) {
    if (documents.length === 0) {
        $('#documents-list').html(`
            <p style="text-align: center; padding: 3rem; color: var(--text-muted);">
                <i class="fas fa-file-alt" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.3;"></i>
                No documents found
            </p>
        `);
        return;
    }
    
    let html = '';
    documents.forEach(doc => {
        const size = formatFileSize(doc.file_size);
        const date = new Date(doc.created_at).toLocaleDateString();
        
        html += `
            <div class="document-item" onclick="viewDocument(${doc.id})">
                <div class="document-icon">
                    <i class="fas fa-file"></i>
                </div>
                <div class="document-info">
                    <div class="document-name">${doc.name}</div>
                    <div class="document-project">
                        <i class="fas fa-folder"></i> ${doc.project?.name || 'Unknown Project'}
                    </div>
                    <div class="document-meta">
                        <i class="fas fa-lock"></i> Encrypted • ${size} • v${doc.current_version} • ${date}
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#documents-list').html(html);
}

function viewDocument(docId) {
    window.location.href = `/documents/${docId}`;
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}
</script>
@endpush
