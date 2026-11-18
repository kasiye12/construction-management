@extends('layouts.app')

@section('title', 'Projects - CMS')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2>🏗️ Projects</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Projects</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <!-- Download Template Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="templateDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-download me-1"></i> Templates
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="templateDropdown">
                    <li><h6 class="dropdown-header">Project Import Templates</h6></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('projects.template.download', ['format' => 'xlsx']) }}">
                            <i class="fas fa-file-excel text-success me-2"></i> Excel Template (<code>.xlsx</code>)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('projects.template.download', ['format' => 'csv']) }}">
                            <i class="fas fa-file-csv text-primary me-2"></i> CSV Template (<code>.csv</code>)
                        </a>
                    </li>
                </ul>
            </div>

            @if(\App\Helpers\PermissionHelper::canCreate('projects'))
            <!-- Import Button -->
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importProjectModal">
                <i class="fas fa-file-import me-1"></i> Import Projects
            </button>
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Project
            </a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 70px;">Code</th>
                    <th>Project Name</th>
                    <th>Location / Site</th>
                    <th>Client</th>
                    <th>Consultant</th>
                    <th class="text-center">TNT Share</th>
                    <th class="text-end">Contract Amount</th>
                    <th class="text-center">Status</th>
                    <th class="text-end" style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                <tr>
                    <td>
                        <span class="badge bg-light text-secondary border">
                            {{ $project->project_code ?? 'PRJ-' . str_pad($project->id, 3, '0', STR_PAD_LEFT) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('projects.show', $project) }}" class="fw-bold text-decoration-none text-dark">
                            {{ $project->name }}
                        </a>
                        @if($project->contract_signing_date)
                        <div class="small text-muted">
                            <i class="far fa-calendar-alt me-1"></i> Signed: {{ $project->contract_signing_date->format('M d, Y') }}
                        </div>
                        @endif
                    </td>
                    <td>
                        @if($project->location)
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $project->location }}
                        </span>
                        @else
                        <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                    <td>
                        <span title="{{ $project->client_name }}">
                            {{ Str::limit($project->client_name ?? 'N/A', 30) }}
                        </span>
                    </td>
                    <td>
                        <span class="small text-muted" title="{{ $project->consultant_name }}">
                            {{ Str::limit($project->consultant_name ?? 'N/A', 28) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">
                            {{ $project->formatted_tnt_share }}
                        </span>
                    </td>
                    <td class="text-end fw-bold">
                        {{ number_format($project->contract_amount, 2) }} <small class="text-muted fw-normal">ETB</small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $project->status=='active' ? 'success' : ($project->status=='completed' ? 'info' : 'secondary') }}">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            @if(\App\Helpers\PermissionHelper::canView('projects'))
                            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-info" title="View Project"><i class="fas fa-eye"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canEdit('projects'))
                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-warning" title="Edit Project"><i class="fas fa-edit"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canDelete('projects'))
                            <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this project?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger" title="Delete Project"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="fas fa-folder-open fa-3x mb-3 text-secondary"></i>
                        <h6>No projects found in the master registry.</h6>
                        <p class="small">Click <strong>Import Projects</strong> to upload projects from Excel/CSV, or click <strong>New Project</strong> to create one.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white border-top">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 flex-wrap gap-2">
            <div class="pagination-info small text-muted">
                Showing {{ $projects->firstItem() ?? 0 }} - {{ $projects->lastItem() ?? 0 }} of {{ $projects->total() }} projects
            </div>
            {{ $projects->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>

<!-- Modal for Quick Import -->
<div class="modal fade" id="importProjectModal" tabindex="-1" aria-labelledby="importProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('projects.import.post') }}" method="POST" enctype="multipart/form-data" id="modalImportForm">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="importProjectModalLabel">
                        <i class="fas fa-file-import me-2"></i> Import Projects Master Registry
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Upload an Excel spreadsheet (<code>.xlsx</code>, <code>.xls</code>) or CSV file. The importer automatically detects columns like <strong>Project Name</strong>, <strong>Contract Amount</strong>, <strong>TNT Share (%)</strong>, <strong>Signing Date</strong>, <strong>Client</strong>, <strong>Consultant</strong>, and <strong>Location</strong>.
                    </p>

                    <div class="mb-4">
                        <label for="modalFile" class="form-label fw-bold">Select File <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="modalFile" class="form-control" accept=".xlsx,.xls,.csv,.txt" required>
                        <div class="form-text">Maximum size: 30MB. Allowed: <code>.xlsx</code>, <code>.xls</code>, <code>.csv</code></div>
                    </div>

                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body py-2 px-3">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="update_existing" id="modalUpdateExisting" value="1" checked>
                                <label class="form-check-label fw-semibold" for="modalUpdateExisting">
                                    Update existing projects if project name matches
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('projects.template.download', ['format' => 'xlsx']) }}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-file-excel me-1"></i> Template (.xlsx)
                            </a>
                            <a href="{{ route('projects.template.download', ['format' => 'csv']) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-file-csv me-1"></i> Template (.csv)
                            </a>
                        </div>
                        <a href="{{ route('projects.import') }}" class="small text-decoration-none">
                            <i class="fas fa-external-link-alt me-1"></i> Open Advanced Import Page
                        </a>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4" id="modalSubmitBtn">
                        <i class="fas fa-upload me-1"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalForm = document.getElementById('modalImportForm');
    const modalBtn = document.getElementById('modalSubmitBtn');
    if (modalForm && modalBtn) {
        modalForm.addEventListener('submit', function() {
            modalBtn.disabled = true;
            modalBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Importing...';
        });
    }
});
</script>
@endsection
