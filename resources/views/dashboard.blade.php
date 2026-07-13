@extends('layouts.app')

@section('title', 'Dashboard - Construction Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">📊 Construction Management Dashboard</h2>
            <p class="text-muted">Welcome back! Here's your project overview.</p>
        </div>
        <a href="{{ route('projects.create') }}" class="btn btn-primary btn-lg">
            <i class="fas fa-plus me-2"></i>New Project
        </a>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-primary text-white" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">ACTIVE PROJECTS</h6>
                            <h2 class="mb-0">{{ $activeProjects }}</h2>
                            <small>of {{ $totalProjects }} total</small>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded">
                            <i class="fas fa-hard-hat fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-success text-white" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">TOTAL REVENUE</h6>
                            <h2 class="mb-0">{{ number_format($totalRevenue, 0) }} ETB</h2>
                            <small>All projects</small>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-warning text-white" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">PROFIT/LOSS</h6>
                            <h2 class="mb-0">{{ number_format($profitLoss, 0) }} ETB</h2>
                            <small>{{ number_format($profitMargin, 1) }}% margin</small>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-info text-white" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">PENDING IPCs</h6>
                            <h2 class="mb-0">{{ $pendingIpcs }}</h2>
                            <small>{{ $approvedIpcs }} approved</small>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded">
                            <i class="fas fa-file-invoice fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Projects & IPCs -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card" style="border-radius: 15px; box-shadow: 0 2px 15px rgba(0,0,0,0.08);">
                <div class="card-header bg-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">📁 Recent Projects</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProjects as $project)
                                <tr>
                                    <td>
                                        <a href="{{ route('projects.show', $project) }}" class="text-decoration-none">
                                            {{ Str::limit($project->name, 40) }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $project->status == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($project->status) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($project->contract_amount, 0) }} ETB</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No projects yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card" style="border-radius: 15px; box-shadow: 0 2px 15px rgba(0,0,0,0.08);">
                <div class="card-header bg-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">📄 Recent IPCs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>IPC No.</th>
                                    <th>Project</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentIpcs as $ipc)
                                <tr>
                                    <td>
                                        <a href="{{ route('ipcs.show', $ipc) }}" class="text-decoration-none">
                                            {{ $ipc->ipc_number }}
                                        </a>
                                    </td>
                                    <td>{{ Str::limit($ipc->project->name ?? 'N/A', 30) }}</td>
                                    <td>{{ number_format($ipc->net_payment_amount, 0) }} ETB</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No IPCs yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-2">
        <div class="col-12">
            <div class="card" style="border-radius: 15px; box-shadow: 0 2px 15px rgba(0,0,0,0.08);">
                <div class="card-body">
                    <h5 class="mb-3">⚡ Quick Actions</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('projects.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> New Project
                        </a>
                        <a href="{{ route('boq-items.create') }}" class="btn btn-success">
                            <i class="fas fa-list me-1"></i> Add BOQ Item
                        </a>
                        <a href="{{ route('ipcs.create') }}" class="btn btn-info text-white">
                            <i class="fas fa-file-invoice me-1"></i> Create IPC
                        </a>
                        <a href="{{ route('subcontractors.create') }}" class="btn btn-warning">
                            <i class="fas fa-user-plus me-1"></i> Add Subcontractor
                        </a>
                        <a href="{{ route('cost-categories.create') }}" class="btn btn-secondary">
                            <i class="fas fa-sitemap me-1"></i> Add Category
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
