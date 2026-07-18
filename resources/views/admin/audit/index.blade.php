@extends('layouts.app')

@section('title', 'Audit Trail - CMS')

@section('content')
<div class="page-header">
    <h2>📝 Audit Trail</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Audit Trail</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-history me-2"></i>All System Changes</h5></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Date/Time</th><th>User</th><th>Module</th><th>Action</th><th>Field</th><th>Old Value</th><th>New Value</th><th>IP</th></tr>
            </thead>
            <tbody>
                @php $logs = \App\Models\AuditTrail::with('user')->latest()->paginate(30); @endphp
                @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                    <td>{{ $log->user->name ?? 'System' }}</td>
                    <td><small>{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</small></td>
                    <td><span class="badge bg-{{ $log->action=='created'?'success':($log->action=='updated'?'warning':($log->action=='deleted'?'danger':'info')) }}">{{ ucfirst($log->action) }}</span></td>
                    <td>{{ $log->field_name ?? '-' }}</td>
                    <td><small>{{ Str::limit($log->old_value, 30) }}</small></td>
                    <td><small>{{ Str::limit($log->new_value, 30) }}</small></td>
                    <td><small>{{ $log->ip_address ?? '-' }}</small></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4">No audit records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer"><div class="d-flex justify-content-between align-items-center px-3 py-2">
                <div class="pagination-info">Showing {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} results</div>
                {{ $logs->links('vendor.pagination.custom') }}
            </div></div>
</div>
@endsection
