@extends('layouts.app')

@section('title', 'Edit Role - CMS')

@section('content')
<div class="page-header">
    <h2>✏️ Edit Role: {{ $role->display_name }}</h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card">
            <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label class="form-label">Display Name <span class="text-danger">*</span></label>
                    <input type="text" name="display_name" class="form-control" 
                           value="{{ old('display_name', $role->display_name) }}" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $role->description) }}</textarea>
                </div>
                
                <h6>Permissions</h6>
                @php $currentPerms = is_string($role->permissions) ? json_decode($role->permissions, true) : ($role->permissions ?? []); @endphp
                
                @foreach(\App\Helpers\RoleHelper::getPermissionGroups() as $group => $perms)
                <div class="mb-3">
                    <strong>{{ $group }}</strong>
                    <div class="row">
                        @foreach($perms as $perm)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" 
                                       value="{{ $perm }}" id="perm_{{ $perm }}"
                                       {{ in_array($perm, $currentPerms) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm_{{ $perm }}">
                                    {{ \App\Helpers\RoleHelper::getPermissionLabel($perm) }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
                
                <button type="submit" class="btn btn-primary">Update Role</button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
