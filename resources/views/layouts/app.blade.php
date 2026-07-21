<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TNT Construction - CMS Pro')</title>
    
    <link rel="icon" type="image/png" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏗️</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        :root { --sidebar-width: 260px; --topbar-height: 64px; --primary: #4f46e5; --primary-dark: #3730a3; --gray-50: #f9fafb; --gray-100: #f3f4f6; --gray-200: #e5e7eb; --gray-600: #4b5563; --gray-700: #374151; --gray-800: #1f2937; --gray-900: #111827; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--gray-50); color: var(--gray-800); font-size: 0.875rem; -webkit-font-smoothing: antialiased; }
        .sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: var(--sidebar-width); background: var(--gray-900); z-index: 1040; overflow-y: auto; display: flex; flex-direction: column; }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        .sidebar-brand { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar-brand .logo-icon { width: 44px; height: 44px; background: linear-gradient(135deg, #4f46e5, #7c3aed); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 8px; }
        .sidebar-brand h4 { color: white; font-size: 0.95rem; font-weight: 700; margin: 0; }
        .sidebar-brand span { color: rgba(255,255,255,0.4); font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1.5px; }
        .sidebar-nav { flex: 1; padding: 12px; }
        .sidebar-label { color: rgba(255,255,255,0.3); font-size: 0.6rem; text-transform: uppercase; letter-spacing: 2px; padding: 16px 12px 6px; font-weight: 600; }
        .sidebar-label:first-child { padding-top: 4px; }
        .sidebar a { color: rgba(255,255,255,0.55); padding: 10px 12px; margin: 1px 0; border-radius: 8px; font-size: 0.8rem; font-weight: 500; display: flex; align-items: center; gap: 10px; text-decoration: none; transition: all 0.15s ease; }
        .sidebar a:hover { color: white; background: rgba(255,255,255,0.06); }
        .sidebar a.active { color: white; background: var(--primary); font-weight: 600; box-shadow: 0 2px 8px rgba(79,70,229,0.3); }
        .sidebar a i { width: 20px; text-align: center; font-size: 0.85rem; }
        .sidebar a .badge { margin-left: auto; font-size: 0.6rem; padding: 3px 7px; }
        .sidebar-footer { padding: 14px; text-align: center; color: rgba(255,255,255,0.25); font-size: 0.65rem; border-top: 1px solid rgba(255,255,255,0.08); }
        .topbar { position: fixed; top: 0; left: var(--sidebar-width); right: 0; height: var(--topbar-height); background: white; border-bottom: 1px solid var(--gray-200); z-index: 1030; display: flex; align-items: center; padding: 0 20px; gap: 16px; }
        .topbar-brand { display: flex; align-items: center; gap: 10px; min-width: 200px; }
        .topbar-logo { width: 36px; height: 36px; background: linear-gradient(135deg, #4f46e5, #7c3aed); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: white; }
        .topbar-title { font-weight: 700; font-size: 0.85rem; color: var(--gray-900); line-height: 1.2; }
        .topbar-domain { font-size: 0.6rem; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; }
        .search-box { flex: 1; max-width: 400px; position: relative; }
        .search-box input { width: 100%; padding: 8px 16px 8px 40px; border: 1px solid var(--gray-200); border-radius: 25px; font-size: 0.8rem; background: var(--gray-50); transition: all 0.2s; }
        .search-box input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,0.1); background: white; }
        .search-box i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.85rem; }
        .topbar-actions { display: flex; align-items: center; gap: 8px; margin-left: auto; }
        .btn-icon { width: 38px; height: 38px; border-radius: 10px; border: 1px solid var(--gray-200); background: white; color: var(--gray-600); display: flex; align-items: center; justify-content: center; cursor: pointer; position: relative; }
        .btn-icon:hover { background: var(--gray-50); }
        .btn-icon .badge { position: absolute; top: -4px; right: -4px; font-size: 0.55rem; min-width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; border-radius: 8px; }
        .user-btn { display: flex; align-items: center; gap: 8px; padding: 4px 12px 4px 4px; border-radius: 30px; border: none; background: transparent; cursor: pointer; }
        .user-btn:hover { background: var(--gray-100); }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.8rem; }
        .user-name { font-weight: 600; font-size: 0.78rem; }
        .user-role { font-size: 0.68rem; color: var(--gray-600); }
        .main-content { margin-left: var(--sidebar-width); margin-top: var(--topbar-height); padding: 24px 28px; min-height: calc(100vh - var(--topbar-height)); }
        .card { border: 1px solid var(--gray-200); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); background: white; }
        .card-header { background: white; border-bottom: 1px solid var(--gray-200); padding: 14px 18px; border-radius: 12px 12px 0 0; }
        .card-body { padding: 18px; }
        .table th { font-weight: 600; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-600); background: var(--gray-50); }
        .table td { vertical-align: middle; font-size: 0.8rem; }
        .badge { font-weight: 500; border-radius: 6px; padding: 4px 10px; }
        .btn { border-radius: 8px; font-weight: 500; font-size: 0.8rem; padding: 7px 14px; }
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.open { transform: translateX(0); } .topbar { left: 0; } .main-content { margin-left: 0; padding: 14px; } .topbar-brand { display: none; } }
        @media print { .sidebar, .topbar, .btn, .no-print { display: none !important; } .main-content { margin: 0 !important; padding: 0 !important; } }
    </style>
    <style>
        .pagination { margin-bottom: 0; }
        .page-link { padding: 3px 10px !important; font-size: 0.75rem !important; border-radius: 4px !important; margin: 0 1px; }
        .page-item.active .page-link { background: #4f46e5 !important; border-color: #4f46e5 !important; }
        .page-link:hover { background: #eef2ff !important; color: #4f46e5 !important; }
        .pagination-info { font-size: 0.7rem; color: #6b7280; padding: 3px 0; }
    </style>
    @stack('styles')
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="logo-icon">🏗️</div>
            <h4>TNT Construction</h4>
            <span>CMS Pro v2.0</span>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-label">Main</div>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="{{ route('projects.index') }}" class="{{ request()->routeIs('projects.*') && !request()->routeIs('projects.team*') && !request()->routeIs('projects.subcontractors*') ? 'active' : '' }}"><i class="fas fa-hard-hat"></i> Projects</a>
            
            <div class="sidebar-label">Engineering</div>
            <a href="{{ route('boq-items.index') }}" class="{{ request()->routeIs('boq-items.*') ? 'active' : '' }}"><i class="fas fa-calculator"></i> BOQ & Costing</a>
            <a href="{{ route('quantity-takeoffs.index') }}" class="{{ request()->routeIs('quantity-takeoffs.*') ? 'active' : '' }}"><i class="fas fa-ruler-combined"></i> Quantity Take-Off
                @php $toPending = \App\Models\QuantityTakeoff::where('status','draft')->count(); @endphp
                @if($toPending>0)<span class="badge bg-warning text-dark">{{ $toPending }}</span>@endif
            </a>
            <a href="{{ route('takeoff-sheets.index') }}" class="{{ request()->routeIs('takeoff-sheets.*') ? 'active' : '' }}"><i class="fas fa-table"></i> Takeoff Sheets</a>
            <a href="{{ route('ipcs.index') }}" class="{{ request()->routeIs('ipcs.*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> Payment Certificates
                @php $pc = \App\Models\Ipc::where('status','submitted')->count(); @endphp
                @if($pc>0)<span class="badge bg-warning text-dark">{{ $pc }}</span>@endif
            </a>
            <a href="{{ route('material-deliveries.index') }}" class="{{ request()->routeIs('material-deliveries.*') ? 'active' : '' }}"><i class="fas fa-truck-loading"></i> Material Deliveries</a>
            <a href="{{ route('actual-costs.index') }}" class="{{ request()->routeIs('actual-costs.*') ? 'active' : '' }}"><i class="fas fa-receipt"></i> Actual Costs</a>
            <a href="{{ route('gantt.index') }}" class="{{ request()->routeIs('gantt.*') ? 'active' : '' }}"><i class="fas fa-chart-gantt"></i> Gantt Chart</a>
            
            <div class="sidebar-label">Management</div>
            <a href="{{ route('subcontractors.index') }}" class="{{ request()->routeIs('subcontractors.*') ? 'active' : '' }}"><i class="fas fa-users"></i> Subcontractors</a>
            <a href="{{ route('cost-categories.index') }}" class="{{ request()->routeIs('cost-categories.*') ? 'active' : '' }}"><i class="fas fa-sitemap"></i> Cost Categories</a>
            
            <div class="sidebar-label">Reports</div>
            <a href="{{ route('reports.30-column') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="fas fa-table"></i> 30-Column Report</a>
            <a href="{{ route('actual-costs.variance') }}"><i class="fas fa-chart-bar"></i> Variance Report</a>
            
            @if(auth()->check() && auth()->user()->hasAnyPermission(['users.view','roles.view']))
            <div class="sidebar-label">Administration</div>
            @if(\App\Helpers\PermissionHelper::canView('users'))
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="fas fa-user-cog"></i> Users</a>
            @endif
            @if(\App\Helpers\PermissionHelper::canView('roles'))
            <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"><i class="fas fa-shield-alt"></i> Roles</a>
            @endif
            <a href="{{ route('admin.workflow.index') }}" class="{{ request()->routeIs('admin.workflow.*') ? 'active' : '' }}"><i class="fas fa-project-diagram"></i> Workflow Permissions</a>
            <a href="{{ route('admin.company-settings.index') }}" class="{{ request()->routeIs('admin.company-settings.*') ? 'active' : '' }}"><i class="fas fa-building"></i> Company Settings</a>
            <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><i class="fas fa-cog"></i> Tax Settings</a>
            <a href="{{ route('admin.audit.index') }}" class="{{ request()->routeIs('admin.audit.*') ? 'active' : '' }}"><i class="fas fa-history"></i> Audit Trail</a>
            @endif
            
            <div class="sidebar-label">Alerts</div>
            <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}"><i class="fas fa-bell"></i> Notifications
                @php $unread = \App\Models\Notification::unreadCount(); @endphp
                @if($unread>0)<span class="badge bg-danger">{{ $unread }}</span>@endif
            </a>
        </nav>
        <div class="sidebar-footer">© {{ date('Y') }} TNT Construction</div>
    </aside>

    <header class="topbar">
        <button class="btn-icon d-md-none" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fas fa-bars"></i></button>
        <div class="topbar-brand"><div class="topbar-logo">🏗️</div><div><div class="topbar-title">TNT Construction</div><div class="topbar-domain">www.tnt-constructions.com</div></div></div>
        <div class="search-box"><i class="fas fa-search"></i><input type="text" placeholder="Search..." onkeypress="if(event.key==='Enter'){window.location.href='{{ route('projects.index') }}?search='+this.value}"></div>
        <div class="topbar-actions">
            <div class="dropdown">
                <button class="btn-icon" data-bs-toggle="dropdown"><i class="fas fa-bell"></i>@if($unread>0)<span class="badge bg-danger">{{ $unread }}</span>@endif</button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg" style="width:320px;border-radius:12px;border:none;">
                    <div class="d-flex justify-content-between px-3 py-2 border-bottom"><strong>Notifications</strong><a href="{{ route('notifications.mark-all-read') }}" class="text-muted small" onclick="event.preventDefault();document.getElementById('mark-all-form').submit();">Mark all read</a><form id="mark-all-form" action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-none">@csrf</form></div>
                    <div style="max-height:280px;overflow-y:auto;">@foreach(\App\Models\Notification::recent(auth()->id(),5) as $n)<a href="{{ route('notifications.read',$n) }}" class="dropdown-item {{ $n->is_read?'':'bg-light' }}"><small><i class="fas fa-{{ $n->icon }} text-{{ $n->color }} me-1"></i></small>{{ Str::limit($n->title,40) }}<br><small class="text-muted">{{ $n->created_at->diffForHumans() }}</small></a>@endforeach</div>
                    <div class="text-center py-2 border-top"><a href="{{ route('notifications.index') }}" class="btn btn-sm btn-link">View All</a></div>
                </div>
            </div>
            @auth
            <div class="dropdown">
                <button class="user-btn" data-bs-toggle="dropdown"><div class="user-avatar">{{ auth()->user()->initials }}</div><div class="d-none d-md-block text-start"><div class="user-name">{{ auth()->user()->name }}</div><div class="user-role">{{ auth()->user()->role_label }}</div></div></button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg" style="border-radius:12px;border:none;min-width:200px;">
                    <div class="px-3 py-2"><strong>{{ auth()->user()->name }}</strong><br><small class="text-muted">{{ auth()->user()->email }}</small></div>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('admin.profile') }}" class="dropdown-item"><i class="fas fa-user-circle me-2"></i> My Profile</a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i> Sign Out</button></form>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}" class="btn btn-primary">Sign In</a>
            @endauth
        </div>
    </header>

    <main class="main-content">
        @if(session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if($errors->any())<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-triangle me-2"></i><strong>Please fix:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if(session('error'))<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @yield('content')
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>$(document).ready(function(){$('.datatable').DataTable({pageLength:25,responsive:true});setTimeout(()=>$('.alert').fadeOut('slow'),5000);});</script>
    @stack('scripts')
</body>
</html>
