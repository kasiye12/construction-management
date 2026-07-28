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
                <div class="card-header"><h5 class="mb-0">Permissions</h5></div>
                <div class="card-body">
                    @php $rolePerms = is_string($role->permissions) ? json_decode($role->permissions, true) : ($role->permissions ?? []); @endphp
                    @foreach($permissions as $group => $perms)
                    <div class="mb-3">
                        <h6 class="text-primary mb-2">{{ $group }}</h6>
                        <div class="row">
                            @foreach($perms as $perm)
                            <div class="col-md-6 mb-1">
                                <div class="form-check">
                                    <input type="checkbox" name="permissions[]" value="{{ $perm }}" class="form-check-input" id="perm_{{ $loop->index }}" {{ in_array($perm, $rolePerms) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="perm_{{ $loop->index }}">{{ str_replace('.', ' → ', $perm) }}</label>
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
@endsection
