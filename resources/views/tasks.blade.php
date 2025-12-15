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

<!-- Edit Task Modal (Replicated from projects/show.blade.php) -->
<div id="taskModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="task-modal-title">Edit Task</h3>
            <span class="modal-close" onclick="closeTaskModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="taskForm">
                <input type="hidden" id="task-id">
                <!-- Hidden Project ID -->
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

                <!-- Restricted Fields Wrapper for JS Toggling -->
                <div id="task-restricted-fields">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Assign To</label>
                            <select id="task-assignee" class="form-input" multiple>
                                <option value="">Select assignees...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Priority</label>
                            <select id="task-priority" class="form-input">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" id="task-start-date" class="form-input">
                        </div>
                        <div class="form-group">
                            <label>Deadline</label>
                            <input type="date" id="task-deadline" class="form-input">
                        </div>
                    </div>
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
/* Re-using styles from project show page where possible */
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
/* Kanban & List Styles */
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
const currentUser = @json(auth()->user());

$(document).ready(function() {
    console.log("Tasks page script initialized");
    loadTasks();
    
    // Filter buttons
    $('#filter-btn').click(function() {
        // ... (existing filter logic if any)
    });
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

    // Edit Task Form Submit
    $('#taskForm').submit(function(e) {
        e.preventDefault();
        saveTask();
    });

    // Drag and Drop Logic Initialization
    setupDragAndDrop();
});

function loadTasks() {
    console.log('Loading tasks...');
    $('#todo-tasks, #progress-tasks, #done-tasks, #tasks-list').html('<p style="text-align: center; padding: 2rem; color: var(--text-muted);">Loading tasks...</p>');

    $.get('/api/v1/tasks')
        .done(function(response) {
            console.log('Tasks fetched:', response);
            let tasks = response.data || response;
            if (!tasks) tasks = [];
            
            allTasks = tasks;
            renderTasks();
        })
        .fail(function(xhr) {
            console.error('Failed to load tasks:', xhr);
            showToast('Failed to load tasks', 'error');
            const errorHtml = '<p style="text-align: center; padding: 2rem; color: var(--danger);">Failed to load tasks</p>';
            $('#todo-tasks, #progress-tasks, #done-tasks, #tasks-list').html(errorHtml);
        });
}

function renderTasks() {
    let filtered = allTasks;
    
    if (currentFilter !== 'all') {
        filtered = allTasks.filter(t => t.status === currentFilter);
    }
    
    // Update counts
    const todoCount = allTasks.filter(t => t.status === 'todo').length;
    const progressCount = allTasks.filter(t => t.status === 'in_progress').length;
    const doneCount = allTasks.filter(t => t.status === 'done').length;

    $('#todo-count').text(todoCount);
    $('#progress-count').text(progressCount);
    $('#done-count').text(doneCount);
    
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
    
    renderTaskColumn('todo', todoTasks);
    renderTaskColumn('in_progress', progressTasks);
    renderTaskColumn('done', doneTasks);
}

function renderTaskColumn(status, tasks) {
    const containerId = status === 'in_progress' ? 'progress-tasks' : `${status}-tasks`;
    
    if (tasks.length === 0) {
        $(`#${containerId}`).html('<p style="text-align: center; padding: 2rem; color: var(--text-muted);">No tasks</p>');
        return;
    }
    
    let html = '';
    tasks.forEach(task => {
        const deadline = task.deadline ? new Date(task.deadline).toLocaleDateString() : 'No deadline';
        const isOverdue = task.deadline && new Date(task.deadline) < new Date() && task.status !== 'done';
        const projectId = task.project ? task.project.id : (task.project_id || '');
        const projectName = task.project ? task.project.name : 'Unknown Project';
        
        let assigneesHtml = '';
        if (task.assignees && task.assignees.length > 0) {
            assigneesHtml = '<div style="display: flex; -space-x-2: 0.5rem;" title="' + task.assignees.map(a => a.name).join(', ') + '">' + 
                task.assignees.slice(0, 3).map(a => `
                    <div style="width: 24px; height: 24px; background: var(--primary-blue); color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; border: 2px solid var(--bg-card); margin-right: -8px;">
                        ${a.name.charAt(0).toUpperCase()}
                    </div>
                `).join('') +
                (task.assignees.length > 3 ? `<div style="width: 24px; height: 24px; background: var(--bg-dark); color: var(--text-muted); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; border: 2px solid var(--border); margin-right: -8px;">+${task.assignees.length - 3}</div>` : '') +
                '</div>';
        } else {
            assigneesHtml = '<span style="color: var(--text-muted); font-size: 0.8rem;">Unassigned</span>';
        }

        html += `
            <div class="task-card" draggable="true" data-task-id="${task.id}" data-status="${status}">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div class="task-project" onclick="goToProject(${projectId})">
                        <i class="fas fa-folder"></i> ${projectName}
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: start;">
                     <div class="task-title" onclick="goToProject(${projectId})">${task.title}</div>
                     <button class="btn btn-sm btn-secondary" onclick="editTask(${task.id})" title="Edit Task" style="padding: 0.2rem 0.5rem; background: none; border: none; color: var(--text-secondary);">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                ${task.description ? `<div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;">${task.description.substring(0, 100)}${task.description.length > 100 ? '...' : ''}</div>` : ''}
                
                <div class="task-meta">
                    <span ${isOverdue ? 'style="color: var(--danger);"' : ''}><i class="fas fa-calendar"></i> ${deadline}</span>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                    ${assigneesHtml}
                    <span class="priority-badge priority-${task.priority}">${task.priority}</span>
                </div>
            </div>
        `;
    });
    
    $(`#${containerId}`).html(html);

    // Re-attach Drag and Drop handlers
    setupDragAndDrop();
}

function setupDragAndDrop() {
    $('.task-card').off('dragstart').on('dragstart', function(e) {
        e.originalEvent.dataTransfer.setData('taskId', $(this).data('task-id'));
        e.originalEvent.dataTransfer.setData('oldStatus', $(this).data('status'));
    });
    
    $('.kanban-column').off('dragover').on('dragover', function(e) {
        e.preventDefault();
        $(this).css('background', 'var(--bg-card-hover)');
    });
    
    $('.kanban-column').off('dragleave').on('dragleave', function(e) {
        $(this).css('background', 'var(--bg-card)');
    });
    
    $('.kanban-column').off('drop').on('drop', function(e) {
        e.preventDefault();
        $(this).css('background', 'var(--bg-card)');
        
        let newStatus = 'todo';
        const column = $(e.target).closest('.kanban-column');

        // Identify status from IDs inside the column
        if (column.find('#progress-tasks').length) newStatus = 'in_progress';
        if (column.find('#done-tasks').length) newStatus = 'done';
        if (column.find('#todo-tasks').length) newStatus = 'todo';

        const taskId = e.originalEvent.dataTransfer.getData('taskId');
        const oldStatus = e.originalEvent.dataTransfer.getData('oldStatus');
        
        if (oldStatus !== newStatus && taskId) {
            updateTaskStatus(taskId, newStatus);
        }
    });
}

function renderList(tasks) {
    if (tasks.length === 0) {
        $('#tasks-list').html('<p style="text-align: center; padding: 2rem; color: var(--text-muted);">No tasks found</p>');
        return;
    }
    
    let html = '';
    tasks.forEach(task => {
        const deadline = task.deadline ? new Date(task.deadline).toLocaleDateString() : 'No deadline';
        const projectId = task.project ? task.project.id : (task.project_id || '');
        const projectName = task.project ? task.project.name : 'Unknown Project';
        const statusClass = task.status === 'done' ? 'success' : task.status === 'in_progress' ? 'warning' : 'text-muted';
        
        let assigneesHtml = '';
        if (task.assignees && task.assignees.length > 0) {
            assigneesHtml = '<div style="display: flex; -space-x-2: 0.5rem; margin-right: 1rem;">' + 
                task.assignees.slice(0,3).map(a => `
                    <div title="${a.name}" style="width: 24px; height: 24px; background: var(--primary-blue); color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; border: 2px solid var(--bg-card); margin-right: -8px;">
                        ${a.name.charAt(0).toUpperCase()}
                    </div>
                `).join('') + 
                '</div>';
        }

        html += `
            <div class="task-list-item" onclick="goToProject(${projectId})">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                    <div>
                        <div style="font-size: 0.8rem; color: var(--primary-blue); margin-bottom: 0.25rem;">
                            <i class="fas fa-folder"></i> ${projectName}
                        </div>
                        <div style="font-weight: 600; font-size: 1.1rem;">${task.title}</div>
                    </div>
                    <div style="display: flex; align-items: center;">
                        ${assigneesHtml}
                        <span class="priority-badge priority-${task.priority}">${task.priority}</span>
                    </div>
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

function updateTaskStatus(taskId, newStatus) {
    // Optimistic update
    const task = allTasks.find(t => t.id == taskId);
    if(task) {
        task.status = newStatus;
        renderTasks(); 
    }

    $.ajax({
        url: `/api/v1/tasks/${taskId}`,
        method: 'PUT',
        data: { status: newStatus },
        success: function(response) {
            showToast('Task status updated', 'success');
            // Background refresh
            $.get('/api/v1/tasks').done(function(r){ allTasks = r.data || r; });
        },
        error: function(xhr) {
            showToast('Failed to update task', 'error');
            // Revert on error
            if(task) {
                task.status = (newStatus === 'done' ? 'in_progress' : 'todo'); 
                loadTasks();
            }
        }
    });
}

function goToProject(projectId) {
    if(projectId) window.location.href = `/projects/${projectId}`;
}

// ----------------------------------------------------
// KEY CHANGE: Dynamic Permission and Member Loading
// ----------------------------------------------------
function editTask(taskId) {
    // Find task in allTasks
    const task = allTasks.find(t => t.id === taskId);
    if (!task) {
        console.error('Task not found:', taskId);
        return;
    }

    // Populate basic fields
    $('#task-id').val(task.id);
    $('#task-title').val(task.title);
    $('#task-description').val(task.description);
    $('#task-status').val(task.status);
    $('#task-priority').val(task.priority);
    $('#task-start-date').val(task.start_date ? task.start_date.split('T')[0] : '');
    $('#task-deadline').val(task.deadline ? task.deadline.split('T')[0] : '');
    $('#task-project-id').val(task.project_id); 
    
    $('#taskModal').addClass('show'); 
    
    // Determine Permissions and Populate Assignees
    // We fetch the project details to check the user's role in THIS specific project
    if(task.project_id) {
        $.get(`/api/v1/projects/${task.project_id}`)
            .done(function(response) {
                const project = response.data || response;
                
                // Find current user's role in this project
                const member = project.members ? project.members.find(m => m.id === currentUser.id) : null;
                const projectRole = member ? member.pivot.role : null;
                const isGlobalAdmin = currentUser.role === 'admin' || currentUser.is_admin; 
                
                // Logic: Restricted if NOT global admin AND is just a 'member' role in project
                const isRestricted = !isGlobalAdmin && (projectRole === 'member');

                if (isRestricted) {
                    $('#task-restricted-fields').hide();
                } else {
                    $('#task-restricted-fields').show();
                    
                    // Populate Assignee Options
                    let options = '';
                    if (project.members) {
                         project.members.forEach(m => {
                            options += `<option value="${m.id}">${m.name}</option>`;
                        });
                    }
                    $('#task-assignee').html(options);
                    
                    // Set selected assignees
                    const currentAssigneeIds = task.assignees ? task.assignees.map(a => a.id) : [];
                    $('#task-assignee').val(currentAssigneeIds);
                    
                    // Initialize Select2 if not already
                     if (!$('#task-assignee').data('select2')) {
                        $('#task-assignee').select2({
                            placeholder: 'Select assignees',
                            allowClear: true,
                            width: '100%'
                        });
                    }
                    $('#task-assignee').trigger('change');
                }
            })
            .fail(function() {
                 showToast('Failed to load project details for permission check', 'error');
                 // Default to hiding if we can't verify
                 $('#task-restricted-fields').hide();
            });
    } else {
        // Fallback if no project ID (shouldn't happen for valid tasks)
        $('#task-restricted-fields').hide();
    }
}
window.editTask = editTask;

function closeTaskModal() {
    $('#taskModal').removeClass('show');
    // Destroy Select2 to avoid issues
     if ($('#task-assignee').data('select2')) {
        $('#task-assignee').select2('destroy');
    }
}
window.closeTaskModal = closeTaskModal;

function saveTask() {
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
    
    $.ajax({
        url: `/api/v1/tasks/${taskId}`,
        method: 'PUT',
        data: data,
        success: function() {
            showToast('Task updated successfully', 'success');
            closeTaskModal();
            loadTasks(); 
        },
        error: function(xhr) {
             showToast(xhr.responseJSON?.message || 'Failed to update task', 'error');
        }
    });
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}
</script>
@endpush
