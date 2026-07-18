@extends('layouts.app')

@section('title', 'Workflow Permissions - CMS')

@push('styles')
<style>
    .perm-table td { vertical-align: middle; }
    .perm-table th { font-size: 0.65rem; text-align: center; }
    .step-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-block; margin: 2px; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h2>🔐 Workflow Approval Permissions</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Workflow Permissions</li>
        </ol>
    </nav>
</div>

<!-- Legend -->
<div class="mb-3 p-3 bg-light rounded">
    <strong class="me-3">Legend:</strong>
    <span class="step-badge bg-info text-white">📝 Prepare</span>
    <span class="step-badge bg-warning">✅ Check</span>
    <span class="step-badge bg-primary text-white">📤 Submit</span>
    <span class="step-badge bg-success text-white">✔️ Approve</span>
    <span class="step-badge bg-danger text-white">❌ Reject</span>
    <span class="step-badge bg-dark text-white">💰 Pay</span>
    <span class="step-badge bg-info text-white">📐 Verify T.O</span>
    <span class="step-badge bg-success text-white">📐 Approve T.O</span>
    <span class="step-badge bg-warning">📦 Record Del</span>
    <span class="step-badge bg-success text-white">📦 Confirm Del</span>
</div>

<!-- MAIN FORM - Save Permissions -->
<form action="{{ route('admin.workflow.update') }}" method="POST" id="mainForm">
    @csrf @method('PUT')
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">👥 User Workflow Permissions</h5>
            <div>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="checkAll(true)"><i class="fas fa-check-all"></i> All On</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="checkAll(false)"><i class="fas fa-times"></i> All Off</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered perm-table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2">User</th>
                            <th rowspan="2">Role</th>
                            <th colspan="6" class="text-center" style="background:#e8eaf6;">📄 IPC Certificate</th>
                            <th colspan="2" class="text-center" style="background:#e0f2fe;">📐 Take-Off</th>
                            <th colspan="2" class="text-center" style="background:#fef3c7;">📦 Delivery</th>
                        </tr>
                        <tr>
                            <th>📝 Prepare</th><th>✅ Check</th><th>📤 Submit</th>
                            <th>✔️ Approve</th><th>❌ Reject</th><th>💰 Pay</th>
                            <th>📐 Verify</th><th>📐 Approve</th>
                            <th>📦 Record</th><th>📦 Confirm</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $users = \App\Models\User::with('workflowPermissions')->orderBy('name')->get(); @endphp
                        @foreach($users as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong><br><small class="text-muted">{{ $user->email }}</small></td>
                            <td><span class="badge bg-secondary">{{ $user->role_label }}</span></td>
                            
                            @foreach(['prepare','check','submit','approve','reject','pay','verify_takeoff','approve_takeoff','record_delivery','confirm_delivery'] as $step)
                            <td class="text-center">
                                <input type="checkbox" name="permissions[{{ $user->id }}][{{ $step }}]" value="1"
                                    {{ $user->workflowPermissions->where('workflow_step',$step)->where('can_act',true)->count()>0 ? 'checked' : '' }}>
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save All Permissions</button>
        </div>
    </div>
</form>

<!-- SEPARATE PRESET FORMS (POST method) -->
<div class="card mt-4">
    <div class="card-header"><h5 class="mb-0">📋 Quick Presets</h5></div>
    <div class="card-body">
        <p class="text-muted small mb-2">Apply a preset template to all users based on their roles:</p>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.workflow.preset') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="preset" value="standard">
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-user-check me-1"></i> Standard (Role-based)
                </button>
            </form>
            <form action="{{ route('admin.workflow.preset') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="preset" value="all_managers">
                <button type="submit" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-user-shield me-1"></i> All Managers Full Access
                </button>
            </form>
        </div>
        <div class="mt-2 small text-muted">
            <strong>Standard Preset:</strong><br>
            • Admin: All permissions<br>
            • Manager: Prepare, Check, Submit, Approve, Reject | Verify/Approve T.O | Record/Confirm Delivery<br>
            • Engineer: Prepare, Check, Submit | Verify T.O | Record Delivery<br>
            • Finance: Approve, Reject, Pay | Confirm Delivery<br>
            • Viewer: No permissions
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function checkAll(enable) {
    document.querySelectorAll('.perm-table input[type="checkbox"]').forEach(cb => cb.checked = enable);
}
</script>
@endpush
