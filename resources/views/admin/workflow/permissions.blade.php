@extends('layouts.app')

@section('title', 'Workflow Permissions - CMS')

@push('styles')
<style>
    .perm-table td { vertical-align: middle; }
    .step-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h2>🔐 Workflow Approval Permissions</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
            <li class="breadcrumb-item active">Workflow Permissions</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">👥 User Workflow Permissions</h5>
                <p class="text-muted small mb-0">Configure which users can perform each approval step on payment certificates.</p>
            </div>
            <div class="card-body">
                <!-- Legend -->
                <div class="mb-4">
                    <span class="step-badge bg-info text-white me-2">📝 Prepare</span>
                    <span class="step-badge bg-warning text-white me-2">✅ Check</span>
                    <span class="step-badge bg-primary text-white me-2">📤 Submit</span>
                    <span class="step-badge bg-success text-white me-2">✔️ Approve</span>
                    <span class="step-badge bg-danger text-white me-2">❌ Reject</span>
                    <span class="step-badge bg-dark text-white">💰 Pay</span>
                </div>

                <form action="{{ route('admin.workflow.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="table-responsive">
                        <table class="table table-bordered perm-table">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th class="text-center">📝 Prepare</th>
                                    <th class="text-center">✅ Check</th>
                                    <th class="text-center">📤 Submit</th>
                                    <th class="text-center">✔️ Approve</th>
                                    <th class="text-center">❌ Reject</th>
                                    <th class="text-center">💰 Pay</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $users = \App\Models\User::with('workflowPermissions')->orderBy('name')->get(); @endphp
                                @foreach($users as $user)
                                <tr>
                                    <td>
                                        <strong>{{ $user->name }}</strong>
                                        <br><small class="text-muted">{{ $user->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $user->role_label }}</span>
                                    </td>
                                    @foreach(['prepare', 'check', 'submit', 'approve', 'reject', 'pay'] as $step)
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="permissions[{{ $user->id }}][{{ $step }}]" 
                                                   value="1"
                                                   {{ $user->workflowPermissions->where('workflow_step', $step)->where('can_act', true)->count() > 0 ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-outline-primary" onclick="checkAll(true)">
                            <i class="fas fa-check-all me-1"></i> Enable All
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="checkAll(false)">
                            <i class="fas fa-times me-1"></i> Disable All
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Permissions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Quick Presets -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">📋 Quick Presets</h5></div>
            <div class="card-body">
                <p class="text-muted small">Apply a preset permission template:</p>
                <div class="d-flex gap-2 flex-wrap">
                    <form action="{{ route('admin.workflow.preset') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="preset" value="standard">
                        <button class="btn btn-outline-primary btn-sm">Standard (Role-based)</button>
                    </form>
                    <form action="{{ route('admin.workflow.preset') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="preset" value="all_managers">
                        <button class="btn btn-outline-success btn-sm">All Managers Full Access</button>
                    </form>
                    <form action="{{ route('admin.workflow.preset') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="preset" value="engineers_only">
                        <button class="btn btn-outline-warning btn-sm">Engineers Only (Prepare/Check)</button>
                    </form>
                    <form action="{{ route('admin.workflow.preset') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="preset" value="finance_approve">
                        <button class="btn btn-outline-info btn-sm">Finance Approve/Pay Only</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function checkAll(enable) {
    document.querySelectorAll('.form-check-input').forEach(cb => cb.checked = enable);
}
</script>
@endpush
