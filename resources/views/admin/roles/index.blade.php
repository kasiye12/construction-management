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
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-custom">
            <i class="fas fa-plus me-2"></i>New Role
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Role</th>
                    <th>Display Name</th>
                    <th>Users</th>
                    <th>Permissions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td><strong>{{ $role->name }}</strong></td>
                    <td>{{ $role->display_name }}</td>
                    <td>{{ $role->users_count }}</td>
                    <td>
                        @php $perms = is_string($role->permissions) ? json_decode($role->permissions, true) : ($role->permissions ?? []); @endphp
                        {{ count($perms) }} permissions
                    </td>
                    <td>
                        <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center">No roles found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
