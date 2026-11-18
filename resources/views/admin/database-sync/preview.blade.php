@extends('layouts.app')

@section('title', 'Database Synchronization Preview - CMS')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2>🔍 Database Synchronization Preview Report</h2>
            <p class="text-muted mb-0">
                Batch: <code>{{ $syncLog->batch_id }}</code> | File: <strong>{{ $syncLog->uploaded_filename }}</strong> ({{ $syncLog->formatted_file_size }})
            </p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.database-sync.index') }}">Smart DB Sync</a></li>
                    <li class="breadcrumb-item active">Preview</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.database-sync.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Sync Center
            </a>
            <a href="{{ route('admin.database-backups.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-database me-1"></i> Backups Manager
            </a>
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

<!-- Batch Status Banner -->
@if($syncLog->status === 'previewed')
<div class="alert alert-warning border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <i class="fas fa-info-circle fa-2x text-warning"></i>
        <div>
            <h6 class="mb-0 fw-bold">Preview Stage — Production Database Untouched</h6>
            <div class="small">
                Comparison completed inside isolated staging. Review the detailed differences below and click <strong>Approve & Apply</strong> to merge changes.
            </div>
        </div>
    </div>
    <span class="badge bg-warning text-dark px-3 py-2 fs-6">STATUS: PENDING APPROVAL</span>
</div>
@elseif($syncLog->status === 'applied')
<div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <i class="fas fa-check-circle fa-2x text-success"></i>
        <div>
            <h6 class="mb-0 fw-bold">Successfully Synchronized with Production Database</h6>
            <div class="small">
                Applied on {{ $syncLog->applied_at ? $syncLog->applied_at->format('M d, Y h:i A') : 'N/A' }} by {{ $syncLog->admin_name }}. Pre-sync backup snapshot is safely preserved.
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success px-3 py-2 fs-6">STATUS: APPLIED</span>
        @if($syncLog->canRollback())
        <form action="{{ route('admin.database-sync.rollback', $syncLog->batch_id) }}" method="POST" class="d-inline" onsubmit="return confirm('WARNING: Are you sure you want to rollback the database to its pre-sync snapshot?')">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-undo me-1"></i> Rollback Changes
            </button>
        </form>
        @endif
    </div>
</div>
@elseif($syncLog->status === 'rolled_back')
<div class="alert alert-secondary border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <i class="fas fa-history fa-2x text-secondary"></i>
        <div>
            <h6 class="mb-0 fw-bold">Batch Rolled Back</h6>
            <div class="small">
                This sync batch was restored to its pre-sync backup on {{ $syncLog->rolled_back_at ? $syncLog->rolled_back_at->format('M d, Y h:i A') : 'N/A' }}.
            </div>
        </div>
    </div>
    <span class="badge bg-secondary px-3 py-2 fs-6">STATUS: ROLLED BACK</span>
</div>
@endif

<!-- Diff Counters Cards -->
<div class="row g-3 mb-4">
    <div class="col-md">
        <div class="card border-0 shadow-sm h-100 bg-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">New Tables</div>
                        <h4 class="fw-bold mb-0 text-success">+{{ $syncLog->tables_created_count }}</h4>
                        <small class="text-muted">To be created</small>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                        <i class="fas fa-plus-square fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md">
        <div class="card border-0 shadow-sm h-100 bg-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">New Columns</div>
                        <h4 class="fw-bold mb-0 text-primary">+{{ $syncLog->columns_added_count }}</h4>
                        <small class="text-muted">To be added safely</small>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                        <i class="fas fa-columns fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md">
        <div class="card border-0 shadow-sm h-100 bg-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">New Records</div>
                        <h4 class="fw-bold mb-0 text-info">+{{ number_format($syncLog->records_inserted_count) }}</h4>
                        <small class="text-muted">To be inserted</small>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                        <i class="fas fa-file-import fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md">
        <div class="card border-0 shadow-sm h-100 bg-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Records to Update</div>
                        <h4 class="fw-bold mb-0 text-warning">~{{ number_format($syncLog->records_updated_count) }}</h4>
                        <small class="text-muted">Differing values</small>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                        <i class="fas fa-sync fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md">
        <div class="card border-0 shadow-sm h-100 bg-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Unchanged Records</div>
                        <h4 class="fw-bold mb-0 text-secondary">{{ number_format($syncLog->records_unchanged_count) }}</h4>
                        <small class="text-muted">Identical records</small>
                    </div>
                    <div class="bg-secondary bg-opacity-10 text-secondary rounded-3 p-3">
                        <i class="fas fa-check-double fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incompatible Warnings (if any) -->
@if(!empty($syncLog->incompatible_warnings))
<div class="card border-0 shadow-sm mb-4 border-start border-4 border-danger">
    <div class="card-header bg-danger bg-opacity-10 py-3">
        <h6 class="mb-0 fw-bold text-danger">
            <i class="fas fa-exclamation-triangle me-2"></i> Incompatible Schema Warnings ({{ count($syncLog->incompatible_warnings) }})
        </h6>
    </div>
    <div class="card-body p-3">
        <p class="small text-muted mb-2">The following column type discrepancies were detected. Production columns will NOT be forcibly truncated or altered to prevent data loss.</p>
        <ul class="list-group list-group-flush small">
            @foreach($syncLog->incompatible_warnings as $w)
            <li class="list-group-item px-0">
                <i class="fas fa-arrow-right text-danger me-1"></i> {{ $w['message'] }}
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<!-- Section 1: New Tables to Create -->
@if(!empty($syncLog->tables_created))
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-success">
            <i class="fas fa-plus-circle me-2"></i> New Tables to Create ({{ count($syncLog->tables_created) }})
        </h5>
        <span class="badge bg-success bg-opacity-10 text-success fw-bold">CREATE TABLE IF NOT EXISTS</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Table Name</th>
                    <th>Rows in Uploaded Dump</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($syncLog->tables_created as $idx => $t)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td><strong class="text-dark"><code>{{ $t['table_name'] }}</code></strong></td>
                    <td>
                        <span class="badge bg-light text-dark border">{{ number_format($t['rows_count']) }} records</span>
                    </td>
                    <td>
                        <span class="badge bg-success">Will be created</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Section 2: New Columns to Add -->
@if(!empty($syncLog->columns_added))
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-primary">
            <i class="fas fa-columns me-2"></i> New Columns to Add to Existing Tables ({{ count($syncLog->columns_added) }})
        </h5>
        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">ALTER TABLE ADD COLUMN</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Table</th>
                    <th>New Column Name</th>
                    <th>Column Definition</th>
                    <th>Placement</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($syncLog->columns_added as $idx => $c)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td><code>{{ $c['table_name'] }}</code></td>
                    <td><strong class="text-primary"><code>{{ $c['column_name'] }}</code></strong></td>
                    <td><code>{{ $c['column_definition'] }}</code></td>
                    <td>
                        @if(!empty($c['after_column']))
                            <small class="text-muted">AFTER <code>{{ $c['after_column'] }}</code></small>
                        @else
                            <small class="text-muted">At end of table</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-primary">Will be added</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Section 3: Data Records Differences Breakdown -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="fas fa-table me-2 text-info"></i> Detailed Per-Table Data Comparison
        </h5>
        <span class="badge bg-secondary">{{ count($syncLog->changes_summary ?? []) }} Tables Analyzed</span>
    </div>
    <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
            <thead class="table-light sticky-top">
                <tr>
                    <th>Table Name</th>
                    <th>Table Status</th>
                    <th class="text-center">New Columns</th>
                    <th class="text-end text-info">Inserts</th>
                    <th class="text-end text-warning">Updates</th>
                    <th class="text-end text-secondary">Unchanged</th>
                    <th>Primary Key</th>
                </tr>
            </thead>
            <tbody>
                @foreach($syncLog->changes_summary ?? [] as $tblName => $summary)
                <tr>
                    <td>
                        <strong><code>{{ $tblName }}</code></strong>
                    </td>
                    <td>
                        @if($summary['is_new_table'])
                            <span class="badge bg-success">New Table</span>
                        @else
                            <span class="badge bg-light text-dark border">Existing Table</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if(($summary['new_columns_count'] ?? 0) > 0)
                            <span class="badge bg-primary">+{{ $summary['new_columns_count'] }} cols</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-end fw-bold text-info">
                        {{ number_format($summary['records_to_insert']) }}
                    </td>
                    <td class="text-end fw-bold text-warning">
                        {{ number_format($summary['records_to_update']) }}
                    </td>
                    <td class="text-end text-secondary">
                        {{ number_format($summary['records_unchanged']) }}
                    </td>
                    <td>
                        @if(!empty($summary['primary_key']))
                            <code class="small">{{ implode(', ', $summary['primary_key']) }}</code>
                        @else
                            <span class="text-muted small">No PK (Hash diff)</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Section 4: Admin Approval & Confirmation Card (Only when Previewed) -->
@if($syncLog->status === 'previewed')
<div class="card border-0 shadow-sm mb-4 border-top border-4 border-success">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-success">
            <i class="fas fa-check-shield me-2"></i> Administrator Confirmation & Execution
        </h5>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.database-sync.apply', $syncLog->batch_id) }}" method="POST" id="applySyncForm">
            @csrf

            <h6 class="fw-bold mb-3">Choose Synchronization Operations to Apply:</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="form-check form-switch p-3 bg-light rounded-3 border">
                        <input class="form-check-input" type="checkbox" name="create_tables" id="optCreateTables" value="1" checked>
                        <label class="form-check-label fw-bold" for="optCreateTables">
                            Create Missing Tables ({{ $syncLog->tables_created_count }})
                        </label>
                        <div class="text-muted small">Executes CREATE TABLE IF NOT EXISTS for tables missing in production.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch p-3 bg-light rounded-3 border">
                        <input class="form-check-input" type="checkbox" name="add_columns" id="optAddColumns" value="1" checked>
                        <label class="form-check-label fw-bold" for="optAddColumns">
                            Add Missing Columns ({{ $syncLog->columns_added_count }})
                        </label>
                        <div class="text-muted small">Safely appends missing columns to existing tables using ALTER TABLE ADD.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch p-3 bg-light rounded-3 border">
                        <input class="form-check-input" type="checkbox" name="insert_records" id="optInsertRecords" value="1" checked>
                        <label class="form-check-label fw-bold" for="optInsertRecords">
                            Insert New Records (+{{ number_format($syncLog->records_inserted_count) }})
                        </label>
                        <div class="text-muted small">Inserts rows present in uploaded database that do not exist in production.</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch p-3 bg-light rounded-3 border">
                        <input class="form-check-input" type="checkbox" name="update_records" id="optUpdateRecords" value="1" checked>
                        <label class="form-check-label fw-bold" for="optUpdateRecords">
                            Update Matching Differing Records (~{{ number_format($syncLog->records_updated_count) }})
                        </label>
                        <div class="text-muted small">Updates only differing fields for existing records matched by Primary Key.</div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info d-flex align-items-center gap-2 mb-4">
                <i class="fas fa-shield-alt fa-lg"></i>
                <div class="small">
                    <strong>Pre-Sync Safety Guarantee:</strong> Before making any modification to your live database, the system will automatically create a full database backup snapshot. If anything goes wrong, you can revert with 1-click rollback.
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.database-sync.index') }}" class="btn btn-secondary">
                    Cancel & Return
                </a>
                <button type="button" class="btn btn-success btn-lg px-4" data-bs-toggle="modal" data-bs-target="#confirmApplyModal">
                    <i class="fas fa-check-circle me-1"></i> Approve & Apply Changes Now
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Confirm Apply -->
<div class="modal fade" id="confirmApplyModal" tabindex="-1" aria-labelledby="confirmApplyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="confirmApplyModalLabel">
                    <i class="fas fa-check-shield me-2"></i> Confirm Database Synchronization
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3">Are you ready to apply the approved changes to the live production database?</p>
                <ul class="small text-muted mb-3">
                    <li>A pre-sync recovery snapshot will be saved.</li>
                    <li>Zero existing tables or rows will be deleted.</li>
                    <li>All new tables, columns, and records will be merged incrementally.</li>
                </ul>
                <div class="alert alert-warning py-2 small mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i> Please do not close your browser window while synchronization is running.
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success px-4" id="finalApplyBtn" onclick="submitApplyForm()">
                    <i class="fas fa-play me-1"></i> Confirm & Execute Merge
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function submitApplyForm() {
    const btn = document.getElementById('finalApplyBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Applying Changes...';
    document.getElementById('applySyncForm').submit();
}
</script>
@endif
@endsection
