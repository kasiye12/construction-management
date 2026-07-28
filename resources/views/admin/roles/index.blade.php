@extends('layouts.app')

@section('title', 'Role Management - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>🛡️ Role Management</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Roles</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Role
        </a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Role</th>
                    <th>Display Name</th>
                    <th>Users</th>
                    <th>Permissions</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td>
                        <span class="fw-bold">{{ $role->name }}</span>
                        @if($role->name === 'admin')
                            <span class="badge bg-danger ms-1">System</span>
                        @endif
                    </td>
                    <td>{{ $role->display_name }}</td>
                    <td>
                        <span class="badge bg-info">{{ $role->users_count }}</span>
                    </td>
                    <td>
                        @php $perms = is_string($role->permissions) ? json_decode($role->permissions, true) : ($role->permissions ?? []); @endphp
                        <span class="badge bg-secondary">{{ count($perms) }} permissions</span>
                    </td>
                    <td>{{ $role->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                            @if($role->name !== 'admin' && $role->users_count == 0)
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4">No roles found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
