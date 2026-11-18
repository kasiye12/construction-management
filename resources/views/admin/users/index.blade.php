@extends('layouts.app')

@section('title', 'User Management - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h2>👥 Users</h2></div>
        @if(\App\Helpers\PermissionHelper::canCreate('users'))
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New User</a>
        @endif
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>User</th><th>Email</th><th>Role</th><th>Department</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge bg-primary">{{ $user->role_label }}</span>
                        @if($user->is_super_admin)
                            <span class="badge bg-danger" title="Super Admin"><i class="fas fa-crown"></i> Super</span>
                        @elseif($user->custom_permissions !== null)
                            <span class="badge bg-warning text-dark" title="Custom Permissions"><i class="fas fa-sliders-h"></i> Custom</span>
                        @endif
                    </td>
                    <td>{{ $user->department ?? 'N/A' }}</td>
                    <td><span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if(\App\Helpers\PermissionHelper::canView('users'))
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info" title="View Profile"><i class="fas fa-eye"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canEdit('users'))
                            <a href="{{ route('admin.users.permissions', $user) }}" class="btn btn-dark" title="Manage Permissions"><i class="fas fa-key"></i></a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning" title="Edit Profile"><i class="fas fa-edit"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canDelete('users') && $user->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer"><div class="d-flex justify-content-between align-items-center px-3 py-2">
                <div class="pagination-info">Showing {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results</div>
                {{ $users->links('vendor.pagination.custom') }}
            </div></div>
</div>
@endsection
