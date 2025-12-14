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
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.3rem;
            font-weight: 700;
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
        
        /* Main Content */
        .main-container {
            margin-left: 260px;
            min-height: 100vh;
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
        
        .notif-bell {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.3rem;
            cursor: pointer;
            padding: 0.5rem;
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
        
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-lock"></i>
                <span>SecureShare</span>
            </div>
        </div>
        
        <nav class="sidebar-menu">
            <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('projects.index') }}" class="menu-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                <i class="fas fa-folder"></i>
                <span>Projects</span>
            </a>
            <a href="/calendar" class="menu-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Calendar</span>
            </a>
            <a href="/tasks" class="menu-item">
                <i class="fas fa-tasks"></i>
                <span>My Tasks</span>
            </a>
            <a href="/documents" class="menu-item">
                <i class="fas fa-file-alt"></i>
                <span>Documents</span>
            </a>
            @can('viewAny', App\Models\User::class)
            <a href="{{ route('admin.users') }}" class="menu-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i>
                <span>Admin</span>
            </a>
            @endcan
        </nav>
        
        <div class="sidebar-footer">
            @auth
            <div class="user-profile">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">
                        @if(auth()->user()->isAdmin())
                            Admin
                        @elseif(auth()->user()->isManager())
                            Manager
                        @else
                            Member
                        @endif
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="logout-btn" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </aside>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Top Navbar -->
        <header class="topnav">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search projects, tasks, documents...">
            </div>
            
            <button class="notif-bell">
                <i class="fas fa-bell"></i>
            </button>
        </header>
        
        <!-- Content -->
        <main class="content">
            @yield('content')
        </main>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Setup CSRF Token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });
        
        // Init
        $(document).ready(function() {
            console.log('App loaded successfully!');
        });
    </script>
    @stack('scripts')
</body>
</html>