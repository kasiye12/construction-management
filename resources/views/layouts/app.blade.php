<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Construction Management System')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar .brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .sidebar .brand h4 {
            color: white;
            font-weight: 700;
            margin: 0;
            font-size: 1.2rem;
        }
        
        .sidebar .brand p {
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
            margin: 5px 0 0 0;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 4px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.2);
            font-weight: 600;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            min-height: 100vh;
        }
        
        .card-stats {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        
        .card-stats:hover {
            transform: translateY(-5px);
        }
        
        .card-stats .card-body {
            padding: 25px;
        }
        
        .card-stats .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }
        
        .bg-primary-light { background-color: rgba(102, 126, 234, 0.1); color: #667eea; }
        .bg-success-light { background-color: rgba(40, 167, 69, 0.1); color: #28a745; }
        .bg-warning-light { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .bg-danger-light { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .bg-info-light { background-color: rgba(23, 162, 184, 0.1); color: #17a2b8; }
        
        .table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            padding: 25px;
        }
        
        .btn-custom {
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h2 {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
        }
        
        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .profit-badge {
            background-color: #d4edda;
            color: #155724;
        }
        
        .loss-badge {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .resource-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .resource-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="brand">
            <h4>🏗️ CMS</h4>
            <p>Construction Management</p>
        </div>
        
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                   href="{{ route('dashboard') }}">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}" 
                   href="{{ route('projects.index') }}">
                    <i class="fas fa-building"></i> Projects
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('subcontractors.*') ? 'active' : '' }}" 
                   href="{{ route('subcontractors.index') }}">
                    <i class="fas fa-users"></i> Subcontractors
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('cost-categories.*') ? 'active' : '' }}" 
                   href="{{ route('cost-categories.index') }}">
                    <i class="fas fa-sitemap"></i> Cost Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('boq-items.*') ? 'active' : '' }}" 
                   href="{{ route('boq-items.index') }}">
                    <i class="fas fa-list-ol"></i> BOQ Items
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('ipcs.*') ? 'active' : '' }}" 
                   href="{{ route('ipcs.index') }}">
                    <i class="fas fa-file-invoice"></i> IPCs
                </a>
            </li>
        </ul>
        
        <div class="mt-auto p-3" style="position: absolute; bottom: 0; width: 100%;">
            <div class="text-center text-white-50" style="font-size: 0.8rem;">
                <p>© 2024 CMS v1.0</p>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @yield('content')
    </main>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @stack('scripts')
    
    <script>
        $(document).ready(function() {
            // Auto-initialize DataTables
            $('.datatable').DataTable({
                pageLength: 25,
                responsive: true
            });
            
            // Auto-dismiss alerts
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>
