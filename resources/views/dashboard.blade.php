@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Welcome back, <span id="welcome-name">User</span>!</p>
</div>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;" id="project-count">0</div>
                <div style="opacity: 0.9;">Active Projects</div>
            </div>
            <i class="fas fa-folder" style="font-size: 3rem; opacity: 0.3;"></i>
        </div>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border: none;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;" id="task-count">0</div>
                <div style="opacity: 0.9;">My Tasks</div>
            </div>
            <i class="fas fa-tasks" style="font-size: 3rem; opacity: 0.3;"></i>
        </div>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border: none;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;" id="doc-count">0</div>
                <div style="opacity: 0.9;">Documents</div>
            </div>
            <i class="fas fa-file-alt" style="font-size: 3rem; opacity: 0.3;"></i>
        </div>
    </div>
    
    <div class="card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border: none;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;" id="notif-count-dash">0</div>
                <div style="opacity: 0.9;">Notifications</div>
            </div>
            <i class="fas fa-bell" style="font-size: 3rem; opacity: 0.3;"></i>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Recent Projects -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Projects</h2>
            <a href="{{ route('projects.index') }}" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <div id="recent-projects">
            <p style="color: var(--text-muted); text-align: center; padding: 2rem;">Loading projects...</p>
        </div>
    </div>
    
    <!-- My Tasks -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">My Tasks</h2>
            <a href="/tasks" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <div id="my-tasks">
            <p style="color: var(--text-muted); text-align: center; padding: 2rem;">Loading tasks...</p>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">Recent Activity</h2>
    </div>
    <div id="recent-activity">
        <p style="color: var(--text-muted); text-align: center; padding: 2rem;">Loading activity...</p>
    </div>
</div>
@endsection

@push('styles')
<style>
.project-item {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: background 0.3s;
    cursor: pointer;
}

.project-item:hover {
    background: var(--bg-card-hover);
}

.project-item:last-child {
    border-bottom: none;
}

.project-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: linear-gradient(135deg, var(--primary-blue), var(--purple));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.project-info {
    flex: 1;
}

.project-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.project-meta {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.task-item {
    padding: 0.75rem;
    border-left: 3px solid var(--primary-blue);
    background: var(--bg-card-hover);
    border-radius: 6px;
    margin-bottom: 0.75rem;
}

.task-item:last-child {
    margin-bottom: 0;
}

.task-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.task-meta {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.status-badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-todo { background: var(--text-muted); color: white; }
.status-in_progress { background: var(--warning); color: white; }
.status-done { background: var(--success); color: white; }

.activity-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid var(--border);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--bg-card-hover);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-text {
    margin-bottom: 0.25rem;
}

.activity-time {
    font-size: 0.8rem;
    color: var(--text-muted);
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Load stats
    loadDashboardStats();
    
    // Load recent projects
    loadRecentProjects();
    
    // Load my tasks
    loadMyTasks();
    
    // Load recent activity (audit logs)
    loadRecentActivity();
});

function loadDashboardStats() {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    $('#welcome-name').text(user.name || 'User');
    
    // Load project count
    $.get('/api/v1/projects')
        .done(data => {
            $('#project-count').text(data.total || data.data?.length || 0);
        });
    
    // Load notification count
    $.get('/api/v1/notifications/unread-count')
        .done(data => {
            $('#notif-count-dash').text(data.unread_count || 0);
        });
    
    // For now, set task and doc counts to 0 (will load from API)
    $('#task-count').text(0);
    $('#doc-count').text(0);
}

function loadRecentProjects() {
    $.get('/api/v1/projects')
        .done(function(response) {
            const projects = response.data || response;
            
            if (!projects || projects.length === 0) {
                @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                    $('#recent-projects').html('<p style="color: var(--text-muted); text-align: center; padding: 2rem;">No projects yet. <a href="/projects/create" style="color: var(--primary-blue);">Create one!</a></p>');
                @else
                    $('#recent-projects').html('<p style="color: var(--text-muted); text-align: center; padding: 2rem;">No projects yet. Contact your admin or manager to be added to a project.</p>');
                @endif
                return;
            }
            
            let html = '';
            projects.slice(0, 5).forEach(project => {
                const memberCount = project.members?.length || 0;
                const docCount = project.documents?.length || 0;
                
                html += `
                    <div class="project-item" onclick="window.location.href='/projects/${project.id}'">
                        <div class="project-icon">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="project-info">
                            <div class="project-name">${project.name}</div>
                            <div class="project-meta">
                                <i class="fas fa-users"></i> ${memberCount} members • 
                                <i class="fas fa-file"></i> ${docCount} docs
                            </div>
                        </div>
                    </div>
                `;
            });
            
            $('#recent-projects').html(html);
        })
        .fail(() => {
            $('#recent-projects').html('<p style="color: var(--danger); text-align: center; padding: 2rem;">Failed to load projects</p>');
        });
}

function loadMyTasks() {
    // For now, show placeholder
    $('#my-tasks').html('<p style="color: var(--text-muted); text-align: center; padding: 2rem;">No tasks assigned</p>');
}

function loadRecentActivity() {
    $.get('/api/v1/audit-logs')
        .done(function(response) {
            const logs = response.data || response;
            
            if (!logs || logs.length === 0) {
                $('#recent-activity').html('<p style="color: var(--text-muted); text-align: center; padding: 2rem;">No recent activity</p>');
                return;
            }
            
            let html = '';
            logs.slice(0, 10).forEach(log => {
                const icon = getActivityIcon(log.action);
                const time = new Date(log.created_at).toLocaleString();
                
                html += `
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="${icon}"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">${log.description}</div>
                            <div class="activity-time">${time}</div>
                        </div>
                    </div>
                `;
            });
            
            $('#recent-activity').html(html);
        })
        .fail(() => {
            $('#recent-activity').html('<p style="color: var(--text-muted); text-align: center; padding: 2rem;">No activity to show</p>');
        });
}

function getActivityIcon(action) {
    const icons = {
        'login': 'fas fa-sign-in-alt',
        'register': 'fas fa-user-plus',
        'create': 'fas fa-plus',
        'update': 'fas fa-edit',
        'delete': 'fas fa-trash',
        'upload': 'fas fa-upload',
        'download': 'fas fa-download'
    };
    return icons[action] || 'fas fa-circle';
}
</script>
@endpush
