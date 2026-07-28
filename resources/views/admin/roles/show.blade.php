@extends('layouts.app')

@section('title', 'Role Details - CMS')

@section('content')
<div class="page-header">
    <h2>🛡️ Role: {{ $role->display_name }}</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
            <li class="breadcrumb-item active">{{ $role->display_name }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Role Information</h5></div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><td width="150"><strong>Name:</strong></td><td>{{ $role->name }}</td></tr>
                    <tr><td><strong>Display Name:</strong></td><td>{{ $role->display_name }}</td></tr>
                    <tr><td><strong>Description:</strong></td><td>{{ $role->description ?? 'N/A' }}</td></tr>
                    <tr><td><strong>Created:</strong></td><td>{{ $role->created_at->format('M d, Y H:i') }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Permissions ({{ count(is_string($role->permissions) ? json_decode($role->permissions, true) : ($role->permissions ?? [])) }})</h5></div>
            <div class="card-body">
                @php $perms = is_string($role->permissions) ? json_decode($role->permissions, true) : ($role->permissions ?? []); @endphp
                @if(count($perms) > 0)
                    <div class="row">
                        @foreach($perms as $perm)
                        <div class="col-md-4 mb-1">
                            <span class="badge bg-light text-dark border">{{ str_replace('.', ' → ', $perm) }}</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No permissions assigned.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Users ({{ $role->users->count() }})</h5></div>
            <div class="card-body p-0">
                @if($role->users->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($role->users as $user)
                    <div class="list-group-item d-flex align-items-center gap-2">
                        <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:white;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;">
                            {{ $user->initials }}
                        </div>
                        <div>
                            <strong>{{ $user->name }}</strong>
                            <br><small class="text-muted">{{ $user->email }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-3 text-muted text-center">No users assigned.</div>
                @endif
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning"><i class="fas fa-edit me-1"></i> Edit</a>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
