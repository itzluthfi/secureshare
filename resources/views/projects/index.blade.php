@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div class="page-header">
        <h1 class="page-title"><i class="fas fa-folder-open"></i> My Projects</h1>
        @can('create', App\Models\Project::class)
            <a href="{{ route('projects.create') }}" class="btn btn-primary">+ New Project</a>
        @endcan
    </div>
</div>

<div id="projectsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
    <p style="color: #6B7280;">Loading projects...</p>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    console.log('=== PROJECTS PAGE INIT ===');
    
    // Token already declared in layout, just check it
    console.log('1. Token check:', typeof token !== 'undefined' && token ? 'EXISTS (length: ' + token.length + ')' : 'NULL/MISSING');
    
    if (typeof token === 'undefined' || !token) {
        console.error('2. No token found - redirecting to login');
        window.location.href = '/login';
        return;
    }
    
    console.log('2. AJAX already setup in layout');
    console.log('3. Loading projects from API...');
    
    // Load projects
    $.get('/api/v1/projects')
        .done(function(projects) {
            console.log('4. ✅ Projects loaded successfully:', projects);
            console.log('   - Total projects:', projects.data ? projects.data.length : 0);
            
            if (projects.data && projects.data.length > 0) {
                let html = '';
                projects.data.forEach(function(project) {
                    html += `
                        <div class="card" style="cursor: pointer; transition: transform 0.3s;" onclick="window.location.href='/projects/${project.encrypted_id}'">
                            <h3 style="color: var(--primary-blue); margin-bottom: 0.5rem;">${project.name}</h3>
                            <p style="color: #6B7280; margin-bottom: 1rem;">${project.description || 'No description'}</p>
                            <div style="display: flex; justify-content: space-between; font-size: 0.9rem; color: #9CA3AF;">
                                <span>👥 ${project.members ? project.members.length : 0} members</span>
                                <span>📄 ${project.documents ? project.documents.length : 0} docs</span>
                            </div>
                        </div>
                    `;
                });
                $('#projectsGrid').html(html);
                console.log('5. ✅ Projects rendered to DOM');
            } else {
                $('#projectsGrid').html('<p style="color: #6B7280;">No projects found.</p>');
                console.log('5. No projects to display');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('4. ❌ FAILED to load projects');
            console.error('   - HTTP Status:', xhr.status);
            console.error('   - Status Text:', xhr.statusText);
            console.error('   - Error:', error);
            console.error('   - Response:', xhr.responseJSON);
            console.error('   - Response Text:', xhr.responseText);
            
            if (xhr.status === 401) {
                console.error('   - 401 Unauthorized: Token invalid or expired');
                console.error('   - Clearing token and redirecting to login...');
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.href = '/login';
            } else if (xhr.status === 0) {
                console.error('   - Network error: Cannot reach server');
                $('#projectsGrid').html('<p style="color: var(--danger);">Cannot connect to server. Is Laravel running?</p>');
            } else {
                $('#projectsGrid').html('<p style="color: var(--danger);">Failed to load projects. Check console for details.</p>');
            }
        })
        .always(function() {
            console.log('=== PROJECTS PAGE LOAD COMPLETE ===');
        });
});
</script>
@endpush
