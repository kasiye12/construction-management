@extends('layouts.app')

@section('title', 'Actual Costs - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2>💰 Actual Cost Tracking</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Actual Costs</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('actual-costs.variance') }}?project_id={{ $projectId }}" class="btn btn-outline-info">
                <i class="fas fa-chart-bar me-1"></i> Variance Report
            </a>
            <a href="{{ route('actual-costs.create') }}?project_id={{ $projectId }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Cost
            </a>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center py-3">
                <h6 class="text-white-50">TOTAL BUDGET</h6>
                <h3>{{ number_format($totalBudget, 0) }}</h3>
                <small>ETB</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center py-3">
                <h6 class="text-white-50">ACTUAL SPENT</h6>
                <h3>{{ number_format($totalActual, 0) }}</h3>
                <small>{{ $percentUsed }}% of budget</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-{{ $variance >= 0 ? 'success' : 'danger' }} text-white">
            <div class="card-body text-center py-3">
                <h6 class="text-white-50">VARIANCE</h6>
                <h3>{{ number_format($variance, 0) }}</h3>
                <small>{{ $variance >= 0 ? 'Under Budget' : 'Over Budget' }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center py-3">
                <h6 class="text-white-50">TOTAL RECORDS</h6>
                <h3>{{ $costs->total() }}</h3>
                <small>cost entries</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4">
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ $projectId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($projectId)
            <div class="col-md-2">
                <a href="{{ route('actual-costs.index') }}" class="btn btn-sm btn-outline-secondary">Clear Filter</a>
            </div>
            @endif
        </form>
    </div>
</div>

<!-- Costs Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Project</th>
                    <th>BOQ Item</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Vendor</th>
                    <th class="text-end">Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($costs as $cost)
                <tr>
                    <td>{{ $cost->cost_date->format('M d, Y') }}</td>
                    <td>{{ Str::limit($cost->project->name ?? 'N/A', 25) }}</td>
                    <td>{{ Str::limit($cost->boqItem->description ?? 'N/A', 30) }}</td>
                    <td>
                        <span class="badge bg-{{ $cost->cost_type == 'labor' ? 'primary' : ($cost->cost_type == 'material' ? 'warning' : ($cost->cost_type == 'equipment' ? 'info' : 'secondary')) }}">
                            {{ ucfirst($cost->cost_type) }}
                        </span>
                    </td>
                    <td>{{ Str::limit($cost->description, 40) }}</td>
                    <td>{{ $cost->vendor ?? '-' }}</td>
                    <td class="text-end fw-bold">{{ number_format($cost->amount, 2) }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('actual-costs.edit', $cost) }}" class="btn btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('actual-costs.destroy', $cost) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this cost record?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <h5>No Actual Costs Found</h5>
                        <p class="text-muted">Select a project and start recording actual costs.</p>
                        <a href="{{ route('actual-costs.create') }}" class="btn btn-primary">Add Cost</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $costs->links() }}
    </div>
</div>
@endsection
