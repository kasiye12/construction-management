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
                    <td><span class="badge bg-primary">{{ $user->role_label }}</span></td>
                    <td>{{ $user->department ?? 'N/A' }}</td>
                    <td><span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if(\App\Helpers\PermissionHelper::canView('users'))
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info"><i class="fas fa-eye"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canEdit('users'))
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
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
    <div class="card-footer">{{ $users->links() }}</div>
</div>
@endsection
