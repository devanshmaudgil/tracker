<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Staffing Tracker')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        .app-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            background-color: #0a2d29;
            width: 250px;
            min-height: 100vh;
            transition: width 0.3s ease;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
        }
        .sidebar.collapsed {
            width: 60px;
        }
        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar-header h2 {
            color: white;
            font-size: 20px;
            white-space: nowrap;
            overflow: hidden;
        }
        .sidebar.collapsed .sidebar-header h2 {
            display: none;
        }
        .toggle-btn {
            background: none;
            border: none;
            color: #f1cd86;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .toggle-btn:hover {
            background-color: rgba(241, 205, 134, 0.1);
        }
        .sidebar-menu {
            list-style: none;
            padding: 10px 0;
        }
        .sidebar-menu li {
            margin: 5px 0;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .sidebar-menu a:hover {
            background-color: rgba(241, 205, 134, 0.1);
        }
        .sidebar-menu a.active {
            background-color: #f1cd86;
            color: #0a2d29;
        }
        .sidebar-menu .menu-icon {
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }
        .sidebar.collapsed .sidebar-menu .menu-icon {
            margin-right: 0;
        }
        .sidebar.collapsed .sidebar-menu span {
            display: none;
        }
        .sidebar.collapsed .sidebar-menu a {
            justify-content: center;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
            flex: 1;
            transition: margin-left 0.3s ease;
        }
        .sidebar.collapsed ~ .main-content {
            margin-left: 60px;
        }
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .content-header h1 {
            color: #0a2d29;
            font-size: 28px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-primary {
            background-color: #f1cd86;
            color: #0a2d29;
        }
        .btn-primary:hover {
            background-color: #e6c075;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .table-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background-color: #0a2d29;
            color: white;
        }
        th, td {
            padding: 8px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        tbody tr:hover {
            background-color: #f9f9f9;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #0a2d29;
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #f1cd86;
            box-shadow: 0 0 0 3px rgba(241, 205, 134, 0.1);
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>Tracker</h2>
                <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="{{ route('tracker.index') }}" class="{{ request()->routeIs('tracker.*') ? 'active' : '' }}">
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('months.index') }}" class="{{ request()->routeIs('months.*') ? 'active' : '' }}">
                        <span>Months</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('clients.info') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <span>Clients</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('regions.index') }}" class="{{ request()->routeIs('regions.*') ? 'active' : '' }}">
                        <span>Region</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('candidates.index') }}" class="{{ request()->routeIs('candidates.*') ? 'active' : '' }}">
                        <span>Candidates</span>
                    </a>
                </li>

                <li>
                    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                        @csrf
                        <a href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                            <span>Logout</span>
                        </a>
                    </form>
                </li>
            </ul>
        </div>

        <div class="main-content">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        // Restore sidebar state on page load
        window.addEventListener('DOMContentLoaded', function() {
            const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (collapsed) {
                document.getElementById('sidebar').classList.add('collapsed');
            }
        });
    </script>
</body>
</html>

