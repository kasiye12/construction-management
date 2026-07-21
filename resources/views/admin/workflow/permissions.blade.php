@extends('layouts.app')

@section('title', 'Workflow Permissions - Admin')

@push('styles')
<style>
    .workflow-table { font-size: 0.75rem; }
    .workflow-table thead th { 
        background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
        color: white;
        font-weight: 600;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 8px;
        white-space: nowrap;
    }
    .workflow-table tbody td { padding: 10px 8px; vertical-align: middle; }
    .workflow-table tbody tr:hover { background: #f8f9ff; }
    .user-name { font-weight: 600; color: #1a237e; font-size: 0.85rem; }
    .user-email { font-size: 0.7rem; color: #6b7280; }
    .section-header { background: #e8eaf6 !important; color: #1a237e !important; font-weight: 700; font-size: 0.75rem !important; }
    .custom-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: #4f46e5; }
    .checkbox-cell { text-align: center; width: 60px; }
    .role-badge { font-size: 0.7rem; padding: 4px 10px; border-radius: 12px; font-weight: 500; text-transform: uppercase; }
    .role-admin { background: #e8f5e9; color: #2e7d32; }
    .role-manager { background: #e3f2fd; color: #1565c0; }
    .role-engineer { background: #fff3e0; color: #e65100; }
    .role-finance { background: #fce4ec; color: #c62828; }
    .role-viewer { background: #f3e5f5; color: #6a1b9a; }
    .preset-btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; font-weight: 600; padding: 8px 16px; border-radius: 8px; }
    .preset-btn:hover { color: white; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102,126,234,0.4); }
    .save-btn { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; padding: 12px 32px; font-weight: 600; border-radius: 10px; color: white; }
    .save-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(79,70,229,0.5); }
    .sticky-col { position: sticky; left: 0; background: white; z-index: 1; }
    .table-wrapper { overflow-x: auto; max-height: 70vh; border-radius: 12px; border: 1px solid #e5e7eb; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2>🔐 Workflow Permissions</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Workflow Permissions</li>
                </ol>
            </nav>
        </div>
        <form action="{{ route('admin.workflow.preset') }}" method="POST" class="d-flex gap-2 align-items-center">
            @csrf
            <select name="preset" class="form-select form-select-sm" style="width:180px;">
                <option value="standard">📋 Standard Preset</option>
                <option value="all_managers">👥 All Managers Preset</option>
            </select>
            <button type="submit" class="btn preset-btn" onclick="return confirm('Apply this preset? It will overwrite all current permissions.')">
                <i class="fas fa-magic me-1"></i> Apply Preset
            </button>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <small class="text-muted">
            <i class="fas fa-info-circle me-1"></i> Check the boxes to grant workflow permissions. Click <strong>Save All Permissions</strong> to apply changes.
        </small>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <form action="{{ route('admin.workflow.update') }}" method="POST" id="workflowForm">
            @csrf
            @method('PUT')
            <div class="table-wrapper">
                <table class="table table-bordered workflow-table mb-0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="sticky-col" style="min-width:180px;">User</th>
                            <th rowspan="2" style="min-width:80px;">Role</th>
                            <th colspan="6" class="section-header text-center">
                                <i class="fas fa-file-invoice-dollar me-1"></i> IPC Workflow
                            </th>
                            <th colspan="2" class="section-header text-center">
                                <i class="fas fa-ruler-combined me-1"></i> QTO Takeoff
                            </th>
                            <th colspan="2" class="section-header text-center">
                                <i class="fas fa-table me-1"></i> Takeoff Sheets
                            </th>
                            <th colspan="2" class="section-header text-center">
                                <i class="fas fa-truck-loading me-1"></i> Material Delivery
                            </th>
                        </tr>
                        <tr>
                            <th>📝 Prepare</th>
                            <th>✅ Check</th>
                            <th>📤 Submit</th>
                            <th>👍 Approve</th>
                            <th>❌ Reject</th>
                            <th>💰 Pay</th>
                            <th>🔍 Verify</th>
                            <th>✅ Approve</th>
                            <th>🔍 Verify</th>
                            <th>✅ Approve</th>
                            <th>📦 Record</th>
                            <th>✔️ Confirm</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $roleColors = [
                                'admin' => 'role-admin',
                                'manager' => 'role-manager',
                                'engineer' => 'role-engineer',
                                'finance' => 'role-finance',
                                'viewer' => 'role-viewer',
                            ]; 
                        @endphp
                        @foreach($users as $user)
                        @php
                            $perms = $user->workflowPermissions->pluck('can_act', 'workflow_step')->toArray();
                            $roleName = $user->getRoleName();
                            $roleClass = $roleColors[$roleName] ?? 'bg-secondary text-white';
                        @endphp
                        <tr>
                            <td class="sticky-col">
                                <div class="user-name">{{ $user->name }}</div>
                                <div class="user-email">{{ $user->email }}</div>
                            </td>
                            <td><span class="role-badge {{ $roleClass }}">{{ ucfirst($roleName) }}</span></td>
                            <td class="checkbox-cell"><input type="checkbox" class="custom-checkbox" name="permissions[{{$user->id}}][prepare]" {{ ($perms['prepare']??false)?'checked':'' }}></td>
                            <td class="checkbox-cell"><input type="checkbox" class="custom-checkbox" name="permissions[{{$user->id}}][check]" {{ ($perms['check']??false)?'checked':'' }}></td>
                            <td class="checkbox-cell"><input type="checkbox" class="custom-checkbox" name="permissions[{{$user->id}}][submit]" {{ ($perms['submit']??false)?'checked':'' }}></td>
                            <td class="checkbox-cell"><input type="checkbox" class="custom-checkbox" name="permissions[{{$user->id}}][approve]" {{ ($perms['approve']??false)?'checked':'' }}></td>
                            <td class="checkbox-cell"><input type="checkbox" class="custom-checkbox" name="permissions[{{$user->id}}][reject]" {{ ($perms['reject']??false)?'checked':'' }}></td>
                            <td class="checkbox-cell"><input type="checkbox" class="custom-checkbox" name="permissions[{{$user->id}}][pay]" {{ ($perms['pay']??false)?'checked':'' }}></td>
                            <td class="checkbox-cell"><input type="checkbox" class="custom-checkbox" name="permissions[{{$user->id}}][verify_takeoff]" {{ ($perms['verify_takeoff']??false)?'checked':'' }}></td>
                            <td class="checkbox-cell"><input type="checkbox" class="custom-checkbox" name="permissions[{{$user->id}}][approve_takeoff]" {{ ($perms['approve_takeoff']??false)?'checked':'' }}></td>
                            <td class="checkbox-cell"><input type="checkbox" class="custom-checkbox" name="permissions[{{$user->id}}][verify_takeoff_sheet]" {{ ($perms['verify_takeoff_sheet']??false)?'checked':'' }}></td>
                            <td class="checkbox-cell"><input type="checkbox" class="custom-checkbox" name="permissions[{{$user->id}}][approve_takeoff_sheet]" {{ ($perms['approve_takeoff_sheet']??false)?'checked':'' }}></td>
                            <td class="checkbox-cell"><input type="checkbox" class="custom-checkbox" name="permissions[{{$user->id}}][record_delivery]" {{ ($perms['record_delivery']??false)?'checked':'' }}></td>
                            <td class="checkbox-cell"><input type="checkbox" class="custom-checkbox" name="permissions[{{$user->id}}][confirm_delivery]" {{ ($perms['confirm_delivery']??false)?'checked':'' }}></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="p-3 bg-light d-flex justify-content-between align-items-center" style="border-top:2px solid #e5e7eb;">
                <span class="text-muted small"><i class="fas fa-users me-1"></i> {{ $users->count() }} users</span>
                <button type="submit" class="btn save-btn">
                    <i class="fas fa-save me-2"></i> Save All Permissions
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
