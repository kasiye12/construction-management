@extends('layouts.app')

@section('title', 'Dashboard - CMS Pro')

@push('styles')
<style>
    .welcome-banner {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border-radius: 16px;
        padding: 28px 32px;
        color: white;
        margin-bottom: 24px;
    }
    .welcome-banner h3 { font-weight: 700; }
    .welcome-banner .role-badge {
        background: rgba(255,255,255,0.2);
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
    }
    .quick-stats { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px; }
    .quick-stat {
        background: white;
        border-radius: 12px;
        padding: 16px 20px;
        flex: 1;
        min-width: 140px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .quick-stat .icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
    }
    .quick-stat .value { font-size: 1.3rem; font-weight: 700; }
    .quick-stat .label { font-size: 0.7rem; color: #6b7280; text-transform: uppercase; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Welcome back, {{ $user->name }}! 👋</h3>
                <p class="mb-0 opacity-75">
                    @switch($role)
                        @case('admin') You have full system access. Here's your overview. @break
                        @case('manager') Manage projects, approvals, and team performance. @break
                        @case('engineer') Track BOQ items, IPCs, and project progress. @break
                        @case('finance') Review payments, approvals, and financial reports. @break
                        @default View project status and reports.
                    @endswitch
                </p>
            </div>
            <span class="role-badge">{{ $user->role_label }}</span>
        </div>
    </div>

    <!-- ========== ADMIN DASHBOARD ========== -->
    @if($role == 'admin')
    <div class="quick-stats">
        <div class="quick-stat"><div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-building"></i></div><div><div class="value">{{ $activeProjects }}</div><div class="label">Active Projects</div></div></div>
        <div class="quick-stat"><div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-users"></i></div><div><div class="value">{{ $activeUsers }}</div><div class="label">Active Users</div></div></div>
        <div class="quick-stat"><div class="icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-file-invoice"></i></div><div><div class="value">{{ $pendingIpcs }}</div><div class="label">Pending IPCs</div></div></div>
        <div class="quick-stat"><div class="icon bg-info bg-opacity-10 text-info"><i class="fas fa-shield-alt"></i></div><div><div class="value">{{ $totalRoles }}</div><div class="label">Roles</div></div></div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4"><div class="card-header"><h5 class="mb-0">📊 Project Status Overview</h5></div><div class="card-body"><canvas id="statusChart" height="200"></canvas></div></div>
            <div class="card"><div class="card-header d-flex justify-content-between"><h5 class="mb-0">📁 Recent Projects</h5><a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">View All</a></div>
                <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Project</th><th>Status</th><th>Amount</th><th>Progress</th></tr></thead>
                <tbody>@foreach($recentProjects as $p)<tr><td><a href="{{ route('projects.show',$p) }}">{{ Str::limit($p->name,40) }}</a></td><td><span class="badge bg-{{ $p->status=='active'?'success':($p->status=='completed'?'primary':'warning') }}">{{ ucfirst($p->status) }}</span></td><td>{{ number_format($p->contract_amount,0) }} ETB</td><td>@php $c=$p->boqItems->where('status','completed')->count(); $t=$p->boqItems->count(); $pct=$t>0?($c/$t)*100:0; @endphp<div class="progress" style="height:6px;"><div class="progress-bar" style="width:{{$pct}}%"></div></div><small>{{number_format($pct,0)}}%</small></td></tr>@endforeach</tbody></table></div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4"><div class="card-header"><h5 class="mb-0">💰 Revenue vs Budget</h5></div><div class="card-body text-center"><h2 class="{{ $profitLoss>=0?'text-success':'text-danger' }}">{{ number_format($profitLoss,0) }} ETB</h2><p class="text-muted">Revenue: {{ number_format($totalRevenue,0) }} | Budget: {{ number_format($totalBudget,0) }}</p></div></div>
            <div class="card mb-4"><div class="card-header"><h5 class="mb-0">📄 Recent IPCs</h5></div><div class="card-body">@foreach($recentIpcs as $ipc)<div class="border-bottom pb-2 mb-2"><a href="{{ route('ipcs.show',$ipc) }}">{{ $ipc->ipc_number }}</a><br><small>{{ $ipc->subcontractor->name ?? 'N/A' }} - {{ number_format($ipc->net_payment_amount,0) }} ETB</small></div>@endforeach</div></div>
            <div class="card"><div class="card-header"><h5 class="mb-0">🔔 Recent Notifications</h5></div><div class="card-body">@foreach($recentNotifications->take(5) as $n)<div class="border-bottom pb-2 mb-2"><small class="text-{{ $n->color }}"><i class="fas fa-{{ $n->icon }}"></i></small> {{ Str::limit($n->title,40) }}<br><small class="text-muted">{{ $n->created_at->diffForHumans() }}</small></div>@endforeach</div></div>
        </div>
    </div>
    @endif

    <!-- ========== MANAGER DASHBOARD ========== -->
    @if($role == 'manager')
    <div class="quick-stats">
        <div class="quick-stat"><div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-building"></i></div><div><div class="value">{{ $activeProjects }}</div><div class="label">Active Projects</div></div></div>
        <div class="quick-stat"><div class="icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-file-invoice"></i></div><div><div class="value">{{ $pendingIpcs }}</div><div class="label">Pending Approval</div></div></div>
        <div class="quick-stat"><div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div><div><div class="value">{{ $approvedIpcs }}</div><div class="label">Approved</div></div></div>
        <div class="quick-stat"><div class="icon {{ $profitLoss>=0?'bg-success':'bg-danger' }} bg-opacity-10 {{ $profitLoss>=0?'text-success':'text-danger' }}"><i class="fas fa-chart-line"></i></div><div><div class="value">{{ number_format($profitLoss,0) }}</div><div class="label">Profit/Loss</div></div></div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4"><div class="card-header"><h5 class="mb-0">📄 IPCs Requiring Attention</h5></div>
                <div class="table-responsive"><table class="table mb-0"><thead><tr><th>IPC</th><th>Subcontractor</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>@foreach($recentIpcs as $ipc)<tr><td>{{ $ipc->ipc_number }}</td><td>{{ $ipc->subcontractor->name ?? 'N/A' }}</td><td>{{ number_format($ipc->net_payment_amount,0) }} ETB</td><td><span class="badge bg-{{ $ipc->status=='approved'?'success':($ipc->status=='submitted'?'warning':'secondary') }}">{{ ucfirst($ipc->status) }}</span></td><td><a href="{{ route('ipcs.show',$ipc) }}" class="btn btn-sm btn-primary">Review</a></td></tr>@endforeach</tbody></table></div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4"><div class="card-header"><h5 class="mb-0">📊 Project Status</h5></div><div class="card-body"><canvas id="statusChart" height="200"></canvas></div></div>
            <div class="card"><div class="card-header"><h5 class="mb-0">🔔 Notifications</h5></div><div class="card-body">@foreach($recentNotifications as $n)<div class="border-bottom pb-2 mb-2"><small>{{ $n->title }}</small><br><small class="text-muted">{{ $n->created_at->diffForHumans() }}</small></div>@endforeach</div></div>
        </div>
    </div>
    @endif

    <!-- ========== ENGINEER DASHBOARD ========== -->
    @if($role == 'engineer')
    <div class="quick-stats">
        <div class="quick-stat"><div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-list"></i></div><div><div class="value">{{ $myBoqItems }}</div><div class="label">BOQ Items</div></div></div>
        <div class="quick-stat"><div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-file-invoice"></i></div><div><div class="value">{{ $myIpcs }}</div><div class="label">IPCs Created</div></div></div>
        <div class="quick-stat"><div class="icon bg-info bg-opacity-10 text-info"><i class="fas fa-check-circle"></i></div><div><div class="value">{{ $pendingIpcs }}</div><div class="label">Pending Approval</div></div></div>
        <div class="quick-stat"><div class="icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-building"></i></div><div><div class="value">{{ $activeProjects }}</div><div class="label">Active Projects</div></div></div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4"><div class="card-header d-flex justify-content-between"><h5 class="mb-0">📁 Recent Projects</h5><a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">View All</a></div>
                <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Project</th><th>Status</th><th>Amount</th></tr></thead><tbody>@foreach($recentProjects as $p)<tr><td><a href="{{ route('projects.show',$p) }}">{{ Str::limit($p->name,40) }}</a></td><td>{{ ucfirst($p->status) }}</td><td>{{ number_format($p->contract_amount,0) }} ETB</td></tr>@endforeach</tbody></table></div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4"><div class="card-header"><h5 class="mb-0">📄 Recent IPCs</h5></div><div class="card-body">@foreach($recentIpcs as $ipc)<div class="border-bottom pb-2 mb-2"><a href="{{ route('ipcs.show',$ipc) }}">{{ $ipc->ipc_number }}</a><br><small>{{ number_format($ipc->net_payment_amount,0) }} ETB - {{ ucfirst($ipc->status) }}</small></div>@endforeach</div></div>
            <div class="card"><div class="card-header d-flex justify-content-between"><h5 class="mb-0">⚡ Quick Actions</h5></div><div class="card-body d-grid gap-2"><a href="{{ route('boq-items.create') }}" class="btn btn-primary">Add BOQ Item</a><a href="{{ route('ipcs.create') }}" class="btn btn-success">Create IPC</a><a href="{{ route('reports.30-column') }}" class="btn btn-outline-primary">View Reports</a></div></div>
        </div>
    </div>
    @endif

    <!-- ========== FINANCE DASHBOARD ========== -->
    @if($role == 'finance')
    <div class="quick-stats">
        <div class="quick-stat"><div class="icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-clock"></i></div><div><div class="value">{{ $pendingIpcs }}</div><div class="label">Pending Approval</div></div></div>
        <div class="quick-stat"><div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div><div><div class="value">{{ $paidIpcs }}</div><div class="label">Paid IPCs</div></div></div>
        <div class="quick-stat"><div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-money-bill"></i></div><div><div class="value">{{ number_format($totalPaidAmount,0) }}</div><div class="label">Total Paid (ETB)</div></div></div>
        <div class="quick-stat"><div class="icon bg-info bg-opacity-10 text-info"><i class="fas fa-hourglass-half"></i></div><div><div class="value">{{ number_format($totalPendingAmount,0) }}</div><div class="label">Pending Amount (ETB)</div></div></div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card"><div class="card-header"><h5 class="mb-0">📄 IPCs Requiring Financial Review</h5></div>
                <div class="table-responsive"><table class="table mb-0"><thead><tr><th>IPC</th><th>Project</th><th>Subcontractor</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>@foreach($recentIpcs as $ipc)<tr><td><a href="{{ route('ipcs.show',$ipc) }}">{{ $ipc->ipc_number }}</a></td><td>{{ $ipc->project->name ?? 'N/A' }}</td><td>{{ $ipc->subcontractor->name ?? 'N/A' }}</td><td>{{ number_format($ipc->net_payment_amount,0) }} ETB</td><td><span class="badge bg-{{ $ipc->status=='approved'?'success':'warning' }}">{{ ucfirst($ipc->status) }}</span></td></tr>@endforeach</tbody></table></div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4"><div class="card-header"><h5 class="mb-0">💰 Summary</h5></div><div class="card-body"><p>Paid: <strong>{{ number_format($totalPaidAmount,0) }} ETB</strong></p><p>Pending: <strong>{{ number_format($totalPendingAmount,0) }} ETB</strong></p><p>Total Revenue: <strong>{{ number_format($totalRevenue,0) }} ETB</strong></p></div></div>
        </div>
    </div>
    @endif

    <!-- ========== VIEWER DASHBOARD ========== -->
    @if($role == 'viewer')
    <div class="quick-stats">
        <div class="quick-stat"><div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-building"></i></div><div><div class="value">{{ $activeProjects }}</div><div class="label">Active Projects</div></div></div>
        <div class="quick-stat"><div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-file-invoice"></i></div><div><div class="value">{{ $totalIpcs }}</div><div class="label">Total IPCs</div></div></div>
        <div class="quick-stat"><div class="icon bg-info bg-opacity-10 text-info"><i class="fas fa-chart-line"></i></div><div><div class="value">{{ number_format($totalRevenue,0) }}</div><div class="label">Total Revenue</div></div></div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card"><div class="card-header"><h5 class="mb-0">📁 Projects</h5></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Project</th><th>Status</th><th>Amount</th></tr></thead><tbody>@foreach($recentProjects as $p)<tr><td>{{ Str::limit($p->name,50) }}</td><td>{{ ucfirst($p->status) }}</td><td>{{ number_format($p->contract_amount,0) }} ETB</td></tr>@endforeach</tbody></table></div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-header"><h5 class="mb-0">📊 Summary</h5></div><div class="card-body"><p>Projects: <strong>{{ $totalProjects }}</strong></p><p>Active: <strong>{{ $activeProjects }}</strong></p><p>Revenue: <strong>{{ number_format($totalRevenue,0) }} ETB</strong></p></div></div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
@if(in_array($role, ['admin','manager']))
new Chart(document.getElementById('statusChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Active','Completed','On Hold','Cancelled'],
        datasets: [{ data: [{{ $projectStatusData['active']??0 }},{{ $projectStatusData['completed']??0 }},{{ $projectStatusData['on_hold']??0 }},{{ $projectStatusData['cancelled']??0 }}], backgroundColor: ['#10b981','#3b82f6','#f59e0b','#ef4444'] }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom' } } }
});
@endif
</script>
@endpush
