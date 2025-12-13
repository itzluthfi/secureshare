@extends('layouts.app')

@section('title', 'My Tasks')

@section('content')
<div class="page-header">
    <h1 class="page-title">My Tasks</h1>
    <p class="page-subtitle">All tasks assigned to you</p>
</div>

<!-- Filter & View Options -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div style="display: flex; gap: 0.5rem;">
        <button class="filter-btn active" data-filter="all">All Tasks</button>
        <button class="filter-btn" data-filter="todo">To Do</button>
        <button class="filter-btn" data-filter="in_progress">In Progress</button>
        <button class="filter-btn" data-filter="done">Done</button>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <button class="view-btn active" data-view="kanban">
            <i class="fas fa-th"></i> Kanban
        </button>
        <button class="view-btn" data-view="list">
            <i class="fas fa-list"></i> List
        </button>
    </div>
</div>

<!-- Kanban View -->
<div id="kanban-view" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
    <!-- To Do Column -->
    <div class="kanban-column">
        <div class="kanban-header" style="background: var(--text-muted);">
            <i class="fas fa-circle"></i>
            <span>To Do</span>
            <span class="task-count" id="todo-count">0</span>
        </div>
        <div class="kanban-body" id="todo-tasks">
            <p style="text-align: center; padding: 2rem; color: var(--text-muted);">Loading...</p>
        </div>
    </div>
    
    <!-- In Progress Column -->
    <div class="kanban-column">
        <div class="kanban-header" style="background: var(--warning);">
            <i class="fas fa-spinner"></i>
            <span>In Progress</span>
            <span class="task-count" id="progress-count">0</span>
        </div>
        <div class="kanban-body" id="progress-tasks">
            <p style="text-align: center; padding: 2rem; color: var(--text-muted);">Loading...</p>
        </div>
    </div>
    
    <!-- Done Column -->
    <div class="kanban-column">
        <div class="kanban-header" style="background: var(--success);">
            <i class="fas fa-check-circle"></i>
            <span>Done</span>
            <span class="task-count" id="done-count">0</span>
        </div>
        <div class="kanban-body" id="done-tasks">
            <p style="text-align: center; padding: 2rem; color: var(--text-muted);">Loading...</p>
        </div>
    </div>
</div>

<!-- List View -->
<div id="list-view" style="display: none;">
    <div class="card">
        <div id="tasks-list">
            <p style="text-align: center; padding: 2rem; color: var(--text-muted);">Loading tasks...</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.filter-btn, .view-btn {
    padding: 0.6rem 1.2rem;
    background: var(--bg-card-hover);
    border: none;
    border-radius: 8px;
    color: var(--text-secondary);
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.filter-btn:hover, .view-btn:hover {
    background: var(--border);
    color: var(--text-primary);
}

.filter-btn.active, .view-btn.active {
    background: var(--primary-blue);
    color: white;
}

.kanban-column {
    background: var(--bg-card);
    border-radius: 12px;
    overflow: hidden;
}

.kanban-header {
    padding: 1rem;
    color: white;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.kanban-header .task-count {
    margin-left: auto;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.2rem 0.6rem;
    border-radius: 10px;
    font-size: 0.85rem;
}

.kanban-body {
    padding: 1rem;
    min-height: 400px;
}

.task-card {
    background: var(--bg-dark);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    border-left: 3px solid var(--primary-blue);
    cursor: pointer;
    transition: transform 0.2s;
}

.task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.task-project {
    font-size: 0.75rem;
    color: var(--primary-blue);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.task-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.task-meta {
    font-size: 0.8rem;
    color: var(--text-muted);
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.priority-badge {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.priority-low { background: #6B7280; }
.priority-medium { background: var(--warning); }
.priority-high { background: var(--danger); }

.task-list-item {
    padding: 1.25rem;
    border-bottom: 1px solid var(--border);
    cursor: pointer;
    transition: background 0.3s;
}

.task-list-item:hover {
    background: var(--bg-card-hover);
}

.task-list-item:last-child {
    border-bottom: none;
}
</style>
@endpush

@push('scripts')
<script>
let allTasks = [];
let currentFilter = 'all';
let currentView = 'kanban';

$(document).ready(function() {
    loadMyTasks();
    
    // Filter buttons
    $('.filter-btn').click(function() {
        currentFilter = $(this).data('filter');
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        renderTasks();
    });
    
    // View toggle
    $('.view-btn').click(function() {
        currentView = $(this).data('view');
        $('.view-btn').removeClass('active');
        $(this).addClass('active');
        
        if (currentView === 'kanban') {
            $('#kanban-view').show();
            $('#list-view').hide();
        } else {
            $('#kanban-view').hide();
            $('#list-view').show();
        }
        renderTasks();
    });
});

function loadMyTasks() {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    
    // Load all projects and get tasks assigned to me
    $.get('/api/v1/projects')
        .done(function(response) {
            const projects = response.data || response;
            let promises = [];
            
            projects.forEach(project => {
                promises.push(
                    $.get(`/api/v1/projects/${project.id}/tasks`)
                        .then(taskResponse => ({
                            project: project,
                            tasks: (taskResponse.data || taskResponse).filter(t => t.assigned_to === user.id)
                        }))
                );
            });
            
            Promise.all(promises).then(results => {
                allTasks = [];
                results.forEach(result => {
                    result.tasks.forEach(task => {
                        task.project = result.project;
                        allTasks.push(task);
                    });
                });
                
                renderTasks();
            });
        });
}

function renderTasks() {
    let filtered = allTasks;
    
    if (currentFilter !== 'all') {
        filtered = allTasks.filter(t => t.status === currentFilter);
    }
    
    if (currentView === 'kanban') {
        renderKanban(filtered);
    } else {
        renderList(filtered);
    }
}

function renderKanban(tasks) {
    const todoTasks = tasks.filter(t => t.status === 'todo');
    const progressTasks = tasks.filter(t => t.status === 'in_progress');
    const doneTasks = tasks.filter(t => t.status === 'done');
    
    $('#todo-count').text(todoTasks.length);
    $('#progress-count').text(progressTasks.length);
    $('#done-count').text(doneTasks.length);
    
    renderTaskColumn('todo-tasks', todoTasks);
    renderTaskColumn('progress-tasks', progressTasks);
    renderTaskColumn('done-tasks', doneTasks);
}

function renderTaskColumn(containerId, tasks) {
    if (tasks.length === 0) {
        $(`#${containerId}`).html('<p style="text-align: center; padding: 2rem; color: var(--text-muted);">No tasks</p>');
        return;
    }
    
    let html = '';
    tasks.forEach(task => {
        const deadline = task.deadline ? new Date(task.deadline).toLocaleDateString() : 'No deadline';
        const isOverdue = task.deadline && new Date(task.deadline) < new Date() && task.status !== 'done';
        
        html += `
            <div class="task-card" onclick="goToProject(${task.project.id})">
                <div class="task-project">
                    <i class="fas fa-folder"></i> ${task.project.name}
                </div>
                <div class="task-title">${task.title}</div>
                ${task.description ? `<div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;">${task.description.substring(0, 100)}${task.description.length > 100 ? '...' : ''}</div>` : ''}
                <div class="task-meta">
                    <span ${isOverdue ? 'style="color: var(--danger);"' : ''}><i class="fas fa-calendar"></i> ${deadline}</span>
                </div>
                <div style="margin-top: 0.5rem;">
                    <span class="priority-badge priority-${task.priority}">${task.priority}</span>
                </div>
            </div>
        `;
    });
    
    $(`#${containerId}`).html(html);
}

function renderList(tasks) {
    if (tasks.length === 0) {
        $('#tasks-list').html('<p style="text-align: center; padding: 2rem; color: var(--text-muted);">No tasks found</p>');
        return;
    }
    
    let html = '';
    tasks.forEach(task => {
        const deadline = task.deadline ? new Date(task.deadline).toLocaleDateString() : 'No deadline';
        const statusClass = task.status === 'done' ? 'success' : task.status === 'in_progress' ? 'warning' : 'text-muted';
        
        html += `
            <div class="task-list-item" onclick="goToProject(${task.project.id})">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                    <div>
                        <div style="font-size: 0.8rem; color: var(--primary-blue); margin-bottom: 0.25rem;">
                            <i class="fas fa-folder"></i> ${task.project.name}
                        </div>
                        <div style="font-weight: 600; font-size: 1.1rem;">${task.title}</div>
                    </div>
                    <span class="priority-badge priority-${task.priority}">${task.priority}</span>
                </div>
                ${task.description ? `<div style="color: var(--text-secondary); margin-bottom: 0.5rem;">${task.description}</div>` : ''}
                <div style="display: flex; gap: 1.5rem; font-size: 0.85rem; color: var(--text-muted);">
                    <span><i class="fas fa-calendar"></i> ${deadline}</span>
                    <span style="color: var(--${statusClass});">
                        <i class="fas fa-circle"></i> ${task.status.replace('_', ' ').toUpperCase()}
                    </span>
                </div>
            </div>
        `;
    });
    
    $('#tasks-list').html(html);
}

function goToProject(projectId) {
    window.location.href = `/projects/${projectId}`;
}

function updateTaskStatus(taskId, newStatus) {
    $.ajax({
        url: `/api/v1/tasks/${taskId}/status`,
        method: 'PUT',
        data: { status: newStatus },
        success: function() {
            showToast('Task status updated', 'success');
            loadMyTasks();
        },
        error: function() {
            showToast('Failed to update task', 'error');
        }
    });
}
</script>
@endpush
