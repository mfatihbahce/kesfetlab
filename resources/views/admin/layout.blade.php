<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', ($brandName ?? 'Kesfet LAB') . ' - Yonetici Paneli')</title>
    <link rel="icon" href="{{ asset('favicon.jpg') }}" type="image/jpeg" sizes="any">
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
            background: #ffffff;
            border-radius: 12px;
            padding: 12px 12px 10px;
            border: 1px solid #ebeef3;
            box-shadow: 0 6px 16px rgba(47,49,56,0.06);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(47,49,56,0.10);
        }

        .kpi-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .kpi-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .86rem;
            background: rgba(255,122,0,.12);
            color: #c56d00;
        }

        .kpi-chip {
            font-size: .62rem;
            font-weight: 700;
            padding: 3px 7px;
            border-radius: 999px;
            color: #6b7280;
            background: #f3f4f6;
        }

        .kpi-body {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 2px;
        }

        .kpi-value {
            font-size: 1.8rem;
            line-height: .95;
            font-weight: 700;
            color: #111827;
        }

        .kpi-label {
            color: #374151;
            font-weight: 600;
            font-size: .85rem;
        }

        .kpi-foot {
            margin-top: 2px;
            color: #9ca3af;
            font-size: .72rem;
            display: flex;
            align-items: center;
            gap: 5px;
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
                    Sınıflar
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.groups') }}" class="nav-link {{ request()->routeIs('admin.groups*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    Gruplar
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.calendar') }}" class="nav-link {{ request()->routeIs('admin.calendar*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i>
                    Takvim
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
                    Öğrenciler
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

        // Prevent accidental double submit (419 / duplicate requests)
        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (form.dataset.submitting === '1') {
                event.preventDefault();
                return;
            }
            form.dataset.submitting = '1';
            const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitButtons.forEach((btn) => {
                btn.disabled = true;
                btn.classList.add('disabled');
            });
        }, true);
    </script>
</body>
</html>
