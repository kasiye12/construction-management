@extends('layouts.app')

@section('title', 'Weekly Contractual Follow-up Reports (OF/TNT/CAD/01) - CMS')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h2>📑 Weekly Contractual Follow-up Reports</h2>
            <p class="text-muted mb-0">Contract Administration Form: <strong>OF/TNT/CAD/01</strong> (Causes of Claim, Detailed Events & Site Correspondence)</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Contractual Reports</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('contractual-reports.download-template') }}" class="btn btn-outline-success">
                <i class="fas fa-file-excel me-1"></i> Blank Excel Template
            </a>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                <i class="fas fa-file-import me-1"></i> Import from Excel
            </button>
            <a href="{{ route('contractual-reports.create', ['project_id' => $projectId]) }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Weekly Report
            </a>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-stats h-100 shadow-sm border-0 border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Total Reports</div>
                        <h3 class="mb-0 mt-1 text-primary">{{ $totalReports }}</h3>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                        <i class="fas fa-file-signature fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stats h-100 shadow-sm border-0 border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Approved</div>
                        <h3 class="mb-0 mt-1 text-success">{{ $approvedCount }}</h3>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stats h-100 shadow-sm border-0 border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Under Review</div>
                        <h3 class="mb-0 mt-1 text-warning">{{ $pendingCount }}</h3>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                        <i class="fas fa-hourglass-half fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stats h-100 shadow-sm border-0 border-start border-secondary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Drafts</div>
                        <h3 class="mb-0 mt-1 text-secondary">{{ $draftCount }}</h3>
                    </div>
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 text-secondary">
                        <i class="fas fa-pencil-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('contractual-reports.index') }}" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Project</label>
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ ($projectId ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="checked" {{ request('status') == 'checked' ? 'selected' : '' }}>Checked</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" onchange="this.form.submit()">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" onchange="this.form.submit()">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search number, location...">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    @if(request()->hasAny(['project_id', 'status', 'date_from', 'date_to', 'search']))
                        <a href="{{ route('contractual-reports.index') }}" class="btn btn-outline-danger" title="Clear filters"><i class="fas fa-times"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Report #</th>
                    <th>Project & Location</th>
                    <th>Reporting Week</th>
                    <th class="text-center">Claim Flags</th>
                    <th class="text-center">Claim Events</th>
                    <th class="text-center">Correspondence</th>
                    <th>Sign-offs</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $r)
                <tr>
                    <td>
                        <div class="fw-bold text-primary">{{ $r->report_number }}</div>
                        <span class="badge bg-light text-dark border">{{ $r->document_code }} (Iss {{ $r->issue_no }})</span>
                    </td>
                    <td>
                        <div class="fw-bold">{{ Str::limit($r->project?->name ?? 'Unknown / Archived Project', 35) }}</div>
                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $r->project_location ?: ($r->project?->location ?: 'Site') }}</small>
                    </td>
                    <td>
                        <div class="fw-semibold">
                            {{ $r->week_start_date->format('M d') }} - {{ $r->week_end_date->format('M d, Y') }}
                        </div>
                        <small class="text-muted">7 days</small>
                    </td>
                    <td class="text-center">
                        @php $activeChecks = $r->active_claims_count; @endphp
                        @if($activeChecks > 0)
                            <span class="badge bg-danger rounded-pill" title="{{ $activeChecks }} claim categories flagged">
                                <i class="fas fa-exclamation-triangle me-1"></i>{{ $activeChecks }} items
                            </span>
                        @else
                            <span class="badge bg-success bg-opacity-75 rounded-pill">None</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @php $eventsCount = $r->claimEvents->count(); @endphp
                        @if($eventsCount > 0)
                            <span class="badge bg-warning text-dark rounded-pill">
                                <i class="fas fa-list-alt me-1"></i>{{ $eventsCount }} events
                            </span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @php 
                            $correspCount = $r->active_correspondences_count;
                            $docCount = $r->documents->count();
                        @endphp
                        <div>
                            @if($correspCount > 0)
                                <span class="badge bg-info text-dark rounded-pill">{{ $correspCount }} active</span>
                            @else
                                <span class="text-muted small">None</span>
                            @endif
                        </div>
                        @if($docCount > 0)
                            <small class="text-primary"><i class="fas fa-paperclip me-1"></i>{{ $docCount }} attached</small>
                        @endif
                    </td>
                    <td>
                        <div class="small">
                            <div><span class="text-muted">Prep:</span> {{ $r->prepared_name ?: ($r->preparedByUser?->name ?: 'N/A') }}</div>
                            @if($r->checked_name || $r->checkedByUser)
                                <div><span class="text-muted">Chk:</span> {{ $r->checked_name ?: $r->checkedByUser?->name }}</div>
                            @endif
                            @if($r->approved_name || $r->approvedByUser)
                                <div><span class="text-muted">App:</span> {{ $r->approved_name ?: $r->approvedByUser?->name }}</div>
                            @endif
                        </div>
                    </td>
                    <td>
                        {!! $r->status_badge !!}
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('contractual-reports.show', $r) }}" class="btn btn-outline-primary" title="View Report">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('contractual-reports.excel', $r) }}" class="btn btn-outline-success" title="Export to Official Excel">
                                <i class="fas fa-file-excel"></i>
                            </a>
                            <a href="{{ route('contractual-reports.print', $r) }}" target="_blank" class="btn btn-outline-secondary" title="Print 3-Page Format">
                                <i class="fas fa-print"></i>
                            </a>
                            @php
                                $canEdit = $r->isDraft() || $r->isRejected() || auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->role === 'Contract';
                                $canDelete = auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->role === 'Contract' || auth()->user()->hasPermission('contractual-reports.delete') || $r->prepared_by === auth()->id();
                            @endphp
                            @if($canEdit)
                                <a href="{{ route('contractual-reports.edit', $r) }}" class="btn btn-outline-warning" title="Edit Report">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if($canDelete)
                                <form action="{{ route('contractual-reports.destroy', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove report {{ $r->report_number }} from the list?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Remove / Delete Report">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="fas fa-file-contract fa-3x mb-3 text-secondary opacity-50"></i>
                        <p class="mb-2">No weekly contractual reports found.</p>
                        <a href="{{ route('contractual-reports.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Create First Weekly Report
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $reports->links() }}
        </div>
    @endif
</div>

<!-- Modal: Import from Excel -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('contractual-reports.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelModalLabel"><i class="fas fa-file-excel text-success me-2"></i> Import Contractual Report from Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Upload an official <strong>OF/TNT/CAD/01</strong> Excel workbook (Page 01, Page 02, Page 03). The system will automatically parse the dates, claim checklist, claim events, and site correspondence.</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Project <span class="text-danger">*</span></label>
                        <select name="project_id" class="form-select" required>
                            <option value="">-- Choose Project --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ ($projectId ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Excel File (.xlsx) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        <div class="form-text">Supported format: Microsoft Excel (.xlsx) up to 10MB</div>
                    </div>

                    <div class="alert alert-info py-2 small mb-0">
                        <i class="fas fa-info-circle me-1"></i> You can download the blank official template using the button at the top of the page.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-upload me-1"></i> Import Now</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
