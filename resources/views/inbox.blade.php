@extends('layouts.app')

@section('title', 'Inbox')

@section('content')
<div class="page-header">
    <h1 class="page-title">Inbox</h1>
    <p class="page-subtitle">Your notifications and project invitations</p>
</div>

<div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
    <button class="btn btn-primary btn-sm" onclick="markSelectedAsRead()" id="markSelectedBtn" style="display: none;">
        <i class="fas fa-check"></i> Mark Selected as Read
    </button>
    <button class="btn btn-secondary btn-sm" onclick="markAllAsRead()">
        <i class="fas fa-check-double"></i> Mark All as Read
    </button>
    <button class="filter-btn active" data-filter="all">All</button>
    <button class="filter-btn" data-filter="unread">Unread</button>
    <button class="filter-btn" data-filter="invitations">Invitations</button>
    <button class="filter-btn" data-filter="tasks">Tasks</button>
    <button class="filter-btn" data-filter="comments">Comments</button>
</div>

<div class="card">
    <div id="notifications-list">
        <p style="text-align: center; padding: 3rem; color: var(--text-muted);">
            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
            Loading notifications...
        </p>
    </div>
</div>
@endsection

@push('styles')
<style>
.filter-btn {
    padding: 0.5rem 1rem;
    background: var(--bg-card-hover);
    border: none;
    border-radius: 8px;
    color: var(--text-secondary);
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.filter-btn:hover {
    background: var(--border);
    color: var(--text-primary);
}

.filter-btn.active {
    background: var(--primary-blue);
    color: white;
}

.notif-item {
    display: flex;
    gap: 1rem;
    padding: 1.25rem;
    border-bottom: 1px solid var(--border);
    transition: background 0.3s;
    cursor: pointer;
}

.notif-item:hover {
    background: var(--bg-card-hover);
}

.notif-item:last-child {
    border-bottom: none;
}

.notif-item.unread {
    background: rgba(79, 127, 255, 0.05);
    border-left: 3px solid var(--primary-blue);
}

.notif-icon {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.2rem;
}

.notif-icon.invitation { background: linear-gradient(135deg, var(--purple), var(--primary-blue)); }
.notif-icon.task { background: linear-gradient(135deg, var(--warning), #F59E0B); }
.notif-icon.comment { background: linear-gradient(135deg, var(--success), #10B981); }
.notif-icon.document { background: linear-gradient(135deg, #4facfe, #00f2fe); }
.notif-icon.default { background: var(--text-muted); }

.notif-content {
    flex: 1;
}

.notif-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.notif-message {
    color: var(--text-secondary);
    font-size: 0.95rem;
    margin-bottom: 0.5rem;
}

.notif-meta {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.notif-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.empty-inbox {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-muted);
}

.empty-inbox i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.empty-inbox h3 {
    margin-bottom: 0.5rem;
    color: var(--text-secondary);
}
</style>
@endpush

@push('scripts')
<script>
let allNotifications = [];
let currentFilter = 'all';

$(document).ready(function() {
    loadNotifications();
    
    // Filter buttons
    $('.filter-btn').click(function() {
        currentFilter = $(this).data('filter');
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        filterNotifications();
    });
});

function loadNotifications() {
    $.get('/api/v1/notifications')
        .done(function(response) {
            allNotifications = response.data || response;
            filterNotifications();
        })
        .fail(function() {
            $('#notifications-list').html(`
                <div class="empty-inbox">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Failed to load notifications</h3>
                    <p>Please try again later</p>
                </div>
            `);
        });
}

function filterNotifications() {
    let filtered = allNotifications;
    
    if (currentFilter === 'unread') {
        filtered = allNotifications.filter(n => !n.read_at);
    } else if (currentFilter === 'invitations') {
        filtered = allNotifications.filter(n => n.type.includes('invitation'));
    } else if (currentFilter === 'tasks') {
        filtered = allNotifications.filter(n => n.type.includes('task'));
    } else if (currentFilter === 'comments') {
        filtered = allNotifications.filter(n => n.type.includes('comment'));
    }
    
    renderNotifications(filtered);
}

function renderNotifications(notifications) {
    if (notifications.length === 0) {
        $('#notifications-list').html(`
            <div class="empty-inbox">
                <i class="fas fa-inbox"></i>
                <h3>No notifications</h3>
                <p>You're all caught up!</p>
            </div>
        `);
        return;
    }
    
    let html = '';
    notifications.forEach(notif => {
        const isUnread = !notif.read_at;
        const iconClass = getNotificationIcon(notif.type);
        const iconType = getNotificationType(notif.type);
        const time = formatTime(notif.created_at);
        const data = notif.data ||{};
        const isPendingInvitation = notif.type === 'project_invitation' && data.action_required;
        
        html += `
            <div class="notif-item ${isUnread ? 'unread' : ''}" ${!isPendingInvitation ? `onclick="handleNotificationClick(${notif.id}, '${notif.type}', ${JSON.stringify(data).replace(/"/g, '&quot;')})"` : ''}>
                <input type="checkbox" class="notif-checkbox" value="${notif.id}" onclick="event.stopPropagation();" onchange="toggleCheckboxVisibility()" style="margin-right: 1rem;">
                <div class="notif-icon ${iconType}">
                    <i class="${iconClass}"></i>
                </div>
                <div class="notif-content">
                    <div class="notif-title">${notif.title || 'Notification'}</div>
                    <div class="notif-message">${notif.message || notif.type}</div>
                    ${isPendingInvitation ? `
                        <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem;">
                            <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); acceptInvitation(${notif.id}, ${data.project_id})">
                                <i class="fas fa-check"></i> Accept
                            </button>
                            <button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); declineInvitation(${notif.id}, ${data.project_id})">
                                <i class="fas fa-times"></i> Decline
                            </button>
                        </div>
                    ` : ''}
                    <div class="notif-meta">
                        <i class="fas fa-clock"></i> ${time}
                    </div>
                </div>
                <div class="notif-actions">
                    ${isUnread ? '<span style="width: 8px; height: 8px; background: var(--primary-blue); border-radius: 50%;"></span>' : ''}
                </div>
            </div>
        `;
    });
    
    $('#notifications-list').html(html);
}

function getNotificationIcon(type) {
    if (type.includes('task')) return 'fas fa-tasks';
    if (type.includes('comment')) return 'fas fa-comment';
    if (type.includes('document')) return 'fas fa-file';
    if (type.includes('invitation')) return 'fas fa-envelope';
    if (type.includes('member')) return 'fas fa-user-plus';
    return 'fas fa-bell';
}

function getNotificationType(type) {
    if (type.includes('task')) return 'task';
    if (type.includes('comment')) return 'comment';
    if (type.includes('document')) return 'document';
    if (type.includes('invitation')) return 'invitation';
    return 'default';
}

function formatTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    if (days < 7) return `${days} day${days > 1 ? 's' : ''} ago`;
    
    return date.toLocaleDateString();
}

function handleNotificationClick(notifId, type, data) {
    // Mark as read
    $.post(`/api/v1/notifications/${notifId}/mark-read`)
        .done(function() {
            // Update notification count in sidebar
            loadNotificationCount();
        });
    
    // Navigate based on type
    if (type.includes('task') && data.task_id) {
        window.location.href = `/projects/${data.project_id}`;
    } else if (type.includes('comment') && data.document_id) {
        window.location.href = `/documents/${data.document_id}`;
    } else if (type.includes('document') && data.document_id) {
        window.location.href = `/documents/${data.document_id}`;
    } else if (type.includes('project') && data.project_id) {
        window.location.href = `/projects/${data.project_id}`;
    }
    
    // Reload notifications
    setTimeout(() => loadNotifications(), 500);
}

function markAllAsRead() {
    $.post('/api/v1/notifications/mark-all-read')
        .done(function() {
            showToast('All notifications marked as read', 'success');
            loadNotifications();
            loadNotificationCount();
        })
        .fail(function() {
            showToast('Failed to mark notifications as read', 'error');
        });
}

function acceptInvitation(notifId, projectId) {
    $.post(`/api/v1/projects/${projectId}/invitations/accept`)
        .done(function(response) {
            showToast('Invitation accepted! Welcome to the project!', 'success');
            // Mark notification as read
            $.post(`/api/v1/notifications/${notifId}/mark-read`);
            // Reload notifications
            loadNotifications();
            loadNotificationCount();
            // Optional: redirect to project
            setTimeout(() => window.location.href = `/projects/${projectId}`, 1500);
        })
        .fail(function(xhr) {
            showToast(xhr.responseJSON?.message || 'Failed to accept invitation', 'error');
        });
}

function declineInvitation(notifId, projectId) {
    if (!confirm('Are you sure you want to decline this invitation?')) {
        return;
    }
    
    $.post(`/api/v1/projects/${projectId}/invitations/decline`)
        .done(function(response) {
            showToast('Invitation dec lined', 'info');
            // Mark notification as read
            $.post(`/api/v1/notifications/${notifId}/mark-read`);
            // Reload notifications
            loadNotifications();
            loadNotificationCount();
        })
        .fail(function(xhr) {
            showToast(xhr.responseJSON?.message || 'Failed to decline invitation', 'error');
        });
}

// NEW FUNCTIONS - Toggle checkbox visibility
function toggleCheckboxVisibility() {
    const checkedCount = $('.notif-checkbox:checked').length;
    $('#markSelectedBtn').toggle(checkedCount > 0);
}

// NEW FUNCTIONS - Mark selected notifications as read  
function markSelectedAsRead() {
    const selected = [];
    $('.notif-checkbox:checked').each(function() {
        selected.push($(this).val());
    });
    
    if (selected.length === 0) {
        showToast('No notifications selected', 'warning');
        return;
    }
    
    let completed = 0;
    selected.forEach(id => {
        $.post(`/api/v1/notifications/${id}/mark-read`)
            .done(() => {
                completed++;
                if (completed === selected.length) {
                    showToast(`${selected.length} notification(s) marked as read`, 'success');
                    loadNotifications();
                    loadNotificationCount();
                    $('#markSelectedBtn').hide();
                }
            })
            .fail(() => {
                showToast('Failed to mark notification', 'error');
            });
    });
}
</script>
@endpush
