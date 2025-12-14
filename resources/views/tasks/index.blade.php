@extends('layouts.app')

@section('title', 'Tasks Board')

@section('content')
<div class="page-header">
    <h1 class="page-title"><i class="fas fa-tasks"></i> Task Board</h1>
    <div style="display: flex; gap: 1rem;">
        <select id="projectFilter" class="form-input" style="width: 250px;">
            <option value="">All Projects</option>
        </select>
        @can('create', App\Models\Task::class)
        <button class="btn btn-primary" id="newTaskBtn">
            <i class="fas fa-plus"></i> New Task
        </button>
        @endcan
    </div>
</div>

<!-- Kanban Board -->
<div class="kanban-board">
    <!-- TODO Column -->
    <div class="kanban-column" data-status="todo">
        <div class="kanban-header">
            <h3><i class="fas fa-circle" style="color: #6B7280;"></i> To Do</h3>
            <span class="task-count" id="count-todo">0</span>
        </div>
        <div class="kanban-tasks" id="tasks-todo">
            <!-- Tasks will be loaded here -->
        </div>
    </div>

    <!-- IN PROGRESS Column -->
    <div class="kanban-column" data-status="in_progress">
        <div class="kanban-header">
            <h3><i class="fas fa-circle" style="color: #F59E0B;"></i> In Progress</h3>
            <span class="task-count" id="count-in_progress">0</span>
        </div>
        <div class="kanban-tasks" id="tasks-in_progress">
            <!-- Tasks will be loaded here -->
        </div>
    </div>

    <!-- DONE Column -->
    <div class="kanban-column" data-status="done">
        <div class="kanban-header">
            <h3><i class="fas fa-circle" style="color: #10B981;"></i> Done</h3>
            <span class="task-count" id="count-done">0</span>
        </div>
        <div class="kanban-tasks" id="tasks-done">
            <!-- Tasks will be loaded here -->
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div id="addTaskModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New Task</h3>
            <span class="modal-close" onclick="closeAddTaskModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="taskForm">
                <div class="form-group">
                    <label>Project <span style="color: red;">*</span></label>
                    <select id="task-project" required class="form-input">
                        <option value="">Select Project</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Task Title <span style="color: red;">*</span></label>
                    <input type="text" id="task-title" required class="form-input" placeholder="e.g. Design login page">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="task-description" class="form-input" rows="3" placeholder="Detailed description..."></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Priority</label>
                        <select id="task-priority" class="form-input">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assign To</label>
                        <select id="task-assignee" class="form-input">
                            <option value="">Unassigned</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deadline</label>
                    <input type="date" id="task-deadline" class="form-input">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-plus"></i> Create Task
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.kanban-board {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-top: 2rem;
}

.kanban-column {
    background: var(--bg-card);
    border-radius: 12px;
    border: 1px solid var(--border);
    min-height: 500px;
    display: flex;
    flex-direction: column;
}

.kanban-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem;
    border-bottom: 2px solid var(--border);
    background: var(--bg-card-hover);
    border-radius: 12px 12px 0 0;
}

.kanban-header h3 {
    margin: 0;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.task-count {
    background: var(--bg-dark);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-secondary);
}

.kanban-tasks {
    padding: 1rem;
    flex: 1;
    overflow-y: auto;
    min-height: 400px;
}

.kanban-tasks.drag-over {
    background: rgba(79, 127, 255, 0.05);
    border: 2px dashed var(--primary-blue);
}

.task-card {
    background: var(--bg-dark);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    cursor: move;
    transition: all 0.2s;
    position: relative;
}

.task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-color: var(--primary-blue);
}

.task-card.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
}

.task-priority {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.task-priority.high { background: #EF4444; }
.task-priority.medium { background: #F59E0B; }
.task-priority.low { background: #10B981; }

.task-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
    padding-right: 1.5rem;
}

.task-meta {
    display: flex;
    gap: 1rem;
    margin-top: 0.75rem;
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.task-meta span {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.task-project {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

.task-assignee {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--bg-card);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    margin-top: 0.5rem;
}

.task-deadline {
    color: var(--text-secondary);
    font-size: 0.85rem;
}

.task-deadline.overdue {
    color: #EF4444;
    font-weight: 600;
}

@media (max-width: 1024px) {
    .kanban-board {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
let allTasks = [];
let projects = [];
let currentDraggedTask = null;

$(document).ready(function() {
    loadProjects();
    loadAllTasks();
    
    // Bind New Task button
    $('#newTaskBtn').click(function() {
        openAddTaskModal();
    });
});

function loadProjects() {
    $.get('/api/v1/projects')
        .done(response => {
            projects = response.data || response;
            let options = '<option value="">Select Project</option>';
            let filterOptions = '<option value="">All Projects</option>';
            
            projects.forEach(project => {
                options += `<option value="${project.id}">${project.name}</option>`;
                filterOptions += `<option value="${project.id}">${project.name}</option>`;
            });
            
            $('#task-project').html(options);
            $('#projectFilter').html(filterOptions);
            
            // Load users for assignment
            loadUsers();
        })
        .fail(() => showToast('Failed to load projects', 'error'));
}

function loadUsers() {
    $.get('/api/v1/users')
        .done(response => {
            const users = response.data || response;
            let options = '<option value="">Unassigned</option>';
            users.forEach(user => {
                options += `<option value="${user.id}">${user.name}</option>`;
            });
            $('#task-assignee').html(options);
        });
}

function loadTasks(projectId = null) {
    let url = '/api/v1/projects/1/tasks'; // We'll load all tasks
    
    // For now, load tasks from all projects
    // TODO: Implement endpoint to get all user's tasks
    
    $.get('/api/v1/projects')
        .done(response => {
            const projects = response.data || response;
            allTasks = [];
            
            let promises = projects.map(project => 
                $.get(`/api/v1/projects/${project.id}/tasks`)
                    .done(tasks => {
                        allTasks = allTasks.concat(tasks.map(task => ({
                            ...task,
                            project_name: project.name
                        })));
                    })
            );
            
            Promise.all(promises).then(() => {
                renderKanban(projectId);
            });
        });
}

function renderKanban(filterProjectId = null) {
    let tasks = allTasks;
    
    if (filterProjectId) {
        tasks = tasks.filter(t => t.project_id == filterProjectId);
    }
    
    // Group by status
    const byStatus = {
        todo: tasks.filter(t => t.status === 'todo'),
        in_progress: tasks.filter(t => t.status === 'in_progress'),
        done: tasks.filter(t => t.status === 'done')
    };
    
    // Update counts
    $('#count-todo').text(byStatus.todo.length);
    $('#count-in_progress').text(byStatus.in_progress.length);
    $('#count-done').text(byStatus.done.length);
    
    // Render each column
    Object.keys(byStatus).forEach(status => {
        const $container = $(`#tasks-${status}`);
        $container.empty();
        
        if (byStatus[status].length === 0) {
            $container.append(`
                <p style="text-align: center; color: var(--text-muted); padding: 2rem;">
                    No tasks yet
                </p>
            `);
        } else {
            byStatus[status].forEach(task => {
                $container.append(createTaskCard(task));
            });
        }
    });
    
    // Enable drag and drop
    enableDragAndDrop();
}

function createTaskCard(task) {
    const priorityClass = task.priority || 'medium';
    const assigneeName = task.assignee?.name || 'Unassigned';
    const deadlineText = task.deadline ? new Date(task.deadline).toLocaleDateString() : 'No deadline';
    const isOverdue = task.deadline && new Date(task.deadline) < new Date() && task.status !== 'done';
    
    return `
        <div class="task-card" draggable="true" data-task-id="${task.id}" data-project-id="${task.project_id}">
            <div class="task-priority ${priorityClass}"></div>
            <div class="task-project">📁 ${task.project_name || 'Unknown Project'}</div>
            <div class="task-title">${task.title}</div>
            ${task.description ? `<div style="font-size: 0.9rem; color: var(--text-secondary); margin-top: 0.5rem;">${task.description.substring(0, 80)}${task.description.length > 80 ? '...' : ''}</div>` : ''}
            <div class="task-assignee">
                <i class="fas fa-user"></i> ${assigneeName}
            </div>
            ${task.deadline ? `<div class="task-deadline ${isOverdue ? 'overdue' : ''}" style="margin-top: 0.5rem;">
                <i class="fas fa-clock"></i> ${deadlineText} ${isOverdue ? '⚠️' : ''}
            </div>` : ''}
        </div>
    `;
}

function enableDragAndDrop() {
    const cards = document.querySelectorAll('.task-card');
    const containers = document.querySelectorAll('.kanban-tasks');
    
    cards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });
    
    containers.forEach(container => {
        container.addEventListener('dragover', handleDragOver);
        container.addEventListener('drop', handleDrop);
        container.addEventListener('dragleave', handleDragLeave);
    });
}

function handleDragStart(e) {
    this.classList.add('dragging');
    currentDraggedTask = {
        id: this.dataset.taskId,
        projectId: this.dataset.projectId
    };
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    this.classList.add('drag-over');
    return false;
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    
    this.classList.remove('drag-over');
    
    const newStatus = this.closest('.kanban-column').dataset.status;
    const taskId = currentDraggedTask.id;
    const projectId = currentDraggedTask.projectId;
    
    // Update task status via API
    $.ajax({
        url: `/api/v1/tasks/${taskId}`,
        method: 'PUT',
        data: { status: newStatus },
        success: () => {
            showToast('Task status updated!', 'success');
            loadTasks($('#projectFilter').val());
        },
        error: () => {
            showToast('Failed to update task', 'error');
        }
    });
    
    return false;
}

$('#projectFilter').change(function() {
    const projectId = $(this).val();
    renderKanban(projectId);
});

function openAddTaskModal() {
    $('#addTaskModal').addClass('show');
    const today = new Date().toISOString().split('T')[0];
    $('#task-deadline').val(today);
}

function closeAddTaskModal() {
    $('#addTaskModal').removeClass('show');
    $('#taskForm')[0].reset();
}

$('#taskForm').submit(function(e) {
    e.preventDefault();
    
    const data = {
        title: $('#task-title').val(),
        description: $('#task-description').val(),
        priority: $('#task-priority').val(),
        assigned_to: $('#task-assignee').val() || null,
        deadline: $('#task-deadline').val() || null
    };
    
    const projectId = $('#task-project').val();
    
    $.post(`/api/v1/projects/${projectId}/tasks`, data)
        .done(() => {
            showToast('Task created successfully!', 'success');
            closeAddTaskModal();
            loadTasks($('#projectFilter').val());
        })
        .fail(xhr => {
            showToast(xhr.responseJSON?.message || 'Failed to create task', 'error');
        });
});

// Close modal on outside click
$('.modal').click(function(e) {
    if (e.target === this) {
        $(this).removeClass('show');
    }
});
</script>
@endpush
