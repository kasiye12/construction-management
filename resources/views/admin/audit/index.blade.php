@extends('layouts.app')

@section('title', 'System Audit Trail - CMS Pro')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h2 class="mb-1"><i class="fas fa-history text-primary me-2"></i>System Audit Trail</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">System Administration</li>
                    <li class="breadcrumb-item active">Audit Trail</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.audit.export', request()->query()) }}" class="btn btn-success shadow-sm">
                <i class="fas fa-file-csv me-1"></i> Export Filtered Logs (CSV)
            </a>
            @if(request()->hasAny(['search', 'user_id', 'module', 'action', 'date_from', 'date_to']))
                <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-danger shadow-sm">
                    <i class="fas fa-times-circle me-1"></i> Clear Filters
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-stats h-100 shadow-sm border-0 border-start border-primary border-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Audit Records</div>
                        <h3 class="mb-0 mt-1 text-primary">{{ number_format($filteredLogs) }}</h3>
                        <small class="text-muted">Total: {{ number_format($totalLogs) }}</small>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                        <i class="fas fa-list-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stats h-100 shadow-sm border-0 border-start border-success border-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Creates</div>
                        <h3 class="mb-0 mt-1 text-success">{{ number_format($createdCount) }}</h3>
                        <small class="text-success"><i class="fas fa-plus me-1"></i>New entries logged</small>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                        <i class="fas fa-plus-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stats h-100 shadow-sm border-0 border-start border-warning border-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Updates</div>
                        <h3 class="mb-0 mt-1 text-warning">{{ number_format($updatedCount) }}</h3>
                        <small class="text-warning"><i class="fas fa-pen me-1"></i>Field modifications</small>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                        <i class="fas fa-edit fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stats h-100 shadow-sm border-0 border-start border-danger border-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Deletions</div>
                        <h3 class="mb-0 mt-1 text-danger">{{ number_format($deletedCount) }}</h3>
                        <small class="text-danger"><i class="fas fa-trash-alt me-1"></i>Removed items</small>
                    </div>
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger">
                        <i class="fas fa-trash-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-bold"><i class="fas fa-filter text-primary me-2"></i>Filter Audit Records</h5>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <span class="text-muted small">Quick Dates:</span>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="setQuickDate('today')">Today</button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="setQuickDate('week')">Last 7 Days</button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="setQuickDate('month')">This Month</button>
            </div>
        </div>
    </div>
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.audit.index') }}" id="auditFilterForm" class="row g-2 align-items-end">
            <!-- Search Text -->
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1 fw-bold">Search Keywords</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search field, value, ID, IP, user...">
                </div>
            </div>

            <!-- Module Filter -->
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1 fw-bold">Module / Entity</label>
                <select name="module" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Modules</option>
                    @foreach($distinctModules as $mod)
                        <option value="{{ $mod['class'] }}" {{ request('module') == $mod['class'] ? 'selected' : '' }}>
                            {{ $mod['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- User Filter -->
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1 fw-bold">User</label>
                <select name="user_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->role ?: 'User' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Filter -->
            <div class="col-md-1">
                <label class="form-label small text-muted mb-1 fw-bold">Action</label>
                <select name="action" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>

            <!-- Date From -->
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1 fw-bold">Date From</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" onchange="this.form.submit()">
            </div>

            <!-- Date To -->
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1 fw-bold">Date To</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" onchange="this.form.submit()">
                    <button type="submit" class="btn btn-primary" title="Apply Filters"><i class="fas fa-filter"></i></button>
                    @if(request()->hasAny(['search', 'user_id', 'module', 'action', 'date_from', 'date_to']))
                        <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-danger" title="Clear Filters"><i class="fas fa-times"></i></a>
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
                    <th style="width: 140px;">Date & Time</th>
                    <th>User</th>
                    <th>Module & Entity</th>
                    <th>Action</th>
                    <th>Modified Field</th>
                    <th>Change Summary</th>
                    <th>IP / Client</th>
                    <th class="text-end" style="width: 90px;">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $log->created_at ? $log->created_at->format('M d, Y') : 'N/A' }}</div>
                        <small class="text-muted"><i class="far fa-clock me-1"></i>{{ $log->created_at ? $log->created_at->format('H:i:s') : '' }}</small>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-light border p-2 text-primary d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div>
                                <div class="fw-bold">{{ $log->user->name ?? 'System Process' }}</div>
                                <small class="text-muted">{{ $log->user->email ?? 'Automated' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">
                            {{ $log->module_title }}
                        </div>
                        <span class="badge bg-light text-dark border">
                            ID #{{ $log->auditable_id }}
                        </span>
                    </td>
                    <td>
                        {!! $log->action_badge !!}
                    </td>
                    <td>
                        @if($log->field_name)
                            <code class="text-primary fw-bold">{{ $log->field_name }}</code>
                        @else
                            <span class="text-muted small"><em>Full Record ({{ ucfirst($log->action) }})</em></span>
                        @endif
                    </td>
                    <td>
                        @if($log->action === 'updated')
                            <div class="small">
                                <span class="text-danger text-decoration-line-through me-1" title="Old value">
                                    {{ Str::limit($log->old_value ?? 'null', 25) }}
                                </span>
                                <i class="fas fa-arrow-right text-muted small mx-1"></i>
                                <span class="text-success fw-bold" title="New value">
                                    {{ Str::limit($log->new_value ?? 'null', 25) }}
                                </span>
                            </div>
                        @elseif($log->action === 'created')
                            <small class="text-success"><i class="fas fa-check-circle me-1"></i>Record initialized</small>
                        @elseif($log->action === 'deleted')
                            <small class="text-danger"><i class="fas fa-minus-circle me-1"></i>Record removed</small>
                        @else
                            <small class="text-muted">-</small>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted d-block" title="{{ $log->ip_address }}"><i class="fas fa-network-wired me-1"></i>{{ $log->ip_address ?: 'Local' }}</small>
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="openAuditModal({{ json_encode([
                                    'id' => $log->id,
                                    'time' => $log->created_at ? $log->created_at->format('M d, Y H:i:s') : 'N/A',
                                    'user' => $log->user->name ?? 'System',
                                    'email' => $log->user->email ?? '-',
                                    'role' => $log->user->role ?? '-',
                                    'module' => $log->module_title,
                                    'auditable_type' => $log->auditable_type,
                                    'auditable_id' => $log->auditable_id,
                                    'action' => ucfirst($log->action),
                                    'field' => $log->field_name ?? '-',
                                    'old_value' => $log->old_value,
                                    'new_value' => $log->new_value,
                                    'ip' => $log->ip_address ?? '-',
                                    'user_agent' => $log->user_agent ?? '-'
                                ]) }})" title="View Full Inspection Details">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-clipboard-list fa-3x mb-3 text-secondary opacity-50"></i>
                        <p class="mb-2 fw-semibold">No audit records found matching your filters.</p>
                        <a href="{{ route('admin.audit.index') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-undo me-1"></i> Reset Filters
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="text-muted small">
                    Showing <strong>{{ $logs->firstItem() ?? 0 }}</strong> to <strong>{{ $logs->lastItem() ?? 0 }}</strong> of <strong>{{ number_format($logs->total()) }}</strong> audit records
                </div>
                <div>
                    {{ $logs->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal: Audit Record Full Details -->
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold" id="auditModalTitle">
                    <i class="fas fa-search-plus me-2 text-info"></i>Audit Log Record Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small text-muted fw-bold">Date & Time</label>
                        <div class="p-2 bg-light rounded border fw-semibold" id="m_time">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted fw-bold">Action Taken</label>
                        <div class="p-2 bg-light rounded border fw-bold" id="m_action">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted fw-bold">Performing User</label>
                        <div class="p-2 bg-light rounded border" id="m_user">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted fw-bold">Module & Record ID</label>
                        <div class="p-2 bg-light rounded border fw-bold text-primary" id="m_module">-</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label small text-muted fw-bold">Field Modified</label>
                        <div class="p-2 bg-light rounded border font-monospace text-dark" id="m_field">-</div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small text-danger fw-bold"><i class="fas fa-history me-1"></i>Old Value (Previous State)</label>
                        <pre class="p-3 bg-light rounded border text-danger font-monospace small" style="max-height: 220px; overflow-y: auto; white-space: pre-wrap; word-break: break-all;" id="m_old_value"></pre>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-success fw-bold"><i class="fas fa-check-circle me-1"></i>New Value (Updated State)</label>
                        <pre class="p-3 bg-light rounded border text-success font-monospace small" style="max-height: 220px; overflow-y: auto; white-space: pre-wrap; word-break: break-all;" id="m_new_value"></pre>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small text-muted fw-bold">IP Address</label>
                        <div class="p-2 bg-light rounded border small font-monospace" id="m_ip">-</div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small text-muted fw-bold">Browser / Device Agent</label>
                        <div class="p-2 bg-light rounded border small text-muted text-break" id="m_ua">-</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function setQuickDate(type) {
    const today = new Date().toISOString().split('T')[0];
    const fromInput = document.getElementById('date_from');
    const toInput = document.getElementById('date_to');

    if (type === 'today') {
        fromInput.value = today;
        toInput.value = today;
    } else if (type === 'week') {
        const d = new Date();
        d.setDate(d.getDate() - 7);
        fromInput.value = d.toISOString().split('T')[0];
        toInput.value = today;
    } else if (type === 'month') {
        const d = new Date();
        d.setDate(1);
        fromInput.value = d.toISOString().split('T')[0];
        toInput.value = today;
    }

    document.getElementById('auditFilterForm').submit();
}

function openAuditModal(data) {
    document.getElementById('auditModalTitle').innerHTML = '<i class="fas fa-search-plus me-2 text-info"></i>Audit Log #' + data.id + ' (' + data.action + ')';
    document.getElementById('m_time').innerText = data.time;
    document.getElementById('m_action').innerText = data.action;
    document.getElementById('m_user').innerText = data.user + ' (' + data.email + ')';
    document.getElementById('m_module').innerText = data.module + ' #' + data.auditable_id;
    document.getElementById('m_field').innerText = data.field;

    // Format JSON if valid
    document.getElementById('m_old_value').innerText = formatValue(data.old_value);
    document.getElementById('m_new_value').innerText = formatValue(data.new_value);

    document.getElementById('m_ip').innerText = data.ip;
    document.getElementById('m_ua').innerText = data.user_agent;

    const modal = new bootstrap.Modal(document.getElementById('auditDetailModal'));
    modal.show();
}

function formatValue(val) {
    if (!val || val === 'null') return 'null (empty)';
    try {
        const parsed = JSON.parse(val);
        return JSON.stringify(parsed, null, 2);
    } catch (e) {
        return val;
    }
}
</script>
@endsection
