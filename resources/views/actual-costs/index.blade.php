@extends('layouts.app')

@section('title', 'Actual Costs - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>💰 Actual Cost Tracking</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Actual Costs</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('actual-costs.variance') }}?project_id={{ $projectId }}" class="btn btn-info me-2">
                <i class="fas fa-chart-bar me-2"></i>Variance Report
            </a>
            <a href="{{ route('actual-costs.create') }}?project_id={{ $projectId }}" class="btn btn-primary btn-custom">
                <i class="fas fa-plus me-2"></i>Add Cost
            </a>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white" style="border-radius:15px;">
            <div class="card-body p-3 text-center">
                <h6>BUDGET</h6>
                <h3>{{ number_format($totalBudget, 0) }} ETB</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white" style="border-radius:15px;">
            <div class="card-body p-3 text-center">
                <h6>ACTUAL SPENT</h6>
                <h3>{{ number_format($totalActual, 0) }} ETB</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-{{ $variance >= 0 ? 'success' : 'danger' }} text-white" style="border-radius:15px;">
            <div class="card-body p-3 text-center">
                <h6>VARIANCE</h6>
                <h3>{{ number_format($variance, 0) }} ETB</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="filter-bar mb-3">
    <form method="GET" class="row">
        <div class="col-md-4">
            <select name="project_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ $projectId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

<!-- Costs Table -->
<div class="table-card">
    <table class="table table-hover datatable">
        <thead class="table-light">
            <tr>
                <th>Date</th><th>Project</th><th>BOQ Item</th><th>Type</th><th>Description</th><th>Vendor</th><th>Amount</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($costs as $cost)
            <tr>
                <td>{{ $cost->cost_date->format('M d, Y') }}</td>
                <td>{{ $cost->project->name ?? 'N/A' }}</td>
                <td>{{ Str::limit($cost->boqItem->description ?? 'N/A', 30) }}</td>
                <td><span class="badge bg-info">{{ ucfirst($cost->cost_type) }}</span></td>
                <td>{{ $cost->description }}</td>
                <td>{{ $cost->vendor ?? '-' }}</td>
                <td class="text-end fw-bold">{{ number_format($cost->amount, 2) }}</td>
                <td>
                    <a href="{{ route('actual-costs.edit', $cost) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $costs->links() }}
</div>
@endsection
