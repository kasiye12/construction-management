@extends('layouts.app')

@section('title', 'Dashboard - TNT Construction CMS')

@push('styles')
<style>
    .welcome-banner { background: linear-gradient(135deg, #1a237e, #4f46e5); border-radius: 16px; padding: 24px 28px; color: white; margin-bottom: 20px; }
    .stat-card { background: white; border-radius: 12px; padding: 18px; border: 1px solid #e5e7eb; transition: all 0.2s; display: flex; align-items: center; gap: 14px; height: 100%; }
    .stat-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.08); transform: translateY(-2px); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .stat-value { font-size: 1.4rem; font-weight: 700; line-height: 1.2; }
    .stat-label { font-size: 0.7rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
    .quick-link { display: block; padding: 16px; border-radius: 10px; text-align: center; color: white; text-decoration: none; transition: all 0.2s; font-weight: 600; font-size: 0.85rem; height: 100%; }
    .quick-link:hover { transform: scale(1.03); color: white; box-shadow: 0 6px 16px rgba(0,0,0,0.15); }
    .widget-header-btn { font-size: 0.75rem; padding: 2px 8px; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Welcome Banner --}}
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="mb-1">Welcome back, {{ auth()->user()->name }}! 👋</h3>
                <p class="mb-0 opacity-75">TNT Construction & Trading | 티ኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች</p>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-white text-dark fs-6">{{ auth()->user()->role_label }}</span>
                @if(auth()->user()->is_super_admin)
                    <span class="badge bg-danger ms-1"><i class="fas fa-crown me-1"></i> Super Admin</span>
                @endif
                <br>
                <small class="opacity-75">{{ now()->format('l, F d, Y') }}</small>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- 1. ROLE & PERMISSION-BASED STAT CARDS (KPIs)             --}}
    {{-- ======================================================== --}}
    <div class="row mb-4">
        @if(auth()->user()->hasPermission('contractual-reports.view') && !auth()->user()->hasPermission('projects.view'))
            {{-- Specialized Contractual Officer KPI Set --}}
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-file-contract"></i></div>
                    <div>
                        <div class="stat-value">{{ $totalCadReports }}</div>
                        <div class="stat-label">Total CAD-01 Reports</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="stat-value">{{ $pendingCadReports }}</div>
                        <div class="stat-label">Pending Review</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="stat-value">{{ $approvedCadReports }}</div>
                        <div class="stat-label">Approved Reports</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-exclamation-triangle"></i></div>
                    <div>
                        <div class="stat-value">{{ $totalClaimEvents }}</div>
                        <div class="stat-label">Logged Claim Events</div>
                    </div>
                </div>
            </div>
        @else
            {{-- Modular General / Multi-Role KPI Cards --}}
            @if(auth()->user()->hasPermission('projects.view'))
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-hard-hat"></i></div>
                    <div>
                        <div class="stat-value">{{ $activeProjects }}</div>
                        <div class="stat-label">Active Projects</div>
                    </div>
                </div>
            </div>
            @endif

            @if(auth()->user()->hasPermission('contractual-reports.view'))
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-file-contract"></i></div>
                    <div>
                        <div class="stat-value">{{ $totalCadReports }}</div>
                        <div class="stat-label">CAD-01 Reports ({{ $pendingCadReports }} Pending)</div>
                    </div>
                </div>
            </div>
            @endif

            @if(auth()->user()->hasPermission('ipc.view'))
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div>
                        <div class="stat-value">{{ $pendingIpcs }}</div>
                        <div class="stat-label">Pending IPCs</div>
                    </div>
                </div>
            </div>
            @endif

            @if(auth()->user()->hasPermission('deliveries.view'))
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-truck-loading"></i></div>
                    <div>
                        <div class="stat-value">{{ $totalDeliveries }}</div>
                        <div class="stat-label">Material Deliveries</div>
                    </div>
                </div>
            </div>
            @endif

            @if(auth()->user()->hasPermission('takeoff.view'))
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fas fa-ruler-combined"></i></div>
                    <div>
                        <div class="stat-value">{{ $totalTakeoffs }}</div>
                        <div class="stat-label">QTO Measurements</div>
                    </div>
                </div>
            </div>
            @endif

            @if(auth()->user()->hasPermission('equipment.view'))
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fas fa-truck-monster"></i></div>
                    <div>
                        <div class="stat-value">{{ $totalEquipment }}</div>
                        <div class="stat-label">Fleet Registered</div>
                    </div>
                </div>
            </div>
            @endif

            @if(auth()->user()->hasPermission('pm-schedules.view'))
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-calendar-check"></i></div>
                    <div>
                        <div class="stat-value">{{ $overduePmCount }}</div>
                        <div class="stat-label">PM Overdue</div>
                    </div>
                </div>
            </div>
            @endif

            @if(auth()->user()->hasPermission('equipment-breakdowns.view'))
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-tools"></i></div>
                    <div>
                        <div class="stat-value">{{ $downBreakdownCount }}</div>
                        <div class="stat-label">Down Equipment</div>
                    </div>
                </div>
            </div>
            @endif

            @if(auth()->user()->hasPermission('users.view') && !auth()->user()->hasPermission('takeoff.view'))
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-dark bg-opacity-10 text-dark"><i class="fas fa-users-cog"></i></div>
                    <div>
                        <div class="stat-value">{{ $activeUsers }}</div>
                        <div class="stat-label">Active Users</div>
                    </div>
                </div>
            </div>
            @endif
        @endif
    </div>

    {{-- ======================================================== --}}
    {{-- 2. DYNAMIC QUICK ACTION LINKS (ONLY PERMITTED MODULES)   --}}
    {{-- ======================================================== --}}
    @php
        $hasAnyQuickLink = auth()->user()->hasAnyPermission([
            'projects.view', 'boq.view', 'takeoff-sheets.view', 'contractual-reports.view',
            'ipc.view', 'deliveries.view', 'actual-costs.view', 'equipment.view',
            'pm-schedules.view', 'equipment-breakdowns.view', 'equipment-logs.view',
            'subcontractors.view', 'reports.view', 'users.view', 'roles.view'
        ]);
    @endphp

    @if($hasAnyQuickLink)
    <div class="row mb-4">
        @if(auth()->user()->hasPermission('projects.view'))
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('projects.index') }}" class="quick-link" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                <i class="fas fa-hard-hat d-block mb-2" style="font-size:1.5rem;"></i> Projects
            </a>
        </div>
        @endif

        @if(auth()->user()->hasPermission('boq.view'))
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('boq-items.index') }}" class="quick-link" style="background:linear-gradient(135deg,#f59e0b,#ef4444);">
                <i class="fas fa-calculator d-block mb-2" style="font-size:1.5rem;"></i> BOQ & Costing
            </a>
        </div>
        @endif

        @if(auth()->user()->hasPermission('takeoff-sheets.view'))
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('takeoff-sheets.index') }}" class="quick-link" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);">
                <i class="fas fa-table d-block mb-2" style="font-size:1.5rem;"></i> Takeoff Sheets
            </a>
        </div>
        @endif

        @if(auth()->user()->hasPermission('contractual-reports.view'))
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('contractual-reports.index') }}" class="quick-link" style="background:linear-gradient(135deg,#e11d48,#be123c);">
                <i class="fas fa-file-contract d-block mb-2" style="font-size:1.5rem;"></i> Contractual (CAD-01)
            </a>
        </div>
        @if(auth()->user()->hasPermission('contractual-reports.create') && !auth()->user()->hasPermission('projects.view'))
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('contractual-reports.create') }}" class="quick-link" style="background:linear-gradient(135deg,#059669,#10b981);">
                <i class="fas fa-plus-circle d-block mb-2" style="font-size:1.5rem;"></i> + New CAD-01
            </a>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('contractual-reports.download-template') }}" class="quick-link" style="background:linear-gradient(135deg,#0284c7,#0369a1);">
                <i class="fas fa-file-excel d-block mb-2" style="font-size:1.5rem;"></i> Excel Template
            </a>
        </div>
        @endif
        @endif

        @if(auth()->user()->hasPermission('ipc.view'))
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('ipcs.index') }}" class="quick-link" style="background:linear-gradient(135deg,#10b981,#059669);">
                <i class="fas fa-file-invoice-dollar d-block mb-2" style="font-size:1.5rem;"></i> Certificates
            </a>
        </div>
        @endif

        @if(auth()->user()->hasPermission('deliveries.view'))
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('material-deliveries.index') }}" class="quick-link" style="background:linear-gradient(135deg,#ea580c,#c2410c);">
                <i class="fas fa-truck-loading d-block mb-2" style="font-size:1.5rem;"></i> Deliveries
            </a>
        </div>
        @endif

        @if(auth()->user()->hasPermission('actual-costs.view'))
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('actual-costs.index') }}" class="quick-link" style="background:linear-gradient(135deg,#0d9488,#0f766e);">
                <i class="fas fa-receipt d-block mb-2" style="font-size:1.5rem;"></i> Actual Costs
            </a>
        </div>
        @endif

        @if(auth()->user()->hasPermission('equipment.view'))
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('equipment.index') }}" class="quick-link" style="background:linear-gradient(135deg,#0284c7,#0369a1);">
                <i class="fas fa-truck-monster d-block mb-2" style="font-size:1.5rem;"></i> Fleet Registry
            </a>
        </div>
        @endif

        @if(auth()->user()->hasPermission('pm-schedules.view'))
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('pm-schedules.index') }}" class="quick-link" style="background:linear-gradient(135deg,#d97706,#b45309);">
                <i class="fas fa-calendar-check d-block mb-2" style="font-size:1.5rem;"></i> PM (OF/074)
            </a>
        </div>
        @endif

        @if(auth()->user()->hasPermission('equipment-breakdowns.view') && !auth()->user()->hasPermission('equipment.view'))
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('equipment-breakdowns.index') }}" class="quick-link" style="background:linear-gradient(135deg,#dc2626,#991b1b);">
                <i class="fas fa-tools d-block mb-2" style="font-size:1.5rem;"></i> Breakdowns
            </a>
        </div>
        @endif

        @if(auth()->user()->hasPermission('subcontractors.view'))
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('subcontractors.index') }}" class="quick-link" style="background:linear-gradient(135deg,#6366f1,#4338ca);">
                <i class="fas fa-users d-block mb-2" style="font-size:1.5rem;"></i> Subcontractors
            </a>
        </div>
        @endif

        @if(auth()->user()->hasPermission('reports.view') && !auth()->user()->hasPermission('contractual-reports.view'))
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('reports.30-column') }}" class="quick-link" style="background:linear-gradient(135deg,#0f766e,#115e59);">
                <i class="fas fa-chart-pie d-block mb-2" style="font-size:1.5rem;"></i> Executive Reports
            </a>
        </div>
        @endif

        @if(auth()->user()->hasPermission('users.view') && auth()->user()->hasPermission('roles.view'))
        <div class="col-xl-2 col-lg-3 col-md-4 col-6 mb-2">
            <a href="{{ route('admin.users.index') }}" class="quick-link" style="background:linear-gradient(135deg,#334155,#1e293b);">
                <i class="fas fa-user-cog d-block mb-2" style="font-size:1.5rem;"></i> User Management
            </a>
        </div>
        @endif
    </div>
    @endif

    {{-- ======================================================== --}}
    {{-- 3. DYNAMIC DATA WIDGETS & TABLES (FILTERED BY PERMISSION) --}}
    {{-- ======================================================== --}}
    @php
        // Count active widget panels to dynamically assign responsive widths
        $widgetCount = 0;
        if(auth()->user()->hasPermission('ipc.view')) $widgetCount++;
        if(auth()->user()->hasPermission('contractual-reports.view')) $widgetCount++;
        if(auth()->user()->hasPermission('contractual-reports.view') && !auth()->user()->hasPermission('projects.view')) $widgetCount++;
        if(auth()->user()->hasPermission('equipment.view')) $widgetCount++;
        if(auth()->user()->hasPermission('audit.view') || auth()->user()->isAdmin()) $widgetCount++;
        if(auth()->user()->hasPermission('projects.view') && !auth()->user()->hasPermission('ipc.view')) $widgetCount++;

        // Determine column width based on visible widgets
        $colClass = 'col-xl-3 col-lg-6';
        if ($widgetCount === 1) $colClass = 'col-12';
        elseif ($widgetCount === 2) $colClass = 'col-lg-6 col-12';
        elseif ($widgetCount === 3) $colClass = 'col-lg-4 col-md-6 col-12';
    @endphp

    <div class="row">
        {{-- Widget A: Recent Payment Certificates (IPCs) --}}
        @if(auth()->user()->hasPermission('ipc.view'))
        <div class="{{ $colClass }} mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-file-invoice-dollar text-success me-1"></i> Recent IPCs</h6>
                    <a href="{{ route('ipcs.index') }}" class="btn btn-sm btn-outline-primary widget-header-btn">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light"><tr><th>IPC</th><th>Project</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($recentIpcs as $ipc)
                            <tr>
                                <td><a href="{{ route('ipcs.show',$ipc) }}" class="fw-bold text-decoration-none">{{ $ipc->ipc_number }}</a></td>
                                <td>{{ Str::limit($ipc->project->name ?? 'N/A', 14) }}</td>
                                <td><span class="badge bg-{{ $ipc->status=='approved'?'success':'warning' }}">{{ ucfirst($ipc->status) }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No IPCs recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Widget B: Contractual Follow-up Reports (CAD-01) --}}
        @if(auth()->user()->hasPermission('contractual-reports.view'))
        <div class="{{ $colClass }} mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-file-contract text-danger me-1"></i> Contractual (CAD-01)</h6>
                    <a href="{{ route('contractual-reports.index') }}" class="btn btn-sm btn-outline-primary widget-header-btn">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light"><tr><th>Report #</th><th>Project</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($recentCadReports as $cr)
                            <tr>
                                <td><a href="{{ route('contractual-reports.show',$cr) }}" class="fw-bold text-decoration-none">{{ $cr->report_number }}</a></td>
                                <td>{{ Str::limit($cr->project->name ?? 'N/A', 14) }}</td>
                                <td>{!! $cr->status_badge !!}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No CAD-01 reports recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Widget C: Specialized Claim & Delay Incidents (For Contractual Specialists) --}}
        @if(auth()->user()->hasPermission('contractual-reports.view') && !auth()->user()->hasPermission('projects.view'))
        <div class="{{ $colClass }} mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle text-warning me-1"></i> Recent Claim & Delay Incidents</h6>
                    <span class="badge bg-danger">{{ $totalClaimEvents }} Total</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light"><tr><th>Date</th><th>Issue</th><th>Cause Description</th></tr></thead>
                        <tbody>
                            @forelse($recentClaimEvents as $event)
                            <tr>
                                <td><small class="text-muted">{{ $event->event_date ? $event->event_date->format('M d') : 'N/A' }}</small></td>
                                <td><span class="badge bg-light text-dark border">#{{ $event->issue_number ?? '1' }}</span></td>
                                <td><span title="{{ $event->cause_description }}">{{ Str::limit($event->cause_description, 28) }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No claim incidents recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Widget D: Fleet & Maintenance (EAMD) --}}
        @if(auth()->user()->hasPermission('equipment.view'))
        <div class="{{ $colClass }} mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-truck-monster text-info me-1"></i> Fleet & Maintenance (EAMD)</h6>
                    <a href="{{ route('equipment.index') }}" class="btn btn-sm btn-outline-primary widget-header-btn">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light"><tr><th>Equipment</th><th>Plate</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($recentEquipment as $eq)
                            <tr>
                                <td><a href="{{ route('equipment.show',$eq) }}" class="fw-bold text-decoration-none">{{ $eq->equipment_type }}</a></td>
                                <td><span class="badge bg-light text-dark border">{{ $eq->plate_serial_no }}</span></td>
                                <td>{!! $eq->service_status_badge !!}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No equipment assets found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Widget E: Recent Projects (Shown when user has projects.view but not ipc.view) --}}
        @if(auth()->user()->hasPermission('projects.view') && !auth()->user()->hasPermission('ipc.view'))
        <div class="{{ $colClass }} mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-hard-hat text-primary me-1"></i> Active Projects</h6>
                    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary widget-header-btn">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light"><tr><th>Project Name</th><th>Client</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($recentProjects as $proj)
                            <tr>
                                <td><a href="{{ route('projects.show', $proj) }}" class="fw-bold text-decoration-none">{{ Str::limit($proj->name, 20) }}</a></td>
                                <td>{{ Str::limit($proj->client_name ?? 'N/A', 15) }}</td>
                                <td><span class="badge bg-{{ $proj->status=='active'?'success':'secondary' }}">{{ ucfirst($proj->status) }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No projects assigned.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Widget F: Recent System Activity / Audit Trail --}}
        @if(auth()->user()->hasPermission('audit.view') || auth()->user()->isAdmin())
        <div class="{{ $colClass }} mb-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-history text-secondary me-1"></i> Recent Activity</h6>
                    <a href="{{ route('admin.audit.index') }}" class="btn btn-sm btn-outline-primary widget-header-btn">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light"><tr><th>User</th><th>Action</th><th>Time</th></tr></thead>
                        <tbody>
                            @forelse(\App\Models\AuditTrail::with('user')->latest()->take(5)->get() as $log)
                            <tr>
                                <td>{{ $log->user->name ?? 'System' }}</td>
                                <td><span class="badge bg-{{ $log->action=='created'?'success':'info' }}">{{ $log->action }}</span></td>
                                <td><small class="text-muted">{{ $log->created_at->diffForHumans() }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No activity logs recorded.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Fallback for users with no operational module view permissions --}}
    @if(!$hasAnyQuickLink && $widgetCount === 0)
    <div class="alert alert-info border d-flex align-items-center p-4">
        <i class="fas fa-info-circle fa-2x text-primary me-3"></i>
        <div>
            <h5 class="alert-heading mb-1">Standard Portal Access</h5>
            <p class="mb-0 text-muted">You are currently logged in with basic portal access. Additional ERP module views and actions will appear here once assigned by your system administrator.</p>
        </div>
    </div>
    @endif
</div>
@endsection
