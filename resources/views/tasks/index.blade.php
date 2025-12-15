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

<!-- Edit Task Modal -->
<div id="taskModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Task</h3>
            <span class="modal-close" onclick="closeTaskModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="taskForm">
                <input type="hidden" id="task-id">
                <input type="hidden" id="task-project-id">
                
                <div class="form-group">
                    <label>Task Title *</label>
                    <input type="text" id="task-title" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="task-description" class="form-input" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select id="task-status" class="form-input">
                        <option value="todo">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="done">Done</option>
                    </select>
                </div>
                
                <div class="form-group" id="group-task-assignee">
                    <label>Assignees</label>
                    <select id="task-assignee" class="form-input" multiple>
                        <!-- Options populated via JS -->
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group" id="group-task-priority">
                        <label>Priority</label>
                        <select id="task-priority" class="form-input">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="form-group" id="group-task-start-date">
                        <label>Start Date</label>
                        <input type="date" id="task-start-date" class="form-input">
                    </div>
                </div>
                
                <div class="form-group" id="group-task-deadline">
                    <label>Deadline</label>
                    <input type="date" id="task-deadline" class="form-input">
                </div>
               
                <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeTaskModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Modal Styles for Tasks Page */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: var(--bg-card);
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-close {
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-secondary);
}

.modal-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.form-input {
    width: 100%;
    padding: 0.75rem;
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

textarea.form-input {
    resize: vertical;
    font-family: inherit;
}
</style>
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
    cursor: pointer;
}

.task-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    cursor: pointer;
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
    console.log('Page loaded, loading tasks...');
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
    console.log('Starting loadMyTasks...');
    
    // Get current user from localStorage or session
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    console.log('Current user:', user);
    
    if (!user.id) {
        console.error('User ID not found');
        showToast('Please login first', 'error');
        return;
    }
    
    // Use the getAllTasks endpoint instead
    $.get('/api/v1/tasks')
        .done(function(response) {
            console.log('Tasks API response:', response);
            
            // Handle different response structures
            let tasks = [];
            if (response.data) {
                tasks = response.data;
            } else if (Array.isArray(response)) {
                tasks = response;
            } else if (response.success && response.data) {
                tasks = response.data;
            }
            
            console.log('Parsed tasks:', tasks);
            
            // Filter tasks assigned to current user
            allTasks = tasks.filter(task => {
                const isAssignedTo = task.assigned_to === user.id;
                const hasAssignees = task.assignees && Array.isArray(task.assignees);
                const isInAssignees = hasAssignees && task.assignees.some(a => a.id === user.id);
                
                return isAssignedTo || isInAssignees;
            });
            
            console.log('Filtered tasks for user:', allTasks);
            
            if (allTasks.length === 0) {
                console.log('No tasks found for user');
            }
            
            renderTasks();
        })
        .fail(function(xhr, status, error) {
            console.error('Failed to load tasks:', {xhr, status, error});
            showToast('Failed to load tasks', 'error');
            
            // Show error in all containers
            const errorHtml = '<p style="text-align: center; padding: 2rem; color: var(--danger);">Failed to load tasks</p>';
            $('#todo-tasks, #progress-tasks, #done-tasks, #tasks-list').html(errorHtml);
        });
}

function renderTasks() {
    console.log('Rendering tasks, current filter:', currentFilter, 'view:', currentView);
    
    let filtered = allTasks;
    
    if (currentFilter !== 'all') {
        filtered = allTasks.filter(t => t.status === currentFilter);
    }
    
    console.log('Filtered tasks:', filtered);
    
    if (currentView === 'kanban') {
        renderKanban(filtered);
    } else {
        renderList(filtered);
    }
}

function renderKanban(tasks) {
    console.log('Rendering kanban with', tasks.length, 'tasks');
    
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
    console.log('Rendering column', containerId, 'with', tasks.length, 'tasks');
    
    if (tasks.length === 0) {
        $(`#${containerId}`).html('<p style="text-align: center; padding: 2rem; color: var(--text-muted);">No tasks</p>');
        return;
    }
    
    let html = '';
    tasks.forEach(task => {
        const projectName = task.project ? task.project.name : 'Unknown Project';
        const projectId = task.project ? task.project.id : task.project_id;
        const deadline = task.deadline ? new Date(task.deadline).toLocaleDateString() : 'No deadline';
        const isOverdue = task.deadline && new Date(task.deadline) < new Date() && task.status !== 'done';
        
        html += `
            <div class="task-card">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div class="task-project" onclick="goToProject(${projectId})">
                        <i class="fas fa-folder"></i> ${projectName}
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: start;">
                     <div class="task-title" onclick="goToProject(${projectId})">${task.title}</div>
                     <button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); editTask(${task.id})" title="Edit Task" style="padding: 0.2rem 0.5rem;">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>

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
    console.log('Rendering list with', tasks.length, 'tasks');
    
    if (tasks.length === 0) {
        $('#tasks-list').html('<p style="text-align: center; padding: 2rem; color: var(--text-muted);">No tasks found</p>');
        return;
    }
    
    let html = '';
    tasks.forEach(task => {
        const projectName = task.project ? task.project.name : 'Unknown Project';
        const projectId = task.project ? task.project.id : task.project_id;
        const deadline = task.deadline ? new Date(task.deadline).toLocaleDateString() : 'No deadline';
        const statusClass = task.status === 'done' ? 'success' : task.status === 'in_progress' ? 'warning' : 'text-muted';
        const statusText = task.status.replace('_', ' ').toUpperCase();
        
        html += `
            <div class="task-list-item" onclick="goToProject(${projectId})">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                    <div>
                        <div style="font-size: 0.8rem; color: var(--primary-blue); margin-bottom: 0.25rem;">
                            <i class="fas fa-folder"></i> ${projectName}
                        </div>
                        <div style="font-weight: 600; font-size: 1.1rem;">${task.title}</div>
                    </div>
                    <span class="priority-badge priority-${task.priority}">${task.priority}</span>
                </div>
                ${task.description ? `<div style="color: var(--text-secondary); margin-bottom: 0.5rem;">${task.description}</div>` : ''}
                <div style="display: flex; gap: 1.5rem; font-size: 0.85rem; color: var(--text-muted);">
                    <span><i class="fas fa-calendar"></i> ${deadline}</span>
                    <span style="color: var(--${statusClass});">
                        <i class="fas fa-circle"></i> ${statusText}
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

function editTask(taskId) {
    console.log('Editing task:', taskId);
    
    // Find task in allTasks
    const task = allTasks.find(t => t.id === taskId);
    if (!task) {
        console.error('Task not found:', taskId);
        showToast('Task not found', 'error');
        return;
    }

    console.log('Task found:', task);

    $('#task-id').val(task.id);
    $('#task-title').val(task.title);
    $('#task-description').val(task.description || '');
    $('#task-status').val(task.status);
    $('#task-priority').val(task.priority);
    $('#task-start-date').val(task.start_date ? task.start_date.split('T')[0] : '');
    $('#task-deadline').val(task.deadline ? task.deadline.split('T')[0] : '');
    $('#task-project-id').val(task.project_id); 
    
    // Check permissions & Populate Assignees
    const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
    
    // Reset visibility first
    $('#task-title').prop('disabled', false); 
    $('#group-task-assignee').show();
    $('#group-task-priority').show();
    $('#group-task-start-date').show();
    $('#group-task-deadline').show();

    $.get(`/api/v1/projects/${task.project_id}`)
        .done(function(response) {
            const project = response.data || response;
            const member = project.members ? project.members.find(m => m.id === currentUser.id) : null;
            
            const userGlobalRole = currentUser.role; 
            const projectRole = member ? member.pivot.role : null;
            
            // Robust Check: admin override
            const isAdmin = userGlobalRole === 'admin' || currentUser.is_admin;
            const isRestricted = !isAdmin && projectRole === 'member';

            // Populate Assignee Options
            let options = '';
            if (project.members) {
                 project.members.forEach(m => {
                    options += `<option value="${m.id}">${m.name}</option>`;
                });
            }
            $('#task-assignee').html(options);
            
            // Set current assignees
            const currentAssigneeIds = task.assignees ? task.assignees.map(a => a.id) : [];
            $('#task-assignee').val(currentAssigneeIds);
            
            // Initialize Select2 if not already
            if (typeof $.fn.select2 !== 'undefined') {
                if (!$('#task-assignee').data('select2')) {
                    $('#task-assignee').select2({
                        placeholder: 'Select assignees',
                        allowClear: true,
                        width: '100%'
                    });
                }
                $('#task-assignee').trigger('change');
            }

            if (isRestricted) {
                 $('#task-title').prop('disabled', true); 
                 $('#group-task-assignee').hide();
                 $('#group-task-priority').hide();
                 $('#group-task-start-date').hide();
                 $('#group-task-deadline').hide();
            }
            
            $('#taskModal').addClass('show');
        })
        .fail(function(xhr) {
             console.error('Failed to load project:', xhr);
             showToast('Failed to load project details', 'error');
        });
}
window.editTask = editTask;

function closeTaskModal() {
    $('#taskModal').removeClass('show');
    // Destroy Select2 to avoid issues
    if (typeof $.fn.select2 !== 'undefined' && $('#task-assignee').data('select2')) {
        $('#task-assignee').select2('destroy');
    }
}
window.closeTaskModal = closeTaskModal;

$('#taskForm').submit(function(e) {
    e.preventDefault();
    const taskId = $('#task-id').val();
    
    const data = {
        title: $('#task-title').val(),
        description: $('#task-description').val(),
        status: $('#task-status').val(),
        priority: $('#task-priority').val(),
        assignees: $('#task-assignee').val() || [],
        start_date: $('#task-start-date').val() || null,
        deadline: $('#task-deadline').val() || null
    };
    
    console.log('Updating task:', taskId, data);
    
    $.ajax({
        url: `/api/v1/tasks/${taskId}`,
        method: 'PUT',
        data: data,
        success: function(response) {
            console.log('Task updated:', response);
            showToast('Task updated successfully', 'success');
            closeTaskModal();
            loadMyTasks(); // Reload tasks
        },
        error: function(xhr) {
            console.error('Failed to update task:', xhr);
            showToast(xhr.responseJSON?.message || 'Failed to update task', 'error');
        }
    });
});
</script>
@endpush