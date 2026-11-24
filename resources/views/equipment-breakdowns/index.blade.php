@extends('layouts.app')

@section('title', 'Equipment Breakdown & Maintenance Reports (OF/030)')

@section('content')
<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark fw-bold">
                <i class="fas fa-tools text-danger me-2"></i> Equipment Breakdown & Maintenance Reports
            </h1>
            <p class="text-muted small mb-0">Document №: <strong class="text-dark">OF/TNT/ EAMD/030</strong> • TNT Construction & Trading • Equipment Administration & Maintenance Department</p>
        </div>
        <div class="d-flex gap-2">
            @if($canPrepare)
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                    <i class="fas fa-file-upload me-1"></i> Upload Excel (OF/030)
                </button>
                <a href="{{ route('equipment-breakdowns.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> New Breakdown Report
                </a>
            @endif
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('equipment-breakdowns.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Project Site / Location</label>
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">All Projects / Sites</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Workflow Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted (Pending Check)</option>
                        <option value="checked" {{ request('status') == 'checked' ? 'selected' : '' }}>Checked (Pending Approval)</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['project_id', 'status']))
                        <a href="{{ route('equipment-breakdowns.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Reports Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th>Report Code</th>
                        <th>Document No</th>
                        <th>Report Date</th>
                        <th>Location / Project</th>
                        <th class="text-center">Total Breakdowns</th>
                        <th class="text-center">Repair Breakdown Status</th>
                        <th class="text-center">Workflow Status</th>
                        <th>Signatures</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $r)
                        <tr>
                            <td>
                                <a href="{{ route('equipment-breakdowns.show', $r) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $r->report_code }}
                                </a>
                                <div class="small text-muted">{{ $r->title }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $r->document_code }}</span>
                                <div class="small text-muted">Issue: {{ $r->issue_no }}</div>
                            </td>
                            <td>{{ $r->report_date ? $r->report_date->format('d/m/Y') : '-' }}</td>
                            <td>
                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                {{ $r->location_name ?: ($r->project?->name ?: 'Central & Project Sites') }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-dark fs-6">{{ $r->items->count() }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $down = $r->down_count;
                                    $under = $r->under_maintenance_count;
                                    $rep = $r->repaired_count;
                                @endphp
                                @if($down > 0)
                                    <span class="badge bg-danger me-1">{{ $down }} Down</span>
                                @endif
                                @if($under > 0)
                                    <span class="badge bg-info text-dark me-1">{{ $under }} In Progress</span>
                                @endif
                                @if($rep > 0)
                                    <span class="badge bg-success">{{ $rep }} Repaired</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {!! $r->status_badge !!}
                            </td>
                            <td>
                                <div class="small">
                                    @if($r->approved_name)
                                        <span class="text-success"><i class="fas fa-check-double me-1"></i> {{ $r->approved_name }}</span>
                                    @elseif($r->checked_name)
                                        <span class="text-warning text-dark"><i class="fas fa-user-check me-1"></i> {{ $r->checked_name }}</span>
                                    @elseif($r->prepared_name)
                                        <span class="text-muted"><i class="fas fa-pencil-alt me-1"></i> {{ $r->prepared_name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('equipment-breakdowns.show', $r) }}" class="btn btn-outline-primary" title="View Report">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('equipment-breakdowns.export', $r) }}" class="btn btn-outline-success" title="Download Excel (OF/030)">
                                        <i class="fas fa-file-excel"></i>
                                    </a>
                                    <a href="{{ route('equipment-breakdowns.print', $r) }}" target="_blank" class="btn btn-outline-secondary" title="Print Report">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    @if($r->isDraft() || auth()->user()?->isAdmin())
                                        <a href="{{ route('equipment-breakdowns.edit', $r) }}" class="btn btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    @php
                                        $isDraftOrRejected = $r->isDraft() || $r->isRejected();
                                        $canDeleteBreakdown = $isDraftOrRejected && (auth()->user()?->isAdmin() || auth()->user()?->is_super_admin || auth()->user()?->hasPermission('equipment-breakdowns.delete'));
                                    @endphp
                                    @if($canDeleteBreakdown)
                                        <form action="{{ route('equipment-breakdowns.destroy', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove breakdown report {{ $r->report_code }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete Draft Report">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                <p class="mb-2">No Equipment Breakdown Reports found.</p>
                                @if($canPrepare)
                                    <a href="{{ route('equipment-breakdowns.create') }}" class="btn btn-primary btn-sm">Create First Breakdown Report</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
            <div class="card-footer bg-white py-2">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

    {{-- Import Excel Modal --}}
    @if($canPrepare)
        <div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-start">
                    <form action="{{ route('equipment-breakdowns.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title h6">
                                <i class="fas fa-file-excel text-success me-2"></i> Import Maintenance Report (OF/030)
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted">
                                Upload a filled <strong>OF/TNT/ EAMD/030</strong> or <strong>FORMAT 1.xlsx</strong> (Sheet: MAINTENANCE REPORT). The system will automatically import the breakdown list and repair statuses.
                            </p>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Select Excel File (.xlsx, .xls)</label>
                                <input type="file" name="excel_file" class="form-control form-control-sm" accept=".xlsx,.xls" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Assign to Project (Optional)</label>
                                <select name="project_id" class="form-select form-select-sm">
                                    <option value="">Central Workshop / All Sites</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-upload me-1"></i> Start Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
