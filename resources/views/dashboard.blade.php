@extends('layouts.app')

@section('title', 'Dashboard - TNT Construction CMS')

@push('styles')
<style>
    .welcome-banner { background: linear-gradient(135deg, #1a237e, #4f46e5); border-radius: 16px; padding: 24px 28px; color: white; margin-bottom: 20px; }
    .stat-card { background: white; border-radius: 12px; padding: 18px; border: 1px solid #e5e7eb; transition: all 0.2s; display: flex; align-items: center; gap: 14px; }
    .stat-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.08); transform: translateY(-2px); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .stat-value { font-size: 1.4rem; font-weight: 700; line-height: 1.2; }
    .stat-label { font-size: 0.7rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
    .quick-link { display: block; padding: 16px; border-radius: 10px; text-align: center; color: white; text-decoration: none; transition: all 0.2s; font-weight: 600; font-size: 0.85rem; }
    .quick-link:hover { transform: scale(1.03); color: white; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8"><h3 class="mb-1">Welcome back, {{ auth()->user()->name }}! 👋</h3><p class="mb-0 opacity-75">TNT Construction & Trading | www.tnt-constructions.com</p></div>
            <div class="col-md-4 text-end"><span class="badge bg-white text-dark fs-6">{{ auth()->user()->role_label }}</span><br><small class="opacity-75">{{ now()->format('l, F d, Y') }}</small></div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 mb-3"><div class="stat-card"><div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-hard-hat"></i></div><div><div class="stat-value">{{ \App\Models\Project::where('status','active')->count() }}</div><div class="stat-label">Active Projects</div></div></div></div>
        <div class="col-md-3 mb-3"><div class="stat-card"><div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-file-invoice-dollar"></i></div><div><div class="stat-value">{{ \App\Models\Ipc::where('status','submitted')->count() }}</div><div class="stat-label">Pending IPCs</div></div></div></div>
        <div class="col-md-3 mb-3"><div class="stat-card"><div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-truck-loading"></i></div><div><div class="stat-value">{{ \App\Models\MaterialDelivery::count() }}</div><div class="stat-label">Deliveries</div></div></div></div>
        <div class="col-md-3 mb-3"><div class="stat-card"><div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fas fa-ruler-combined"></i></div><div><div class="stat-value">{{ \App\Models\QuantityTakeoff::count() }}</div><div class="stat-label">Measurements</div></div></div></div>
    </div>

    <div class="row mb-4">
        <div class="col-md-2 col-6 mb-2"><a href="{{ route('projects.index') }}" class="quick-link" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);"><i class="fas fa-hard-hat d-block mb-2" style="font-size:1.5rem;"></i> Projects</a></div>
        <div class="col-md-2 col-6 mb-2"><a href="{{ route('boq-items.index') }}" class="quick-link" style="background:linear-gradient(135deg,#f59e0b,#ef4444);"><i class="fas fa-calculator d-block mb-2" style="font-size:1.5rem;"></i> BOQ & Costing</a></div>
        <div class="col-md-2 col-6 mb-2"><a href="{{ route('ipcs.index') }}" class="quick-link" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="fas fa-file-invoice d-block mb-2" style="font-size:1.5rem;"></i> Certificates</a></div>
        <div class="col-md-2 col-6 mb-2"><a href="{{ route('contractual-reports.index') }}" class="quick-link" style="background:linear-gradient(135deg,#e11d48,#be123c);"><i class="fas fa-file-contract d-block mb-2" style="font-size:1.5rem;"></i> Contractual (CAD-01)</a></div>
        <div class="col-md-2 col-6 mb-2"><a href="{{ route('equipment.index') }}" class="quick-link" style="background:linear-gradient(135deg,#0284c7,#0369a1);"><i class="fas fa-truck-monster d-block mb-2" style="font-size:1.5rem;"></i> Fleet Registry</a></div>
        <div class="col-md-2 col-6 mb-2"><a href="{{ route('pm-schedules.index') }}" class="quick-link" style="background:linear-gradient(135deg,#d97706,#b45309);"><i class="fas fa-calendar-check d-block mb-2" style="font-size:1.5rem;"></i> PM (OF/074)</a></div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-3"><div class="card h-100"><div class="card-header d-flex justify-content-between align-items-center"><h6 class="mb-0">📄 Recent IPCs</h6><a href="{{ route('ipcs.index') }}" class="btn btn-sm btn-outline-primary py-0 px-2">View</a></div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>IPC</th><th>Project</th><th>Status</th></tr></thead><tbody>@foreach(\App\Models\Ipc::with('project')->latest()->take(5)->get() as $ipc)<tr><td><a href="{{ route('ipcs.show',$ipc) }}">{{ $ipc->ipc_number }}</a></td><td>{{ Str::limit($ipc->project->name??'N/A',12) }}</td><td><span class="badge bg-{{ $ipc->status=='approved'?'success':'warning' }}">{{ ucfirst($ipc->status) }}</span></td></tr>@endforeach</tbody></table></div></div></div>
        
        <div class="col-md-3 mb-3"><div class="card h-100"><div class="card-header d-flex justify-content-between align-items-center"><h6 class="mb-0">📑 Contractual (CAD-01)</h6><a href="{{ route('contractual-reports.index') }}" class="btn btn-sm btn-outline-primary py-0 px-2">View</a></div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Report #</th><th>Project</th><th>Status</th></tr></thead><tbody>@foreach(\App\Models\ContractualReport::with('project')->latest()->take(5)->get() as $cr)<tr><td><a href="{{ route('contractual-reports.show',$cr) }}">{{ $cr->report_number }}</a></td><td>{{ Str::limit($cr->project->name??'N/A',12) }}</td><td>{!! $cr->status_badge !!}</td></tr>@endforeach</tbody></table></div></div></div>

        <div class="col-md-3 mb-3"><div class="card h-100"><div class="card-header d-flex justify-content-between align-items-center"><h6 class="mb-0">🚜 Fleet & Maintenance (EAMD)</h6><a href="{{ route('equipment.index') }}" class="btn btn-sm btn-outline-primary py-0 px-2">View</a></div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Equipment</th><th>Plate</th><th>Status</th></tr></thead><tbody>@foreach(\App\Models\Equipment::latest()->take(5)->get() as $eq)<tr><td><a href="{{ route('equipment.show',$eq) }}">{{ $eq->equipment_type }}</a></td><td><span class="badge bg-light text-dark border">{{ $eq->plate_serial_no }}</span></td><td>{!! $eq->service_status_badge !!}</td></tr>@endforeach</tbody></table></div></div></div>

        <div class="col-md-3 mb-3"><div class="card h-100"><div class="card-header d-flex justify-content-between align-items-center"><h6 class="mb-0">📝 Recent Activity</h6><a href="{{ route('admin.audit.index') }}" class="btn btn-sm btn-outline-primary py-0 px-2">View</a></div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>User</th><th>Action</th><th>Time</th></tr></thead><tbody>@foreach(\App\Models\AuditTrail::with('user')->latest()->take(5)->get() as $log)<tr><td>{{ $log->user->name??'System' }}</td><td><span class="badge bg-{{ $log->action=='created'?'success':'info' }}">{{ $log->action }}</span></td><td><small>{{ $log->created_at->diffForHumans() }}</small></td></tr>@endforeach</tbody></table></div></div></div>
    </div>
</div>
@endsection
