@extends('layouts.app')

@section('title', 'Manage User Permissions - CMS')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>🔐 User Permissions: {{ $user->name }}</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">Permissions</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Users
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 col-md-5 mb-3">
        <!-- User Summary Card -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 text-white"><i class="fas fa-user-circle me-1"></i> User Profile</h5>
            </div>
            <div class="card-body text-center">
                <div class="user-avatar mx-auto mb-2" style="width: 64px; height: 64px; font-size: 1.5rem;">
                    {{ $user->initials }}
                </div>
                <h5 class="mb-0">{{ $user->name }}</h5>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                <div class="mb-2">
                    <span class="badge bg-info text-dark">{{ $user->role_label }}</span>
                    @if($user->is_super_admin)
                        <span class="badge bg-danger ms-1"><i class="fas fa-crown me-1"></i> Super Admin</span>
                    @endif
                    @if($hasCustom)
                        <span class="badge bg-warning text-dark ms-1"><i class="fas fa-sliders-h me-1"></i> Custom Permissions</span>
                    @else
                        <span class="badge bg-secondary ms-1"><i class="fas fa-users-cog me-1"></i> Role Inherited</span>
                    @endif
                </div>
                <hr>
                <div class="text-start small text-muted">
                    <p class="mb-1"><strong>Department:</strong> {{ $user->department ?? 'General' }}</p>
                    <p class="mb-1"><strong>Position:</strong> {{ $user->position ?? 'Staff' }}</p>
                    <p class="mb-1"><strong>Status:</strong> {!! $user->is_active ? '<span class="text-success fw-bold">Active</span>' : '<span class="text-danger fw-bold">Inactive</span>' !!}</p>
                </div>
            </div>
        </div>

        <!-- Help Info Box -->
        <div class="card bg-light border">
            <div class="card-body">
                <h6 class="fw-bold"><i class="fas fa-info-circle text-primary me-1"></i> Permission Governance</h6>
                <ul class="small text-muted ps-3 mb-0">
                    <li class="mb-1"><strong>Super Admin</strong> grants unrestricted access to all ERP modules, submenus, pages, and documents.</li>
                    <li class="mb-1"><strong>Inherit from Role</strong> automatically aligns the user with permissions configured in the Role Management screen.</li>
                    <li><strong>Custom Permissions</strong> allows granting or revoking specific capabilities for this user specifically.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8 col-md-7">
        <form action="{{ route('admin.users.permissions.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Access Mode Selection -->
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-shield-alt text-primary me-2"></i> Access Strategy</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3 p-3 bg-light rounded border">
                        <input class="form-check-input ms-0 me-3" type="checkbox" name="is_super_admin" value="1" id="is_super_admin" {{ $user->is_super_admin ? 'checked' : '' }} onchange="handleSuperAdminToggle(this)">
                        <label class="form-check-label fw-bold" for="is_super_admin">
                            👑 Grant Super Administrator Access (Full System Access)
                            <div class="text-muted fw-normal small">User bypasses all permission restrictions and has unrestricted access to all 20 modules and documents.</div>
                        </label>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="form-check p-3 rounded border h-100 {{ !$hasCustom ? 'bg-primary-subtle border-primary' : 'bg-light' }}" id="mode_card_inherit">
                                <input class="form-check-input" type="radio" name="permission_mode" id="mode_inherit" value="inherit" {{ !$hasCustom ? 'checked' : '' }} onchange="togglePermissionMode('inherit')">
                                <label class="form-check-label ms-1" for="mode_inherit" style="cursor: pointer;">
                                    <strong>Inherit from Role</strong>
                                    <div class="text-muted small">Synchronize automatically with role: <strong>{{ $user->role_label }}</strong></div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check p-3 rounded border h-100 {{ $hasCustom ? 'bg-warning-subtle border-warning' : 'bg-light' }}" id="mode_card_custom">
                                <input class="form-check-input" type="radio" name="permission_mode" id="mode_custom" value="custom" {{ $hasCustom ? 'checked' : '' }} onchange="togglePermissionMode('custom')">
                                <label class="form-check-label ms-1" for="mode_custom" style="cursor: pointer;">
                                    <strong>Custom User Permissions</strong>
                                    <div class="text-muted small">Explicitly configure granular access overrides for this user.</div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Granular Permissions Matrix -->
            <div class="card mb-3" id="permissionsMatrixCard">
                <div class="card-header d-flex justify-content-between align-items-center bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-check text-primary me-2"></i>
                        Module & Document Permissions
                    </h5>
                    <div class="btn-group btn-group-sm" id="matrixControls">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleAllCheckboxes(true)">Select All</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAllCheckboxes(false)">Deselect All</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="inheritNotice" class="alert alert-info d-flex align-items-center mb-3 {{ $hasCustom ? 'd-none' : '' }}">
                        <i class="fas fa-info-circle fa-lg me-2"></i>
                        <div>Currently displaying permissions inherited from role: <strong>{{ $user->role_label }}</strong>. Switch to <strong>Custom User Permissions</strong> above to customize.</div>
                    </div>

                    @foreach($permissions as $group => $perms)
                    <div class="mb-4 border-bottom pb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-primary mb-0 fw-bold"><i class="fas fa-folder me-1"></i> {{ $group }}</h6>
                            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-muted perm-grp-btn" onclick="toggleGroup('grp_{{ Str::slug($group) }}')"><small>Toggle Group</small></button>
                        </div>
                        <div class="row g-2 grp_{{ Str::slug($group) }}">
                            @foreach($perms as $permKey => $permLabel)
                            @php
                                $isChecked = in_array('*', $userPermissions) || in_array($permKey, $userPermissions);
                                $isRoleDefault = in_array('*', $rolePermissions) || in_array($permKey, $rolePermissions);
                            @endphp
                            <div class="col-md-6">
                                <div class="form-check p-2 rounded bg-light border h-100">
                                    <input type="checkbox" name="permissions[]" value="{{ $permKey }}" class="form-check-input perm-check" id="user_perm_{{ Str::slug($permKey) }}" {{ $isChecked ? 'checked' : '' }} {{ !$hasCustom ? 'disabled' : '' }}>
                                    <label class="form-check-label ms-1" for="user_perm_{{ Str::slug($permKey) }}" style="cursor:pointer;">
                                        <strong>{{ $permLabel }}</strong>
                                        <div class="text-muted small font-monospace">{{ $permKey }}</div>
                                        @if($isRoleDefault)
                                            <span class="badge bg-secondary-subtle text-secondary border small" style="font-size: 0.65rem;">Role Default</span>
                                        @endif
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="d-flex gap-2 mb-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i> Save User Permissions
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary px-3">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function togglePermissionMode(mode) {
    const isCustom = (mode === 'custom');
    const checkboxes = document.querySelectorAll('.perm-check');
    const controls = document.getElementById('matrixControls');
    const inheritNotice = document.getElementById('inheritNotice');
    const cardInherit = document.getElementById('mode_card_inherit');
    const cardCustom = document.getElementById('mode_card_custom');

    checkboxes.forEach(cb => {
        cb.disabled = !isCustom;
    });

    if (isCustom) {
        controls.classList.remove('d-none');
        inheritNotice.classList.add('d-none');
        cardCustom.classList.add('bg-warning-subtle', 'border-warning');
        cardInherit.classList.remove('bg-primary-subtle', 'border-primary');
        cardInherit.classList.add('bg-light');
    } else {
        controls.classList.add('d-none');
        inheritNotice.classList.remove('d-none');
        cardInherit.classList.add('bg-primary-subtle', 'border-primary');
        cardCustom.classList.remove('bg-warning-subtle', 'border-warning');
        cardCustom.classList.add('bg-light');
    }
}

function handleSuperAdminToggle(el) {
    if (el.checked) {
        document.querySelectorAll('.perm-check').forEach(cb => cb.checked = true);
    }
}

function toggleAllCheckboxes(checked) {
    document.querySelectorAll('.perm-check').forEach(cb => {
        if (!cb.disabled) cb.checked = checked;
    });
}

function toggleGroup(groupClass) {
    const boxes = document.querySelectorAll('.' + groupClass + ' .perm-check:not([disabled])');
    if (boxes.length === 0) return;
    const allChecked = Array.from(boxes).every(b => b.checked);
    boxes.forEach(b => b.checked = !allChecked);
}

// Initial state initialization
document.addEventListener('DOMContentLoaded', function() {
    const isInherit = document.getElementById('mode_inherit').checked;
    if (isInherit) {
        togglePermissionMode('inherit');
    }
});
</script>
@endpush
@endsection
