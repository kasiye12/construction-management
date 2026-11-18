@extends('layouts.app')

@section('title', $project->name . ' - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📋 {{ $project->name }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($project->name, 50) }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('equipment.index', ['project_id' => $project->id]) }}" class="btn btn-outline-info btn-custom">
                <i class="fas fa-truck-monster me-1"></i>Fleet ({{ $project->equipment->count() }})
            </a>
            <a href="{{ route('contractual-reports.create', ['project_id' => $project->id]) }}" class="btn btn-primary btn-custom">
                <i class="fas fa-file-contract me-1"></i>New CAD-01 Report
            </a>
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-warning btn-custom">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="{{ route('ipcs.create', ['project_id' => $project->id]) }}" class="btn btn-success btn-custom">
                <i class="fas fa-file-invoice me-1"></i>New IPC
            </a>
        </div>
    </div>
</div>

<!-- Project Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-stats">
            <div class="card-body">
                <h6 class="text-muted">Contract Amount</h6>
                <h3>{{ number_format($project->contract_amount, 2) }} ETB</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stats">
            <div class="card-body">
                <h6 class="text-muted">Total Revenue</h6>
                <h3>{{ number_format($totalRevenue, 2) }} ETB</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stats">
            <div class="card-body">
                <h6 class="text-muted">Budget Cost</h6>
                <h3>{{ number_format($totalBudgetCost, 2) }} ETB</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stats">
            <div class="card-body">
                <h6 class="text-muted">Profit/Loss</h6>
                <h3 class="{{ $totalProfitLoss >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($totalProfitLoss, 2) }} ETB
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- Project Details -->
<div class="row">
    <div class="col-md-8">
        <div class="table-card mb-4">
            <h5>Project Information</h5>
            <hr>
            <table class="table table-bordered">
                <tr>
                    <th width="30%">Client</th>
                    <td>{{ $project->client_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Consultant</th>
                    <td><strong class="text-primary">{{ $project->consultant_name ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th>Contractor</th>
                    <td>{{ $project->contractor_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Location / Site</th>
                    <td>{{ $project->location ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @if($project->status == 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($project->status == 'completed')
                            <span class="badge bg-primary">Completed</span>
                        @elseif($project->status == 'on_hold')
                            <span class="badge bg-warning">On Hold</span>
                        @else
                            <span class="badge bg-danger">Cancelled</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Duration</th>
                    <td>
                        {{ $project->start_date ? $project->start_date->format('M d, Y') : 'N/A' }} 
                        to 
                        {{ $project->end_date ? $project->end_date->format('M d, Y') : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td>{{ $project->description ?? 'No description' }}</td>
                </tr>
            </table>
        </div>
        
        <!-- BOQ Items -->
        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>BOQ Items</h5>
                <a href="{{ route('boq-items.create', ['project_id' => $project->id]) }}" class="btn btn-primary btn-sm btn-custom">
                    <i class="fas fa-plus me-1"></i>Add Item
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Item No.</th>
                            <th>Description</th>
                            <th>Unit</th>
                            <th>Quantity</th>
                            <th>Rate</th>
                            <th>Amount</th>
                            <th>P/L</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($project->boqItems->where('is_parent', false) as $item)
                        <tr>
                            <td>{{ $item->item_number }}</td>
                            <td>
                                <a href="{{ route('boq-items.show', $item) }}">{{ Str::limit($item->description, 60) }}</a>
                            </td>
                            <td>{{ $item->unit }}</td>
                            <td>{{ number_format($item->quantity, 2) }}</td>
                            <td>{{ number_format($item->unit_rate, 2) }}</td>
                            <td>{{ number_format($item->revenue_amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $item->profit_loss >= 0 ? 'profit-badge' : 'loss-badge' }}">
                                    {{ number_format($item->profit_loss, 2) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p>No BOQ items found</p>
                                <a href="{{ route('boq-items.create', ['project_id' => $project->id]) }}" class="btn btn-primary btn-sm">
                                    Add First Item
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Subcontractors -->
        <div class="table-card mb-4">
            <h5>👥 Subcontractors</h5>
            <hr>
            @forelse($project->subcontractors as $subcontractor)
            <a href="{{ route('projects.subcontractors', $project) }}" class="btn btn-outline-primary btn-sm ms-2">
                <i class="fas fa-users-cog me-1"></i> Manage Subcontractors
            <a href="{{ route('projects.team', $project) }}" class="btn btn-outline-info btn-sm ms-2">
                <i class="fas fa-users me-1"></i> Project Team
            </a>
            </a>
            <div class="mb-3 p-3 border rounded">
                <strong>{{ $subcontractor->name }}</strong>
                <p class="mb-1 text-muted small">{{ $subcontractor->pivot->scope_of_work ?? 'No scope defined' }}</p>
                <small>Contract: {{ number_format($subcontractor->pivot->contract_amount, 2) }} ETB</small>
            </div>
            @empty
            <p class="text-muted">No subcontractors assigned</p>
            <a href="{{ route('projects.subcontractors', $project) }}" class="btn btn-outline-primary btn-sm ms-2">
                <i class="fas fa-users-cog me-1"></i> Manage Subcontractors
            <a href="{{ route('projects.team', $project) }}" class="btn btn-outline-info btn-sm ms-2">
                <i class="fas fa-users me-1"></i> Project Team
            </a>
            </a>
            @endforelse
        </div>
        
        <!-- Recent IPCs -->
        <div class="table-card mb-4">
            <h5>📄 Recent IPCs</h5>
            <hr>
            @forelse($project->ipcs->take(5) as $ipc)
            <div class="mb-2 p-2 border rounded">
                <a href="{{ route('ipcs.show', $ipc) }}" class="text-decoration-none">
                    <strong>{{ $ipc->ipc_number }}</strong>
                </a>
                <br>
                <small class="text-muted">
                    {{ optional($ipc->ipc_date)->format('M d, Y') ?? 'N/A' }} - 
                    {{ number_format($ipc->net_payment_amount, 2) }} ETB
                    @if($ipc->status)
                        <span class="badge bg-{{ $ipc->status == 'approved' ? 'success' : ($ipc->status == 'paid' ? 'primary' : 'warning') }} ms-1">
                            {{ ucfirst($ipc->status) }}
                        </span>
                    @endif
                </small>
            </div>
            @empty
            <p class="text-muted">No IPCs generated yet</p>
            @endforelse
        </div>

        <!-- Weekly Contractual Reports (CAD-01) -->
        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">📑 Contractual Reports</h5>
                <a href="{{ route('contractual-reports.create', ['project_id' => $project->id]) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus"></i> New
                </a>
            </div>
            <hr>
            @forelse($project->contractualReports->take(5) as $cr)
            <div class="mb-2 p-2 border rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('contractual-reports.show', $cr) }}" class="fw-bold text-decoration-none">
                        {{ $cr->report_number }}
                    </a>
                    {!! $cr->status_badge !!}
                </div>
                <small class="text-muted d-block mt-1">
                    Week: {{ $cr->week_start_date->format('M d') }} - {{ $cr->week_end_date->format('M d, Y') }}
                </small>
            </div>
            @empty
            <p class="text-muted small mb-0">No contractual follow-up reports recorded yet.</p>
            @endforelse
        </div>

        <!-- Deployed Plant & Equipment (EAMD) -->
        <div class="table-card mt-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">🚜 Assigned Equipment</h5>
                <a href="{{ route('equipment.create', ['project_id' => $project->id]) }}" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-plus"></i> Add
                </a>
            </div>
            <hr>
            @forelse($project->equipment->take(5) as $eq)
            <div class="mb-2 p-2 border rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('equipment.show', $eq) }}" class="fw-bold text-decoration-none">
                        {{ $eq->equipment_type }}
                    </a>
                    {!! $eq->service_status_badge !!}
                </div>
                <small class="text-muted d-block mt-1">
                    Plate: <strong>{{ $eq->plate_serial_no }}</strong> • Reading: {{ number_format($eq->current_reading, 0) }} {{ $eq->reading_unit }}
                </small>
            </div>
            @empty
            <p class="text-muted small mb-0">No equipment currently assigned to this project site.</p>
            @endforelse
        </div>
    </div>
</div>
<!-- Documents -->
@include('partials.document-upload', ['model' => $project, 'type' => 'App\\Models\\Project'])
@endsection
