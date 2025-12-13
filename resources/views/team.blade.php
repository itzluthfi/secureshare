@extends('layouts.app')

@section('title', 'Team')

@section('content')
<div class="page-header">
    <h1 class="page-title">Team Members</h1>
    <p class="page-subtitle">All users in the organization</p>
</div>

<div class="card">
    <div class="card-header">
        <div style="flex: 1;">
            <input type="text" id="search-users" class="form-input" placeholder="Search users..." style="max-width: 400px;">
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button class="filter-btn active" data-role="all">All</button>
            <button class="filter-btn" data-role="admin">Admins</button>
            <button class="filter-btn" data-role="manager">Managers</button>
            <button class="filter-btn" data-role="member">Members</button>
        </div>
    </div>
    
    <div id="team-list">
        <p style="text-align: center; padding: 3rem; color: var(--text-muted);">Loading team members...</p>
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

.member-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    border-bottom: 1px solid var(--border);
    transition: background 0.3s;
}

.member-item:hover {
    background: var(--bg-card-hover);
}

.member-item:last-child {
    border-bottom: none;
}

.member-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-blue), var(--purple));
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.member-info {
    flex: 1;
}

.member-name {
    font-weight: 600;
    font-size: 1.05rem;
    margin-bottom: 0.25rem;
}

.member-email {
    font-size: 0.9rem;
    color: var(--text-muted);
}

.role-badge {
    padding: 0.4rem 0.9rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
}

.role-admin { background: var(--purple); color: white; }
.role-manager { background: var(--warning); color: white; }
.role-member { background: var(--text-muted); color: white; }

.status-badge {
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-active { background: var(--success); color: white; }
.status-inactive { background: var(--danger); color: white; }

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
</style>
@endpush

@push('scripts')
<script>
let allUsers = [];
let currentRole = 'all';
let searchTerm = '';

$(document).ready(function() {
    loadTeam();
    
    // Role filter
    $('.filter-btn').click(function() {
        currentRole = $(this).data('role');
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        filterAndRender();
    });
    
    // Search
    $('#search-users').on('input', function() {
        searchTerm = $(this).val().toLowerCase();
        filterAndRender();
    });
});

function loadTeam() {
    $.get('/api/v1/users')
        .done(function(response) {
            allUsers = response.data || response;
            filterAndRender();
        })
        .fail(function() {
            $('#team-list').html(`
                <p style="text-align: center; padding: 3rem; color: var(--danger);">
                    Failed to load team members
                </p>
            `);
        });
}

function filterAndRender() {
    let filtered = allUsers;
    
    // Filter by role
    if (currentRole !== 'all') {
        filtered = filtered.filter(u => u.role === currentRole);
    }
    
    // Filter by search
    if (searchTerm) {
        filtered = filtered.filter(u => 
            u.name.toLowerCase().includes(searchTerm) ||
            u.email.toLowerCase().includes(searchTerm)
        );
    }
    
    renderTeam(filtered);
}

function renderTeam(users) {
    if (users.length === 0) {
        $('#team-list').html('<p style="text-align: center; padding: 3rem; color: var(--text-muted);">No users found</p>');
        return;
    }
    
    let html = '';
    users.forEach(user => {
        const initial = user.name.charAt(0).toUpperCase();
        const joined = new Date(user.created_at).toLocaleDateString();
        
        html += `
            <div class="member-item">
                <div class="member-avatar">${initial}</div>
                <div class="member-info">
                    <div class="member-name">${user.name}</div>
                    <div class="member-email">${user.email}</div>
                    <div style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--text-muted);">
                        <i class="fas fa-calendar"></i> Joined ${joined}
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <span class="role-badge role-${user.role}">${user.role}</span>
                    <span class="status-badge status-${user.is_active ? 'active' : 'inactive'}">
                        ${user.is_active ? 'Active' : 'Inactive'}
                    </span>
                </div>
            </div>
        `;
    });
    
    $('#team-list').html(html);
}
</script>
@endpush
