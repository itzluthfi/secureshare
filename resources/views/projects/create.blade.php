@extends('layouts.app')

@section('title', 'Create Project')

@section('content')
<div class="page-header">
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
        <a href="{{ route('projects.index') }}" style="color: var(--text-secondary); text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>
    <h1 class="page-title">Create New Project</h1>
    <p class="page-subtitle">Start a new collaborative project</p>
</div>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <form id="createProjectForm">
            <div class="form-group">
                <label for="project-name">Project Name *</label>
                <input type="text" id="project-name" class="form-input" placeholder="e.g., Q4 Marketing Campaign" required>
            </div>
            
            <div class="form-group">
                <label for="project-description">Description</label>
                <textarea id="project-description" class="form-input" rows="4" placeholder="Describe what this project is about..."></textarea>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="add-me-as-owner" checked>
                    Add me as project owner
                </label>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Project
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text-primary);
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    background: var(--bg-dark);
    border: 2px solid var(--border);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 1rem;
    transition: all 0.3s;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px rgba(79, 127, 255, 0.1);
}

textarea.form-input {
    resize: vertical;
    font-family: inherit;
}

.form-group label input[type="checkbox"] {
    margin-right: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
$('#createProjectForm').submit(function(e) {
    e.preventDefault();
    
    const data = {
        name: $('#project-name').val(),
        description: $('#project-description').val()
    };
    
    $.post('/api/v1/projects', data)
        .done(function(response) {
            showToast('Project created successfully!', 'success');
            setTimeout(() => {
                window.location.href = `/projects/${response.project.id}`;
            }, 1000);
        })
        .fail(function(xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                const errorMsg = Object.values(errors).flat().join('\n');
                showToast(errorMsg, 'error');
            } else {
                showToast(xhr.responseJSON?.message || 'Failed to create project', 'error');
            }
        });
});
</script>
@endpush
