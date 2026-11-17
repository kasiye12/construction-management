@extends('layouts.app')

@section('title', 'Preventive Maintenance Schedules (OF/EAMD/074)')

@section('content')
<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark fw-bold">
                <i class="fas fa-calendar-check text-primary me-2"></i> Preventive Maintenance Schedules
            </h1>
            <p class="text-muted small mb-0">Document №: <strong class="text-dark">OF/EAMD/074</strong> • TNT Construction & Trading • Equipment Administration & Maintenance Department</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm-schedules.download-template') }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Download Excel Template
            </a>
            @if($canPrepare)
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                    <i class="fas fa-file-upload me-1"></i> Upload Excel
                </button>
                <a href="{{ route('pm-schedules.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> New PM Schedule
                </a>
            @endif
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('pm-schedules.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Project Site / Location</label>
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">All Projects / Central</option>
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
                        <a href="{{ route('pm-schedules.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Schedules Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th>Schedule Code</th>
                        <th>Document No</th>
                        <th>Location / Project</th>
                        <th>Schedule Date / Period</th>
                        <th class="text-center">Fleet Items</th>
                        <th class="text-center">Service Alerts</th>
                        <th class="text-center">Workflow Status</th>
                        <th>Signatures</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $s)
                        <tr>
                            <td>
                                <a href="{{ route('pm-schedules.show', $s) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $s->schedule_code }}
                                </a>
                                <div class="small text-muted">{{ $s->title }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $s->document_code }}</span>
                                <div class="small text-muted">Issue: {{ $s->issue_no }}</div>
                            </td>
                            <td>
                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                {{ $s->project_location ?: ($s->project?->name ?: 'Central & All Sites') }}
                            </td>
                            <td>
                                <div>{{ $s->schedule_date ? $s->schedule_date->format('d/m/Y') : '-' }}</div>
                                @if($s->period_start && $s->period_end)
                                    <small class="text-muted">{{ $s->period_start->format('d/m') }} - {{ $s->period_end->format('d/m/Y') }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-dark fs-6">{{ $s->items->count() }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $overdue = $s->overdue_count;
                                    $due = $s->due_count;
                                @endphp
                                @if($overdue > 0)
                                    <span class="badge bg-danger me-1">{{ $overdue }} Overdue</span>
                                @endif
                                @if($due > 0)
                                    <span class="badge bg-warning text-dark me-1">{{ $due }} Due</span>
                                @endif
                                @if($overdue == 0 && $due == 0)
                                    <span class="badge bg-success">All Normal</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {!! $s->status_badge !!}
                            </td>
                            <td>
                                <div class="small">
                                    @if($s->approved_name)
                                        <span class="text-success"><i class="fas fa-check-double me-1"></i> Approved by {{ $s->approved_name }}</span>
                                    @elseif($s->checked_name)
                                        <span class="text-warning text-dark"><i class="fas fa-user-check me-1"></i> Checked by {{ $s->checked_name }}</span>
                                    @elseif($s->prepared_name)
                                        <span class="text-muted"><i class="fas fa-pencil-alt me-1"></i> Prepared by {{ $s->prepared_name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('pm-schedules.show', $s) }}" class="btn btn-outline-primary" title="View Document">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('pm-schedules.export', $s) }}" class="btn btn-outline-success" title="Download Excel (OF/074)">
                                        <i class="fas fa-file-excel"></i>
                                    </a>
                                    <a href="{{ route('pm-schedules.print', $s) }}" target="_blank" class="btn btn-outline-secondary" title="Print A4 Format">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    @if($s->isDraft() || auth()->user()?->isAdmin())
                                        <a href="{{ route('pm-schedules.edit', $s) }}" class="btn btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-calendar-times fa-3x mb-3 text-secondary"></i>
                                <p class="mb-2">No Preventive Maintenance Schedules found.</p>
                                @if($canPrepare)
                                    <a href="{{ route('pm-schedules.create') }}" class="btn btn-primary btn-sm">Create First PM Schedule</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($schedules->hasPages())
            <div class="card-footer bg-white py-2">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>

    {{-- Import Excel Modal --}}
    @if($canPrepare)
        <div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-start">
                    <form action="{{ route('pm-schedules.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title h6">
                                <i class="fas fa-file-excel text-success me-2"></i> Import Preventive Maintenance Schedule (OF/074)
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted">
                                Upload a filled <strong>FORMAT 1.xlsx</strong> or <strong>OF/EAMD/074</strong> Excel spreadsheet. The system will automatically parse equipment records, calculate service targets, and generate a new PM Schedule.
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
