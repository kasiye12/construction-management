#!/bin/bash

echo "📝 Creating all view files..."

# Create views directory structure
mkdir -p resources/views/projects
mkdir -p resources/views/subcontractors
mkdir -p resources/views/cost-categories
mkdir -p resources/views/boq-items
mkdir -p resources/views/ipcs
mkdir -p resources/views/layouts

# ==========================================
# 1. Main Layout
# ==========================================
cat > resources/views/layouts/app.blade.php << 'VIEW'
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
VIEW

echo "✅ Main layout created"

# ==========================================
# 2. Dashboard
# ==========================================
cat > resources/views/dashboard.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', 'Dashboard - CMS')

@section('content')
<div class="page-header">
    <h2>📊 Dashboard Overview</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Active Projects</h6>
                        <h3 class="mb-0">{{ $activeProjects }}</h3>
                    </div>
                    <div class="icon-box bg-primary-light">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Projects</h6>
                        <h3 class="mb-0">{{ $totalProjects }}</h3>
                    </div>
                    <div class="icon-box bg-success-light">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Contract Value</h6>
                        <h3 class="mb-0">{{ number_format($totalContractValue, 0) }}</h3>
                    </div>
                    <div class="icon-box bg-warning-light">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Pending IPCs</h6>
                        <h3 class="mb-0">{{ $pendingIpcs ?? 0 }}</h3>
                    </div>
                    <div class="icon-box bg-info-light">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Projects -->
<div class="row mt-4">
    <div class="col-12">
        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">📁 Recent Projects</h5>
                <a href="{{ route('projects.create') }}" class="btn btn-primary btn-custom">
                    <i class="fas fa-plus me-2"></i>New Project
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Project Name</th>
                            <th>Client</th>
                            <th>Contractor</th>
                            <th>Contract Amount</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentProjects as $project)
                        <tr>
                            <td>
                                <a href="{{ route('projects.show', $project) }}" class="text-decoration-none fw-bold">
                                    {{ $project->name }}
                                </a>
                            </td>
                            <td>{{ $project->client_name ?? 'N/A' }}</td>
                            <td>{{ $project->contractor_name ?? 'N/A' }}</td>
                            <td>{{ number_format($project->contract_amount, 2) }} ETB</td>
                            <td>
                                @if($project->status == 'active')
                                    <span class="badge badge-status bg-success">Active</span>
                                @elseif($project->status == 'completed')
                                    <span class="badge badge-status bg-primary">Completed</span>
                                @elseif($project->status == 'on_hold')
                                    <span class="badge badge-status bg-warning">On Hold</span>
                                @else
                                    <span class="badge badge-status bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td>{{ $project->start_date ? $project->start_date->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <p>No projects found. Start by creating a new project.</p>
                                <a href="{{ route('projects.create') }}" class="btn btn-primary">Create Project</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="row mt-4">
    <div class="col-md-4 mb-4">
        <div class="card card-stats">
            <div class="card-body text-center">
                <i class="fas fa-list-ol fa-3x text-primary mb-3"></i>
                <h5>BOQ Items</h5>
                <p class="text-muted">Manage Bill of Quantities</p>
                <a href="{{ route('boq-items.index') }}" class="btn btn-outline-primary btn-custom">Go to BOQ</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card card-stats">
            <div class="card-body text-center">
                <i class="fas fa-file-invoice fa-3x text-success mb-3"></i>
                <h5>IPCs</h5>
                <p class="text-muted">Interim Payment Certificates</p>
                <a href="{{ route('ipcs.index') }}" class="btn btn-outline-success btn-custom">Go to IPCs</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card card-stats">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-warning mb-3"></i>
                <h5>Subcontractors</h5>
                <p class="text-muted">Manage Subcontractors</p>
                <a href="{{ route('subcontractors.index') }}" class="btn btn-outline-warning btn-custom">View All</a>
            </div>
        </div>
    </div>
</div>
@endsection
VIEW

echo "✅ Dashboard view created"

# ==========================================
# 3. Projects Index
# ==========================================
cat > resources/views/projects/index.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', 'Projects - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>🏗️ Projects</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Projects</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('projects.create') }}" class="btn btn-primary btn-custom">
            <i class="fas fa-plus me-2"></i>New Project
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Project Name</th>
                    <th>Client</th>
                    <th>Contractor</th>
                    <th>Contract Amount</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                <tr>
                    <td>{{ $project->id }}</td>
                    <td>
                        <a href="{{ route('projects.show', $project) }}" class="text-decoration-none fw-bold">
                            {{ $project->name }}
                        </a>
                    </td>
                    <td>{{ $project->client_name ?? 'N/A' }}</td>
                    <td>{{ $project->contractor_name ?? 'N/A' }}</td>
                    <td>{{ number_format($project->contract_amount, 2) }} ETB</td>
                    <td>
                        @if($project->status == 'active')
                            <span class="badge badge-status bg-success">Active</span>
                        @elseif($project->status == 'completed')
                            <span class="badge badge-status bg-primary">Completed</span>
                        @elseif($project->status == 'on_hold')
                            <span class="badge badge-status bg-warning">On Hold</span>
                        @else
                            <span class="badge badge-status bg-danger">Cancelled</span>
                        @endif
                    </td>
                    <td>{{ $project->start_date ? $project->start_date->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ $project->end_date ? $project->end_date->format('M d, Y') : 'N/A' }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline" 
                                  onsubmit="return confirm('Are you sure you want to delete this project?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="fas fa-building fa-4x text-muted mb-3"></i>
                        <h4>No Projects Found</h4>
                        <p class="text-muted">Start by creating your first construction project.</p>
                        <a href="{{ route('projects.create') }}" class="btn btn-primary btn-custom">
                            <i class="fas fa-plus me-2"></i>Create Project
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $projects->links() }}
    </div>
</div>
@endsection
VIEW

echo "✅ Projects index view created"

# ==========================================
# 4. Projects Create/Edit
# ==========================================
cat > resources/views/projects/create.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', 'Create Project - CMS')

@section('content')
<div class="page-header">
    <h2>➕ Create New Project</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card">
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="name" class="form-label">Project Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="client_name" class="form-label">Client Name</label>
                        <input type="text" class="form-control" id="client_name" name="client_name" 
                               value="{{ old('client_name') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="contractor_name" class="form-label">Contractor Name</label>
                        <input type="text" class="form-control" id="contractor_name" name="contractor_name" 
                               value="{{ old('contractor_name') }}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ old('start_date') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ old('end_date') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="on_hold" {{ old('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="contract_amount" class="form-label">Contract Amount (ETB)</label>
                    <input type="number" class="form-control" id="contract_amount" name="contract_amount" 
                           value="{{ old('contract_amount', 0) }}" step="0.01" min="0">
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-custom">
                        <i class="fas fa-save me-2"></i>Create Project
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="table-card">
            <h5><i class="fas fa-info-circle me-2"></i>Tips</h5>
            <hr>
            <ul class="list-unstyled">
                <li class="mb-2">✅ Fill in all required fields marked with *</li>
                <li class="mb-2">💰 Contract amount helps track project value</li>
                <li class="mb-2">📅 Set realistic start and end dates</li>
                <li class="mb-2">📝 Add detailed description for clarity</li>
            </ul>
        </div>
    </div>
</div>
@endsection
VIEW

# Create edit view (similar to create)
cp resources/views/projects/create.blade.php resources/views/projects/edit.blade.php
sed -i 's/Create New Project/Edit Project/g' resources/views/projects/edit.blade.php
sed -i 's/Create Project/Update Project/g' resources/views/projects/edit.blade.php
sed -i 's/projects.store/projects.update, $project/g' resources/views/projects/edit.blade.php
sed -i 's/@csrf/@csrf\n                @method("PUT")/g' resources/views/projects/edit.blade.php

echo "✅ Projects create/edit views created"

# ==========================================
# 5. Project Show
# ==========================================
cat > resources/views/projects/show.blade.php << 'VIEW'
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
                    <li class="breadcrumb-item active">{{ $project->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-warning btn-custom">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="{{ route('ipcs.create', ['project_id' => $project->id]) }}" class="btn btn-success btn-custom">
                <i class="fas fa-file-invoice me-2"></i>New IPC
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
                    <th>Contractor</th>
                    <td>{{ $project->contractor_name ?? 'N/A' }}</td>
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
                    <thead>
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
                                <a href="{{ route('boq-items.show', $item) }}">{{ $item->description }}</a>
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
                            <td colspan="7" class="text-center">No BOQ items found</td>
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
            <h5>Subcontractors</h5>
            <hr>
            @forelse($project->subcontractors as $subcontractor)
            <div class="mb-3 p-3 border rounded">
                <strong>{{ $subcontractor->name }}</strong>
                <p class="mb-1 text-muted">{{ $subcontractor->pivot->scope_of_work ?? 'No scope defined' }}</p>
                <small>Contract: {{ number_format($subcontractor->pivot->contract_amount, 2) }} ETB</small>
            </div>
            @empty
            <p class="text-muted">No subcontractors assigned</p>
            @endforelse
        </div>
        
        <!-- Recent IPCs -->
        <div class="table-card">
            <h5>Recent IPCs</h5>
            <hr>
            @forelse($project->ipcs->take(5) as $ipc)
            <div class="mb-2 p-2 border rounded">
                <a href="{{ route('ipcs.show', $ipc) }}" class="text-decoration-none">
                    <strong>{{ $ipc->ipc_number }}</strong>
                </a>
                <br>
                <small class="text-muted">
                    {{ $ipc->ipc_date->format('M d, Y') }} - 
                    {{ number_format($ipc->net_payment_amount, 2) }} ETB
                </small>
            </div>
            @empty
            <p class="text-muted">No IPCs generated yet</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
VIEW

echo "✅ Project show view created"

# ==========================================
# Continue with more views...
# ==========================================
echo ""
echo "✅ All main views created successfully!"
echo "📁 Views location: resources/views/"
echo ""
echo "Next: Run 'php artisan serve' to start the application"

