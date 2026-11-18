@extends('layouts.app')

@section('title', 'Edit Role - CMS')

@section('content')
<div class="page-header">
    <h2>✏️ Edit Role: {{ $role->display_name }}</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <form action="{{ route('admin.roles.update', $role) }}" method="POST">
            @csrf @method('PUT')
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Role Information</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Role Name</label>
                        <input type="text" class="form-control" value="{{ $role->name }}" disabled>
                        <small class="text-muted">Role name cannot be changed</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Display Name <span class="text-danger">*</span></label>
                        <input type="text" name="display_name" class="form-control" value="{{ old('display_name', $role->display_name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $role->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Permissions Matrix</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleAllCheckboxes(true)">Select All</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAllCheckboxes(false)">Deselect All</button>
                    </div>
                </div>
                <div class="card-body">
                    @php $rolePerms = is_string($role->permissions) ? json_decode($role->permissions, true) : ($role->permissions ?? []); @endphp
                    @foreach($permissions as $group => $perms)
                    <div class="mb-4 border-bottom pb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-primary mb-0 fw-bold"><i class="fas fa-folder me-1"></i> {{ $group }}</h6>
                            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-muted" onclick="toggleGroup('grp_{{ Str::slug($group) }}')"><small>Toggle Group</small></button>
                        </div>
                        <div class="row g-2 grp_{{ Str::slug($group) }}">
                            @foreach($perms as $permKey => $permLabel)
                            @php
                                $actualKey = is_numeric($permKey) ? $permLabel : $permKey;
                                $actualLabel = is_numeric($permKey) ? str_replace('.', ' → ', $permLabel) : $permLabel;
                            @endphp
                            <div class="col-md-6">
                                <div class="form-check p-2 rounded bg-light border">
                                    <input type="checkbox" name="permissions[]" value="{{ $actualKey }}" class="form-check-input perm-check" id="perm_{{ Str::slug($actualKey) }}" {{ (in_array('*', $rolePerms) || in_array($actualKey, $rolePerms)) ? 'checked' : '' }}>
                                    <label class="form-check-label ms-1" for="perm_{{ Str::slug($actualKey) }}" style="cursor:pointer;">
                                        <strong>{{ $actualLabel }}</strong>
                                        <div class="text-muted small font-monospace">{{ $actualKey }}</div>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Role</button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<script>
function toggleAllCheckboxes(checked) {
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = checked);
}
function toggleGroup(groupClass) {
    const boxes = document.querySelectorAll('.' + groupClass + ' .perm-check');
    const allChecked = Array.from(boxes).every(b => b.checked);
    boxes.forEach(b => b.checked = !allChecked);
}
</script>
@endsection
