<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - SecureShare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --primary-blue: #4F7FFF;
            --primary-blue-hover: #3D6FEF;
            --bg-dark: #1a1a2e;
            --bg-card: #16213e;
            --bg-card-hover: #1f2f4a;
            --text-primary: #FFFFFF;
            --text-secondary: #B8B8B8;
            --text-muted: #6B7280;
            --border: #2d3748;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --purple: #9D4EDD;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 100;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .sidebar-close {
            display: none;
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.25rem;
            transition: color 0.3s;
        }
        
        .sidebar-close:hover {
            color: var(--text-primary);
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .sidebar-logo i {
            color: var(--primary-blue);
            font-size: 1.5rem;
        }
        
        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s;
            position: relative;
        }
        
        .menu-item:hover {
            background: var(--bg-card-hover);
            color: var(--text-primary);
        }
        
        .menu-item.active {
            background: rgba(79, 127, 255, 0.1);
            color: var(--primary-blue);
            border-left: 3px solid var(--primary-blue);
        }
        
        .menu-item i {
            width: 20px;
            text-align: center;
        }
        
        .menu-badge {
            margin-left: auto;
            background: var(--danger);
            color: white;
            padding: 0.15rem 0.5rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue), var(--purple));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: capitalize;
        }
        
        .logout-btn {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-size: 1.1rem;
        }
        
        .hamburger-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }
        
        /* Main Content */
        .main-container {
            margin-left: 260px;
            width: calc(100% - 260px);
            min-height: 100vh;
            transition: margin-left 0.3s, width 0.3s;
        }
        
        /* Top Navbar */
        .topnav {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            position: sticky;
            top: 0;
            z-index: 90;
        }
        
        .search-box {
            flex: 1;
            max-width: 500px;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 0.65rem 1rem 0.65rem 2.5rem;
            background: var(--bg-dark);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 0.9rem;
        }
        
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }
        
        .topnav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .notif-bell {
            position: relative;
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.3rem;
            cursor: pointer;
            padding: 0.5rem;
            transition: color 0.3s;
        }
        
        .notif-bell:hover {
            color: var(--text-primary);
        }
        
        .notif-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: var(--danger);
            color: white;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        /* Content Area */
        .content {
            padding: 2rem;
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: var(--text-secondary);
        }
        
        /* Cards */
        .card {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid var(--border);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        /* Buttons */
        .btn {
            padding: 0.65rem 1.2rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-hover));
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 127, 255, 0.3);
        }
        
        .btn-secondary {
            background: var(--bg-card-hover);
            color: var(--text-primary);
        }
        
        .btn-secondary:hover {
            background: var(--border);
        }
        
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }
        
        /* Toast */
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--bg-card);
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            border-left: 4px solid var(--primary-blue);
            z-index: 9999;
            display: none;
            align-items: center;
            gap: 0.75rem;
            min-width: 300px;
            animation: slideIn 0.3s;
        }
        
        .toast.show { 
            display: flex !important; 
        }
        
        .toast.success { border-left-color: var(--success); }
        .toast.error { border-left-color: var(--danger); }
        .toast.warning { border-left-color: var(--warning); }
        .toast.info { border-left-color: var(--primary-blue); }
        
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        /* Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                transform: translateX(-100%);
                transition: transform 0.3s;
                z-index: 1000;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-container {
                margin-left: 0 !important;
                width: 100% !important;
            }
            
            .hamburger-btn {
                display: block;
            }
            
            .search-box {
                display: none;
            }
            
            .sidebar-close {
                display: block;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-lock"></i>
                <span>SecureShare</span>
            </div>
            <button class="sidebar-close" onclick="toggleSidebar()" title="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <nav class="sidebar-menu">
            <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('projects.index') }}" class="menu-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                <i class="fas fa-folder"></i>
                <span>Projects</span>
            </a>
            <a href="/tasks" class="menu-item">
                <i class="fas fa-tasks"></i>
                <span>My Tasks</span>
            </a>
            <a href="/documents" class="menu-item">
                <i class="fas fa-file-alt"></i>
                <span>Documents</span>
            </a>
            <a href="/inbox" class="menu-item">
                <i class="fas fa-inbox"></i>
                <span>Inbox</span>
                <span class="menu-badge" id="inbox-count" style="display: none;">0</span>
            </a>
            <a href="/team" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Team</span>
            </a>
            <a href="{{ route('admin.users') }}" class="menu-item" id="admin-menu" style="display: none;">
                <i class="fas fa-user-shield"></i>
                <span>Admin</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar" id="user-avatar">U</div>
                <div class="user-info">
                    <div class="user-name" id="user-name">Loading...</div>
                    <div class="user-role" id="user-role">member</div>
                </div>
                <button class="logout-btn" onclick="logout()" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </aside>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Top Navbar -->
        <header class="topnav">
            <button class="hamburger-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search projects, tasks, documents...">
            </div>
            
            <div class="topnav-actions">
                <button class="notif-bell" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <span class="notif-badge" id="notif-badge" style="display: none;">0</span>
                </button>
            </div>
        </header>
        
        <!-- Content -->
        <main class="content">
            @yield('content')
        </main>
    </div>
    
    <!-- Toast -->
    <div id="toast" class="toast"></div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Check authentication
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = '/login';
        }
        
        // Setup AJAX with token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        });
        
        // Toast notification with colors
        function showToast(message, type = 'success') {
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-times-circle',
                info: 'fa-info-circle',
                warning: 'fa-exclamation-triangle'
            };
            
            const colors = {
                success: 'var(--success)',
                error: 'var(--danger)',
                info: 'var(--primary-blue)',
                warning: 'var(--warning)'
            };
            
            const icon = icons[type] || icons.success;
            const color = colors[type] || colors.success;
            
            const toast = $('#toast');
            toast.html(`
                <i class="fas ${icon}" style="color: ${color}; font-size: 1.5rem;"></i>
                <span style="flex: 1;">${message}</span>
                <button onclick="$('#toast').removeClass('show')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.2rem; padding: 0;">
                    <i class="fas fa-times"></i>
                </button>
            `);
            toast.removeClass('error info warning success').addClass(type).addClass('show');
            
            // Auto close after 5 seconds
            setTimeout(() => toast.removeClass('show'), 5000);
        }
        
        // Logout function
        function logout() {
            $.post('/api/v1/auth/logout')
                .always(function() {
                    localStorage.removeItem('token');
                    localStorage.removeItem('user');
                    window.location.href = '/login';
                });
        }
        
        function toggleNotifications() {
            window.location.href = '/inbox';
        }
        
        function toggleSidebar() {
            $('.sidebar').toggleClass('show');
            $('#sidebar-overlay').toggleClass('show');
        }
        
        // Load user info
        function loadUserInfo() {
            $.get('/api/v1/auth/me')
                .done(function(response) {
                    const user = response.user || response;
                    localStorage.setItem('user', JSON.stringify(user));
                    
                    const initial = user.name.charAt(0).toUpperCase();
                    $('#user-avatar').text(initial);
                    $('#user-name').text(user.name);
                    $('#user-role').text(user.role);
                    
                    // Show admin menu if user is admin
                    if (user.role === 'admin') {
                        $('#admin-menu').show();
                    }
                })
                .fail(function() {
                    console.log('Failed to load user info');
                    localStorage.removeItem('token');
                    window.location.href = '/login';
                });
        }
        
        // Load notifications count
        function loadNotificationCount() {
            $.get('/api/v1/notifications/unread-count')
                .done(function(data) {
                    const count = data.unread_count || 0;
                    if (count > 0) {
                        $('#notif-badge').text(count).show();
                        $('#inbox-count').text(count).show();
                    } else {
                        $('#notif-badge').hide();
                        $('#inbox-count').hide();
                    }
                })
                .fail(function() {
                    console.log('Failed to load notification count');
                });
        }
        
        // Init
        $(document).ready(function() {
            loadUserInfo();
            loadNotificationCount();
            setInterval(loadNotificationCount, 30000); // Every 30s
        });
    </script>
    @stack('scripts')
</body>
</html>
