@extends('layouts.app')

@section('title', 'Smart Database Import & Sync - CMS')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2>🔄 Smart Database Import & Synchronization</h2>
            <p class="text-muted mb-0">Non-destructive SQL database synchronization — preserves all existing production data, tables, and rows.</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Administration</li>
                    <li class="breadcrumb-item active">Smart DB Sync</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.database-backups.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-database me-1"></i> Database Backups
            </a>
            <a href="{{ route('admin.database-backups.download', ['filename' => 'construction_management.sql']) }}" class="btn btn-outline-info">
                <i class="fas fa-download me-1"></i> Download Master Dump
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

<!-- Core Safety Guarantees Banner -->
<div class="card border-0 shadow-sm mb-4 border-start border-4 border-primary bg-light">
    <div class="card-body p-4">
        <div class="d-flex align-items-start gap-3">
            <div class="bg-primary text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="fas fa-shield-alt fa-lg"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-1 text-primary">Zero-Data-Loss Incremental Database Synchronization</h5>
                <p class="small text-muted mb-2">
                    Upload any updated database <code>.sql</code> file provided by developers. The system loads it into a secure staging sandbox, calculates the structural and data differences, and presents an interactive preview.
                </p>
                <div class="row g-2 pt-1">
                    <div class="col-md-3">
                        <div class="small fw-semibold text-success"><i class="fas fa-check me-1"></i> Never Drops Tables</div>
                        <div class="text-muted small">Existing tables are always preserved.</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small fw-semibold text-success"><i class="fas fa-check me-1"></i> Never Deletes Records</div>
                        <div class="text-muted small">Existing rows are never wiped or lost.</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small fw-semibold text-success"><i class="fas fa-check me-1"></i> Adds Missing Columns</div>
                        <div class="text-muted small">Safe ALTER TABLE incremental adds.</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small fw-semibold text-success"><i class="fas fa-check me-1"></i> 1-Click Rollback</div>
                        <div class="text-muted small">Instant restore from pre-sync backup.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Upload SQL File Card -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-primary text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-cloud-upload-alt me-2"></i> Upload Updated SQL Database
                    </h5>
                    <span class="badge bg-light text-primary fw-bold">.SQL / Up to 100MB</span>
                </div>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.database-sync.upload') }}" method="POST" enctype="multipart/form-data" id="syncUploadForm">
                    @csrf

                    <div class="mb-4">
                        <label for="sqlFile" class="form-label fw-bold">Select Developer SQL File <span class="text-danger">*</span></label>
                        <div class="border border-2 border-dashed rounded-3 p-4 text-center bg-light" id="dropArea" style="border-color: #cbd5e1 !important; cursor: pointer;">
                            <i class="fas fa-database text-primary fa-3x mb-3"></i>
                            <h6 class="fw-bold mb-1">Click to select or drag & drop .sql file here</h6>
                            <p class="text-muted small mb-3">Accepts standard MySQL dumps (mysqldump, phpMyAdmin, DBeaver, Workbench)</p>
                            <input type="file" name="sql_file" id="sqlFile" class="form-control d-none @error('sql_file') is-invalid @enderror" accept=".sql" required>
                            <button type="button" class="btn btn-primary btn-sm px-4" onclick="document.getElementById('sqlFile').click()">
                                <i class="fas fa-folder-open me-1"></i> Browse File
                            </button>
                            <div id="fileSelectedName" class="mt-2 fw-bold text-success" style="display:none;"></div>
                        </div>
                        @error('sql_file')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-light border small text-muted mb-4">
                        <i class="fas fa-info-circle text-info me-1"></i>
                        Uploading only parses the file in an isolated sandbox. <strong>No changes are applied to your live database</strong> until you review and approve the comparison preview.
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.database-backups.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-success px-4 py-2 fw-semibold" id="submitBtn">
                            <i class="fas fa-search me-1"></i> Compare & Generate Preview
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- How It Works Card -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="fas fa-cogs me-2"></i> How Smart Synchronization Works
                </h5>
            </div>
            <div class="card-body p-4">
                <ol class="list-group list-group-numbered list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 pb-3">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-dark">Isolated Staging Analysis</div>
                            The uploaded SQL file is loaded into a dedicated temporary MySQL database to test syntax and extract schema.
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 pb-3">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-dark">Automated Schema & Data Diff</div>
                            The engine compares tables, columns, data types, primary keys, and row records against the live production database.
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 pb-3">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-dark">Detailed Preview & Approval</div>
                            You see a breakdown of newly added tables, columns, rows to insert, rows to update, and any incompatible warnings.
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 pb-3">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-dark">Pre-Sync Backup & Incremental Apply</div>
                            Before executing changes, an automatic full backup is created. Only safe <code>CREATE TABLE</code>, <code>ALTER TABLE ADD COLUMN</code>, and <code>INSERT/UPDATE</code> are executed.
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 pb-0">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold text-dark">1-Click Safety Rollback</div>
                            If any issue arises, you can immediately revert the database to its exact pre-sync state with one click.
                        </div>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Synchronization History Log -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-history me-2 text-primary"></i> Synchronization History & Audit Log
        </h5>
        <span class="badge bg-secondary">{{ $history->total() }} Total Events</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Batch ID</th>
                    <th>Uploaded File</th>
                    <th>Status</th>
                    <th>Changes Breakdown</th>
                    <th>Admin</th>
                    <th>Timestamp</th>
                    <th class="text-end" style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $item)
                <tr>
                    <td>
                        <code>{{ $item->batch_id }}</code>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $item->uploaded_filename }}</div>
                        <small class="text-muted">{{ $item->formatted_file_size }}</small>
                    </td>
                    <td>
                        @if($item->status === 'applied')
                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Applied</span>
                        @elseif($item->status === 'previewed')
                            <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Previewed</span>
                        @elseif($item->status === 'rolled_back')
                            <span class="badge bg-secondary"><i class="fas fa-undo me-1"></i> Rolled Back</span>
                        @else
                            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Failed</span>
                        @endif
                    </td>
                    <td>
                        <div class="small">
                            <span class="badge bg-light text-dark border me-1">+{{ $item->tables_created_count }} Tables</span>
                            <span class="badge bg-light text-dark border me-1">+{{ $item->columns_added_count }} Cols</span>
                            <span class="badge bg-light text-success border me-1">+{{ $item->records_inserted_count }} Inserts</span>
                            <span class="badge bg-light text-primary border">~{{ $item->records_updated_count }} Updates</span>
                        </div>
                    </td>
                    <td>
                        <span class="small text-muted">{{ $item->admin_name ?? 'Administrator' }}</span>
                    </td>
                    <td>
                        <div class="small text-dark">{{ $item->created_at->format('M d, Y h:i A') }}</div>
                        <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.database-sync.preview', $item->batch_id) }}" class="btn btn-outline-primary" title="View Preview & Details">
                                <i class="fas fa-eye me-1"></i> Details
                            </a>
                            @if($item->canRollback())
                            <form action="{{ route('admin.database-sync.rollback', $item->batch_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Rollback database to the pre-sync snapshot taken before applying this batch?')">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger" title="Rollback to Pre-Sync State">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="fas fa-clipboard-check fa-3x mb-3 text-secondary"></i>
                        <h6>No database synchronization history yet.</h6>
                        <p class="small">Upload an updated developer <code>.sql</code> file above to preview and synchronize safely.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white border-top">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 flex-wrap gap-2">
            <div class="pagination-info small text-muted">
                Showing {{ $history->firstItem() ?? 0 }} - {{ $history->lastItem() ?? 0 }} of {{ $history->total() }} sync logs
            </div>
            {{ $history->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('sqlFile');
    const dropArea = document.getElementById('dropArea');
    const fileNameDisplay = document.getElementById('fileSelectedName');
    const form = document.getElementById('syncUploadForm');
    const submitBtn = document.getElementById('submitBtn');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (fileInput.files.length > 0) {
                const name = fileInput.files[0].name;
                const size = (fileInput.files[0].size / (1024 * 1024)).toFixed(2);
                fileNameDisplay.innerHTML = `<i class="fas fa-check-circle me-1"></i> Selected: <strong>${name}</strong> (${size} MB)`;
                fileNameDisplay.style.display = 'block';
            }
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropArea.classList.add('bg-white', 'border-primary');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, (e) => {
                e.preventDefault();
                dropArea.classList.remove('bg-white', 'border-primary');
            }, false);
        });

        dropArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                fileInput.files = files;
                const name = files[0].name;
                const size = (files[0].size / (1024 * 1024)).toFixed(2);
                fileNameDisplay.innerHTML = `<i class="fas fa-check-circle me-1"></i> Selected: <strong>${name}</strong> (${size} MB)`;
                fileNameDisplay.style.display = 'block';
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Analyzing Schema & Comparing Records...';
        });
    }
});
</script>
@endsection
