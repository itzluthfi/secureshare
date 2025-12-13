@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<h1 style="margin-bottom: 2rem;">User Management</h1>

<div class="card">
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: var(--light); text-align: left;">
                <th style="padding: 0.7rem;">Name</th>
                <th style="padding: 0.7rem;">Email</th>
                <th style="padding: 0.7rem;">Role</th>
                <th style="padding: 0.7rem;">Status</th>
                <th style="padding: 0.7rem;">Actions</th>
            </tr>
        </thead>
        <tbody id="usersTable">
            <tr><td colspan="5" style="padding: 1rem; text-align: center; color: #6B7280;">Loading users...</td></tr>
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
// Check auth
const token = localStorage.getItem('token');
if (!token) {
    window.location.href = '/login';
}

// Load users
$.get('/api/v1/users')
    .done(function(response) {
        const users = response.data || response;
        let html = '';
        
        users.forEach(function(user) {
            html += `
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 0.7rem;">${user.name}</td>
                    <td style="padding: 0.7rem;">${user.email}</td>
                    <td style="padding: 0.7rem;">
                        <select data-user-id="${user.id}" class="role-select" style="padding: 0.3rem; border: 1px solid var(--border); border-radius: 4px;">
                            <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                            <option value="manager" ${user.role === 'manager' ? 'selected' : ''}>Manager</option>
                            <option value="member" ${user.role === 'member' ? 'selected' : ''}>Member</option>
                        </select>
                    </td>
                    <td style="padding: 0.7rem;">
                        <span style="padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.8rem; 
                            background: ${user.is_active ? 'var(--secondary)' : 'var(--danger)'}; color: white;">
                            ${user.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td style="padding: 0.7rem;">
                        <button class="btn btn-sm btn-danger" onclick="toggleUserStatus(${user.id}, ${!user.is_active})">
                            ${user.is_active ? 'Deactivate' : 'Activate'}
                        </button>
                    </td>
                </tr>
            `;
        });
        
        $('#usersTable').html(html);
        
        // Bind role change event
        $('.role-select').change(function() {
            const userId = $(this).data('user-id');
            const role = $(this).val();
            
            $.post(`/api/v1/users/${userId}/assign-role`, { role })
                .done(() => showToast('Role updated successfully!', 'success'))
                .fail(() => showToast('Failed to update role', 'error'));
        });
    })
    .fail(function(xhr) {
        if (xhr.status === 401 || xhr.status === 403) {
            localStorage.removeItem('token');
            window.location.href = '/login';
        } else {
            $('#usersTable').html('<tr><td colspan="5" style="padding: 1rem; text-align: center; color: var(--danger);">Failed to load users</td></tr>');
        }
    });

function toggleUserStatus(userId, activate) {
    $.ajax({
        url: `/api/v1/users/${userId}`,
        method: 'PUT',
        data: { is_active: activate }
    })
    .done(() => {
        showToast('User status updated!', 'success');
        location.reload();
    })
    .fail(() => showToast('Failed to update status', 'error'));
}
</script>
@endpush
