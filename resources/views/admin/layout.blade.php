<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', ($brandName ?? 'Kesfet LAB') . ' - Yonetici Paneli')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff7a00;
            --secondary-color: #f5d100;
            --dark-color: #2f3138;
            --dark-soft: #464a55;
            --light-color: #fff7cc;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(160deg, var(--dark-color) 0%, var(--dark-soft) 100%);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-logo {
            max-width: 170px;
            max-height: 64px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .sidebar-header h4 {
            color: white;
            margin: 0;
            font-weight: 600;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 1rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white;
            background: linear-gradient(120deg, rgba(255, 122, 0, 0.95), rgba(245, 209, 0, 0.9));
            transform: translateX(5px);
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        
        .top-navbar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content-area {
            padding: 2rem;
        }
        
        /* Cards */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 4px solid var(--primary-color);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Tables */
        .table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .table-header {
            background: linear-gradient(120deg, var(--dark-color) 0%, var(--dark-soft) 100%);
            color: #fff;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        
        .table {
            margin: 0;
        }
        
        .table th {
            border: none;
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .table td {
            border: none;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(120deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            color: #2f3138;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 122, 0, 0.35);
        }
        
        /* Status badges */
        .badge-pending {
            background: #ffc107;
            color: #000;
        }
        
        .badge-approved {
            background: #28a745;
            color: white;
        }
        
        .badge-rejected {
            background: #dc3545;
            color: white;
        }

        .kpi-card {
            position: relative;
            background: linear-gradient(135deg, #ffffff 0%, #fff9ef 100%);
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 8px 20px rgba(47,49,56,0.08);
            border: 1px solid rgba(255,122,0,0.18);
            overflow: hidden;
        }

        .kpi-card::after {
            content: "";
            position: absolute;
            top: -30px;
            right: -30px;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(245,209,0,0.32) 0%, rgba(245,209,0,0) 65%);
        }

        .kpi-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            background: linear-gradient(125deg, var(--primary-color), var(--secondary-color));
            color: #2f3138;
        }

        .kpi-value {
            font-size: 2rem;
            line-height: 1;
            font-weight: 700;
            color: var(--dark-color);
        }

        .kpi-label {
            color: #5f6470;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            @if(!empty($brandLogoPath))
                <img src="{{ asset(ltrim($brandLogoPath, '/')) }}" alt="{{ $brandName }}" class="sidebar-logo">
            @else
                <h4>{{ $brandName ?? 'Kesfet LAB' }}</h4>
            @endif
            <small style="color: rgba(255,255,255,0.7);">Yonetici Paneli</small>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.students') }}" class="nav-link {{ request()->routeIs('admin.students') ? 'active' : '' }}">
                    <i class="fas fa-user-graduate"></i>
                    Ön Kayıtlar
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.workshops') }}" class="nav-link {{ request()->routeIs('admin.workshops') ? 'active' : '' }}">
                    <i class="fas fa-flask"></i>
                    Atölyeler
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.groups') }}" class="nav-link {{ request()->routeIs('admin.groups*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    Gruplar
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.instructors') }}" class="nav-link {{ request()->routeIs('admin.instructors*') ? 'active' : '' }}">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Eğitmenler
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.enrollments') }}" class="nav-link {{ request()->routeIs('admin.enrollments') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    Kayıtlar
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.parents') }}" class="nav-link {{ request()->routeIs('admin.parents*') ? 'active' : '' }}">
                    <i class="fas fa-user-friends"></i>
                    Veliler
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    Ayarlar
                </a>
            </div>
            
            <hr style="border-color: rgba(255,255,255,0.1); margin: 1rem;">
            
            <div class="nav-item">
                <a href="{{ route('form.index') }}" class="nav-link">
                    <i class="fas fa-external-link-alt"></i>
                    Online Form
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3">
                    <i class="fas fa-user-circle me-2"></i>
                    {{ Auth::user()->name ?? 'Admin' }}
                </span>
                <a href="{{ route('form.index') }}" class="btn btn-outline-primary btn-sm me-2">
                    <i class="fas fa-home me-1"></i>
                    Ana Sayfa
                </a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i>
                        Çıkış
                    </button>
                </form>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // App base URL for building absolute paths when app is in a subfolder
        window.appBaseUrl = "{{ url('') }}";
        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }
    </script>
</body>
</html>
