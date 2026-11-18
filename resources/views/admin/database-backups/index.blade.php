@extends('layouts.app')

@section('title', 'Database Management & Backup - CMS')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2>💾 Database Management & Backup</h2>
            <p class="text-muted mb-0">Full MySQL database backups, automated daily synchronization, and one-click downloads.</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Administration</li>
                    <li class="breadcrumb-item active">Database Backups</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <a href="{{ route('admin.database-backups.download', ['filename' => 'construction_management.sql']) }}" class="btn btn-outline-info">
                <i class="fas fa-download me-1"></i> Download Master Dump (.sql)
            </a>

            <!-- Trigger Daily Sync -->
            <form action="{{ route('admin.database-backups.run-daily') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-success" onclick="return confirm('Run daily backup synchronization now? This will refresh all canonical backup files.')">
                    <i class="fas fa-sync-alt me-1"></i> Run Daily Sync Now
                </button>
            </form>

            <!-- Create Manual Backup Modal Trigger -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBackupModal">
                <i class="fas fa-plus me-1"></i> Create Backup Now
            </button>
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

<!-- Daily Auto-Backup Status Banner -->
<div class="card border-0 shadow-sm mb-4 bg-light border-start border-4 border-success">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="fas fa-clock fa-lg"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-bold">Daily Automated Backup</h6>
                        <span class="badge bg-success">SCHEDULED & ACTIVE</span>
                    </div>
                    <div class="small text-muted mt-1">
                        Runs automatically every night at <strong>01:00 AM</strong>. Automatically updates canonical files and purges backups older than <strong>30 days</strong>.
                    </div>
                </div>
            </div>
            <div class="text-end small">
                <div><strong>Canonical Master:</strong> <code>/home/ks/Documents/construction_management.sql</code></div>
                <div class="text-muted">Repository Replica: <code>database/construction_management_full_dump.sql</code></div>
            </div>
        </div>
    </div>
</div>

<!-- Database Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Database Name</div>
                        <h5 class="fw-bold mb-0 text-primary">{{ $stats['database_name'] }}</h5>
                        <small class="text-muted">MySQL {{ Str::limit($stats['mysql_version'], 15) }}</small>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                        <i class="fas fa-database fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Tables & Rows</div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $stats['tables_count'] }} Tables</h5>
                        <small class="text-muted">~{{ number_format($stats['total_rows']) }} total records</small>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                        <i class="fas fa-table fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Database Storage</div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $stats['total_size_formatted'] }}</h5>
                        <small class="text-muted">Data: {{ $stats['data_size_formatted'] }} | Index: {{ $stats['index_size_formatted'] }}</small>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                        <i class="fas fa-hdd fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Backups Stored</div>
                        <h5 class="fw-bold mb-0 text-success">{{ $stats['backups_count'] }} Files</h5>
                        <small class="text-muted">
                            @if($stats['last_backup'])
                                Last: {{ $stats['last_backup']['created_at']->diffForHumans() }}
                            @else
                                No backups recorded
                            @endif
                        </small>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                        <i class="fas fa-save fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Backups List Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-archive me-2 text-primary"></i> Available Backup Files
        </h5>
        <span class="badge bg-secondary">{{ count($backups) }} stored (Total: {{ $stats['backups_total_size'] }})</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Filename</th>
                    <th>Type</th>
                    <th>File Size</th>
                    <th>Created At</th>
                    <th>Age</th>
                    <th class="text-end" style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($backups as $b)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas {{ $b['is_compressed'] ? 'fa-file-archive text-warning' : 'fa-file-code text-primary' }} fa-lg"></i>
                            <div>
                                <strong class="text-dark">{{ $b['filename'] }}</strong>
                                @if($b['is_canonical'])
                                    <span class="badge bg-info ms-1">Active Canonical</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($b['type'] === 'daily')
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold">
                                <i class="fas fa-calendar-day me-1"></i> Daily Auto
                            </span>
                        @elseif($b['type'] === 'canonical')
                            <span class="badge bg-info bg-opacity-10 text-info fw-bold">
                                <i class="fas fa-star me-1"></i> Canonical Master
                            </span>
                        @else
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">
                                <i class="fas fa-user me-1"></i> Manual
                            </span>
                        @endif
                    </td>
                    <td class="fw-semibold">
                        {{ $b['size_formatted'] }}
                    </td>
                    <td>
                        {{ $b['created_at']->format('M d, Y h:i A') }}
                    </td>
                    <td class="text-muted small">
                        {{ $b['age'] }}
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.database-backups.download', ['filename' => $b['filename']]) }}" class="btn btn-outline-success" title="Download Backup">
                                <i class="fas fa-download me-1"></i> Download
                            </a>
                            @if(!$b['is_canonical'])
                            <form action="{{ route('admin.database-backups.destroy', ['filename' => $b['filename']]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this backup file?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Delete Backup">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-database fa-3x mb-3 text-secondary"></i>
                        <h6>No backup files found yet.</h6>
                        <p class="small">Click <strong>Create Backup Now</strong> to generate your first full database backup.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Database Tables & Engine Breakdown (Collapsible) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-list-alt me-2 text-info"></i> Database Tables Inspection
            </h5>
            <small class="text-muted">Live schema statistics for <code>{{ $stats['database_name'] }}</code></small>
        </div>
        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#tablesCollapse" aria-expanded="false" aria-controls="tablesCollapse">
            <i class="fas fa-eye me-1"></i> Toggle Tables View ({{ count($stats['tables']) }})
        </button>
    </div>
    <div class="collapse" id="tablesCollapse">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-sm table-striped table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>#</th>
                        <th>Table Name</th>
                        <th>Engine</th>
                        <th class="text-end">Rows</th>
                        <th class="text-end">Data Size</th>
                        <th class="text-end">Index Size</th>
                        <th class="text-end">Total Size</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['tables'] as $idx => $t)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><code>{{ $t['name'] }}</code></td>
                        <td><span class="badge bg-light text-dark border">{{ $t['engine'] }}</span></td>
                        <td class="text-end fw-semibold">{{ number_format($t['rows']) }}</td>
                        <td class="text-end">{{ $t['data_formatted'] }}</td>
                        <td class="text-end">{{ $t['index_formatted'] }}</td>
                        <td class="text-end fw-bold text-primary">{{ $t['size_formatted'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create Backup Now -->
<div class="modal fade" id="createBackupModal" tabindex="-1" aria-labelledby="createBackupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.database-backups.create') }}" method="POST" id="manualBackupForm">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="createBackupModalLabel">
                        <i class="fas fa-save me-2"></i> Create Database Backup
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        This action executes a live MySQL dump of all <strong>{{ $stats['tables_count'] }} tables</strong>, routines, triggers, and data records. It will also immediately update the canonical master copies at <code>/home/ks/Documents/construction_management.sql</code> and <code>database/construction_management_full_dump.sql</code>.
                    </p>

                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body py-2 px-3">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="compress" id="compressOption" value="1">
                                <label class="form-check-label fw-semibold" for="compressOption">
                                    Compress with Gzip (<code>.sql.gz</code>)
                                </label>
                                <div class="text-muted small">Reduces file size by ~80% for storage savings.</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 small mb-0">
                        <i class="fas fa-info-circle me-1"></i> Process runs safely with <code>--single-transaction</code> without locking tables or interrupting active users.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="backupSubmitBtn">
                        <i class="fas fa-hdd me-1"></i> Start Backup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const backupForm = document.getElementById('manualBackupForm');
    const backupBtn = document.getElementById('backupSubmitBtn');
    if (backupForm && backupBtn) {
        backupForm.addEventListener('submit', function() {
            backupBtn.disabled = true;
            backupBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating Dump...';
        });
    }
});
</script>
@endsection
