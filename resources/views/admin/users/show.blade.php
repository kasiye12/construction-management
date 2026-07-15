@extends('layouts.app')

@section('title', $user->name . ' - User Details')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>👤 {{ $user->name }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </nav>
        </div>
        @if(auth()->user()->hasPermission('users.edit'))
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-custom">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card">
            <h5>User Information</h5><hr>
            <table class="table table-bordered">
                <tr><th width="30%">Name</th><td>{{ $user->name }}</td></tr>
                <tr><th>Username</th><td>{{ $user->username ?? 'N/A' }}</td></tr>
                <tr><th>Email</th><td>{{ $user->email }}</td></tr>
                <tr><th>Phone</th><td>{{ $user->phone ?? 'N/A' }}</td></tr>
                <tr><th>Role</th><td><span class="badge bg-primary">{{ $user->role_label }}</span></td></tr>
                <tr><th>Department</th><td>{{ $user->department ?? 'N/A' }}</td></tr>
                <tr><th>Position</th><td>{{ $user->position ?? 'N/A' }}</td></tr>
                <tr><th>Status</th><td>
                    <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td></tr>
                <tr><th>Last Login</th><td>{{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="table-card mb-3 text-center">
            <div class="user-avatar" style="width:80px;height:80px;border-radius:50%;background:#3949ab;color:white;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;font-weight:600;">
                {{ $user->initials }}
            </div>
            <h5 class="mt-3">{{ $user->name }}</h5>
            <p class="text-muted">{{ $user->role_label }}</p>
        </div>
        
        <div class="table-card">
            <h6>Recent Activity</h6><hr>
            @forelse($user->activityLogs as $log)
                <div class="mb-2">
                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small><br>
                    {{ $log->description }}
                </div>
            @empty
                <p class="text-muted">No activity recorded.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
