@extends('layouts.app')

@section('title', 'Project Details')

@section('content')
    <div class="page-header" style="margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <a href="{{ route('projects.index') }}" style="color: var(--text-secondary); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Projects
            </a>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <h1 class="page-title" id="project-name">Loading...</h1>
                <p class="page-subtitle" id="project-description"></p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                @can('update', $project)
                <button class="btn btn-secondary btn-sm" id="editProjectBtn">
                    <i class="fas fa-edit"></i> Edit
                </button>
                @endcan
                
                @can('manageMembers', $project)
                <button class="btn btn-primary" id="inviteMemberBtn">
                    <i class="fas fa-user-plus"></i> Invite Member
                </button>
                @endcan
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-btn active" data-tab="documents"><i class="fas fa-file-alt"></i> Documents</button>
        <button class="tab-btn" data-tab="tasks"><i class="fas fa-tasks"></i> Tasks</button>
        <button class="tab-btn" data-tab="members"><i class="fas fa-users"></i> Members</button>
        <button class="tab-btn" data-tab="activity"><i class="fas fa-history"></i> Activity</button>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content active" id="documents-tab">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Project Documents</h3>
                @can('create', [App\Models\Document::class, $project])
                <button class="btn btn-primary" id="uploadDocumentBtn">
                    <i class="fas fa-upload"></i> Upload Document
                </button>
                @endcan
            </div>
            <div id="documents-list">
                <p style="text-align: center; padding: 2rem; color: var(--text-muted);">Loading documents...</p>
            </div>
        </div>
    </div>

    <div class="tab-content" id="tasks-tab">
        <div style="margin-bottom: 1rem; display: flex; justify-content: space-between;">
            <h3 style="font-size: 1.3rem; font-weight: 600;">Task Board</h3>
            <button class="btn btn-primary" onclick="openTaskModal()">
                <i class="fas fa-plus"></i> Create Task
            </button>
        </div>

        <!-- Kanban Board -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
            <!-- To Do Column -->
            <div class="kanban-column">
                <div class="kanban-header" style="background: var(--text-muted);">
                    <i class="fas fa-circle"></i>
                    <span>To Do</span>
                    <span class="task-count" id="todo-count">0</span>
                </div>
                <div class="kanban-body" id="todo-tasks">
                    <p style="text-align: center; padding: 1rem; color: var(--text-muted);">No tasks</p>
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
                    <p style="text-align: center; padding: 1rem; color: var(--text-muted);">No tasks</p>
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
                    <p style="text-align: center; padding: 1rem; color: var(--text-muted);">No tasks</p>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-content" id="members-tab">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Project Members</h3>
            </div>
            <div id="members-list">
                <p style="text-align: center; padding: 2rem; color: var(--text-muted);">Loading members...</p>
            </div>
        </div>
    </div>

    <div class="tab-content" id="activity-tab">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Project Activity</h3>
            </div>
            <div id="activity-list">
                <p style="text-align: center; padding: 2rem; color: var(--text-muted);">Loading activity...</p>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Upload Document</h3>
                <button onclick="closeUploadModal()"
                    style="background: none; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <form id="uploadForm">
                    <div class="form-group">
                        <label>Document Name (optional)</label>
                        <input type="text" id="doc-name" class="form-input" placeholder="Leave empty to use filename">
                    </div>
                    <div class="form-group">
                        <label>Select File</label>
                        <input type="file" id="doc-file" class="form-input" required>
                        <small style="color: var(--text-muted);">Maximum file size: 50MB</small>
                    </div>
                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <button type="button" class="btn btn-secondary" onclick="closeUploadModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Task Modal -->
    <div id="taskModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="task-modal-title">Create Task</h3>
                <button onclick="closeTaskModal()"
                    style="background: none; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <form id="taskForm">
                    <input type="hidden" id="task-id">
                    <div class="form-group">
                        <label>Task Title *</label>
                        <input type="text" id="task-title" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="task-description" class="form-input" rows="3"></textarea>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label>Assign To</label>
                            <select id="task-assignee" class="form-input">
                                <option value="">Unassigned</option>
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
                            <input type="datetime-local" id="task-start-date" class="form-input">
                        </div>
                        <div class="form-group">
                            <label>Deadline</label>
                            <input type="datetime-local" id="task-deadline" class="form-input">
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <button type="button" class="btn btn-secondary" onclick="closeTaskModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Invite Member Modal -->
    <div id="inviteMemberModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Invite Member</h3>
                <button onclick="closeInviteMemberModal()"
                    style="background: none; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <form id="inviteMemberForm">
                    <div class="form-group">
                        <label>Select User *</label>
                        <select id="invite-user-id" class="form-input" required>
                            <option value="">Choose a user...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Role *</label>
                        <select id="invite-role" class="form-input" required>
                            <option value="member">Member</option>
                            <option value="manager">Manager</option>
                            <option value="owner">Owner</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <button type="button" class="btn btn-secondary" onclick="closeInviteMemberModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Invite
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--border);
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-weight: 600;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab-btn:hover {
            color: var(--text-primary);
        }

        .tab-btn.active {
            color: var(--primary-blue);
            border-bottom-color: var(--primary-blue);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Kanban Board */
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

        .task-card-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .task-card-meta {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: flex;
            gap: 1rem;
        }

        .priority-badge {
            display: inline-block;
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .priority-low {
            background: #6B7280;
        }

        .priority-medium {
            background: var(--warning);
        }

        .priority-high {
            background: var(--danger);
        }

        /* Document List */
        .document-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .document-item:last-child {
            border-bottom: none;
        }

        .document-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--primary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .document-info {
            flex: 1;
        }

        .document-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .document-meta {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .document-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Member List */
        .member-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .member-item:last-child {
            border-bottom: none;
        }

        .member-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue), var(--purple));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .member-info {
            flex: 1;
        }

        .member-name {
            font-weight: 600;
        }

        .member-email {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .role-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .role-owner {
            background: var(--purple);
            color: white;
        }

        .role-manager {
            background: var(--warning);
            color: white;
        }

        .role-member {
            background: var(--text-muted);
            color: white;
        }

        /* Modal */
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
@endpush

@push('scripts')
    <script>
        let projectId = {{ $project->id ?? 0 }};
        let projectData = null;
        let members = [];

        $(document).ready(function () {
    const projectId = window.location.pathname.split('/').pop();
    
    // Bind action buttons
    $('#editProjectBtn').click(function() { editProject(); });
    $('#inviteMemberBtn').click(function() { openInviteMemberModal(); });
    $('#uploadDocumentBtn').click(function() { openUploadModal(); });
    
    loadProjectDetails();

            // Tab switching
            $('.tab-btn').click(function () {
                const tab = $(this).data('tab');
                $('.tab-btn').removeClass('active');
                $(this).addClass('active');
                $('.tab-content').removeClass('active');
                $(`#${tab}-tab`).addClass('active');

                // Load tab data
                if (tab === 'documents') loadDocuments();
                else if (tab === 'tasks') loadTasks();
                else if (tab === 'members') loadMembers();
                else if (tab === 'activity') loadActivity();
            });

            // Upload form
            $('#uploadForm').submit(function (e) {
                e.preventDefault();
                uploadDocument();
            });

            // Task form
            $('#taskForm').submit(function (e) {
                e.preventDefault();
                saveTask();
            });

            // Invite member form
            $('#inviteMemberForm').submit(function (e) {
                e.preventDefault();
                inviteMember();
            });
        });

        function loadProjectDetails() {
            console.log('Loading project:', projectId);
            $.get(`/api/v1/projects/${projectId}`)
                .done(function (project) {
                    console.log('Project loaded:', project);
                    projectData = project;
                    
                    // Update page title and description
                    $('#project-name').text(project.name);
                    $('#project-description').text(project.description || 'No description');
                    
                    // Update page title in browser
                    document.title = project.name + ' - SecureShare';
                    
                    // Auto-load first tab
                    loadDocuments();
                    loadMembers(); // Load for task assignment and invite
                })
                .fail(function (xhr) {
                    console.error('Failed to load project:', xhr.status, xhr.responseJSON);
                    if (xhr.status === 403 || xhr.status === 401) {
                        showToast('You don\'t have access to this project', 'error');
                        setTimeout(() => window.location.href = '/projects', 2000);
                    } else {
                        showToast('Failed to load project details', 'error');
                        $('#project-name').text('Error loading project');
                    }
                });
        }

        function loadDocuments() {
            $.get(`/api/v1/projects/${projectId}/documents`)
                .done(function (response) {
                    const docs = response.data || response;

                    if (!docs || docs.length === 0) {
                        $('#documents-list').html('<p style="text-align: center; padding: 2rem; color: var(--text-muted);">No documents yet</p>');
                        return;
                    }

                    let html = '';
                    docs.forEach(doc => {
                        const size = formatFileSize(doc.file_size);
                        const date = new Date(doc.created_at).toLocaleDateString();

                        html += `
                        <div class="document-item">
                            <div class="document-icon">
                                <i class="fas fa-file"></i>
                            </div>
                            <div class="document-info">
                                <div class="document-name">${doc.name}</div>
                                <div class="document-meta">
                                    <i class="fas fa-lock"></i> Encrypted • ${size} • Uploaded ${date}
                                </div>
                            </div>
                            <div class="document-actions">
                                <button class="btn btn-secondary btn-sm" onclick="downloadDocument(${doc.id})">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="viewVersions(${doc.id})">
                                    <i class="fas fa-history"></i> v${doc.current_version}
                                </button>
                            </div>
                        </div>
                    `;
                    });

                    $('#documents-list').html(html);
                });
        }

        function loadTasks() {
            $.get(`/api/v1/projects/${projectId}/tasks`)
                .done(function (response) {
                    const tasks = response.data || response;

                    // Clear all columns
                    $('#todo-tasks, #progress-tasks, #done-tasks').html('');

                    const todoTasks = tasks.filter(t => t.status === 'todo');
                    const progressTasks = tasks.filter(t => t.status === 'in_progress');
                    const doneTasks = tasks.filter(t => t.status === 'done');

                    // Update counts
                    $('#todo-count').text(todoTasks.length);
                    $('#progress-count').text(progressTasks.length);
                    $('#done-count').text(doneTasks.length);

                    // Render tasks
                    renderTaskColumn('todo', todoTasks);
                    renderTaskColumn('progress', progressTasks);
                    renderTaskColumn('done', doneTasks);
                });
        }

        function renderTaskColumn(status, tasks) {
            const containerId = status === 'progress' ? 'progress-tasks' : `${status}-tasks`;

            if (tasks.length === 0) {
                $(`#${containerId}`).html('<p style="text-align: center; padding: 1rem; color: var(--text-muted);">No tasks</p>');
                return;
            }

            let html = '';
            tasks.forEach(task => {
                const deadline = task.deadline ? new Date(task.deadline).toLocaleDateString() : 'No deadline';
                const assignee = task.assignee ? task.assignee.name : 'Unassigned';

                html += `
                <div class="task-card" onclick="editTask(${task.id})">
                    <div class="task-card-title">${task.title}</div>
                    <div class="task-card-meta">
                        <span><i class="fas fa-user"></i> ${assignee}</span>
                        <span><i class="fas fa-calendar"></i> ${deadline}</span>
                    </div>
                    <div style="margin-top: 0.5rem;">
                        <span class="priority-badge priority-${task.priority}">${task.priority}</span>
                    </div>
                </div>
            `;
            });

            $(`#${containerId}`).html(html);
        }

        function loadMembers() {
            $.get(`/api/v1/projects/${projectId}`)
                .done(function (project) {
                    members = project.members || [];

                    // Populate task assignee dropdown
                    let options = '<option value="">Unassigned</option>';
                    members.forEach(member => {
                        options += `<option value="${member.id}">${member.name}</option>`;
                    });
                    $('#task-assignee').html(options);

                    // Render members list
                    if (members.length === 0) {
                        $('#members-list').html('<p style="text-align: center; padding: 2rem; color: var(--text-muted);">No members yet</p>');
                        return;
                    }

                    let html = '';
                    members.forEach(member => {
                        const role = member.pivot?.role || 'member';
                        const initial = member.name.charAt(0).toUpperCase();

                        html += `
                        <div class="member-item">
                            <div class="member-avatar">${initial}</div>
                            <div class="member-info">
                                <div class="member-name">${member.name}</div>
                                <div class="member-email">${member.email}</div>
                            </div>
                            <span class="role-badge role-${role}">${role}</span>
                        </div>
                    `;
                    });

                    $('#members-list').html(html);
                });
        }

        function loadActivity() {
            // Placeholder for audit logs
            $('#activity-list').html('<p style="text-align: center; padding: 2rem; color: var(--text-muted);">Activity log coming soon</p>');
        }

        function loadAllUsers() {
            $.get('/api/v1/users')
                .done(function (response) {
                    const users = response.data || response;
                    let options = '<option value="">Choose a user...</option>';
                    users.forEach(user => {
                        options += `<option value="${user.id}">${user.name} (${user.email})</option>`;
                    });
                    $('#invite-user-id').html(options);
                });
        }

        // Modal functions
        function openUploadModal() {
            $('#uploadModal').addClass('show');
        }

        function closeUploadModal() {
            $('#uploadModal').removeClass('show');
            $('#uploadForm')[0].reset();
        }

        function openTaskModal() {
            $('#task-modal-title').text('Create Task');
            $('#task-id').val('');
            $('#taskForm')[0].reset();
            $('#taskModal').addClass('show');
        }

        function closeTaskModal() {
            $('#taskModal').removeClass('show');
        }

        function openInviteMemberModal() {
            loadAvailableUsers(); // Load users when modal opens
            $('#inviteMemberModal').addClass('show');
        }
        
        function loadAvailableUsers() {
            // Load all users for the invite dropdown
            $.get('/api/v1/users')
                .done(function(response) {
                    const users = response.data || response;
                    let options = '<option value="">Select User</option>';
                    users.forEach(user => {
                        // Don't include users already in project
                        const alreadyMember = members.some(m => m.id === user.id);
                        if (!alreadyMember) {
                            options += `<option value="${user.id}">${user.name} (${user.email})</option>`;
                        }
                    });
                    $('#invite-user-id').html(options);
                })
                .fail(function() {
                    showToast('Failed to load users', 'error');
                });
        }

        function closeInviteMemberModal() {
            $('#inviteMemberModal').removeClass('show');
            $('#inviteMemberForm')[0].reset();
        }

        function uploadDocument() {
            const formData = new FormData();
            const file = $('#doc-file')[0].files[0];
            const name = $('#doc-name').val();

            if (!file) {
                showToast('Please select a file', 'error');
                return;
            }

            formData.append('file', file);
            if (name) formData.append('name', name);

            $.ajax({
                url: `/api/v1/projects/${projectId}/documents`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    showToast('Document uploaded successfully!', 'success');
                    closeUploadModal();
                    loadDocuments();
                },
                error: function (xhr) {
                    showToast(xhr.responseJSON?.message || 'Upload failed', 'error');
                }
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
                    if (!response.ok) {
                        throw new Error('Download failed');
                    }
                    return response.blob();
                })
                .then(blob => {
                    // Create download link
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'document'; // Filename will be from Content-Disposition header
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    showToast('Document downloaded successfully!', 'success');
                })
                .catch(error => {
                    console.error('Download error:', error);
                    showToast('Failed to download document', 'error');
                });
        }

        function saveTask() {
            const taskId = $('#task-id').val();
            const url = taskId ? `/api/v1/tasks/${taskId}` : `/api/v1/projects/${projectId}/tasks`;
            const method = taskId ? 'PUT' : 'POST';

            const data = {
                title: $('#task-title').val(),
                description: $('#task-description').val(),
                assigned_to: $('#task-assignee').val() || null,
                priority: $('#task-priority').val(),
                start_date: $('#task-start-date').val() || null,
                deadline: $('#task-deadline').val() || null
            };

            $.ajax({
                url,
                method,
                data,
                success: function (response) {
                    showToast('Task saved successfully!', 'success');
                    closeTaskModal();
                    loadTasks();
                },
                error: function (xhr) {
                    showToast(xhr.responseJSON?.message || 'Failed to save task', 'error');
                }
            });
        }

        function editTask(taskId) {
            $.get(`/api/v1/tasks/${taskId}`)
                .done(function (task) {
                    $('#task-modal-title').text('Edit Task');
                    $('#task-id').val(task.id);
                    $('#task-title').val(task.title);
                    $('#task-description').val(task.description);
                    $('#task-assignee').val(task.assigned_to);
                    $('#task-priority').val(task.priority);
                    $('#task-deadline').val(task.deadline);
                    $('#taskModal').addClass('show');
                });
        }

        function inviteMember() {
            const data = {
                user_id: $('#invite-user-id').val(),
                role: $('#invite-role').val()
            };

            $.post(`/api/v1/projects/${projectId}/members`, data)
                .done(function (response) {
                    showToast('Member invited successfully!', 'success');
                    closeInviteMemberModal();
                    loadMembers();
                })
                .fail(function (xhr) {
                    showToast(xhr.responseJSON?.message || 'Failed to invite member', 'error');
                });
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        }

        function viewVersions(docId) {
            // Placeholder
            showToast('Version history coming soon', 'info');
        }

        function editProject() {
            // Placeholder
            showToast('Edit project coming soon', 'info');
        }
    </script>
@endpush