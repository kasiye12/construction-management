@extends('layouts.app')

@section('title', 'My Profile - CMS')

@section('content')
<div class="page-header">
    <h2>👤 My Profile</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Profile</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Profile Card -->
        <div class="table-card text-center mb-4">
            <div style="width:100px;height:100px;border-radius:50%;background:#3949ab;color:white;display:inline-flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:600;margin-bottom:15px;">
                {{ $user->initials }}
            </div>
            <h4>{{ $user->name }}</h4>
            <p class="text-muted">{{ $user->role_label }}</p>
            <p><strong>{{ $user->department ?? 'No Department' }}</strong></p>
            <p><small>{{ $user->email }}</small></p>
            <p><small>Last Login: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</small></p>
        </div>

        <!-- Change Password -->
        <div class="table-card">
            <h5>🔒 Change Password</h5><hr>
            <form action="{{ route('admin.profile.password') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Update Password</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Edit Profile -->
        <div class="table-card mb-4">
            <h5>✏️ Edit Profile</h5><hr>
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control" value="{{ old('department', $user->department) }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Position</label>
                    <input type="text" name="position" class="form-control" value="{{ old('position', $user->position) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Profile Picture</label>
                    <input type="file" name="avatar" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>

        <!-- Recent Activity -->
        <div class="table-card">
            <h5>📝 Recent Activity</h5><hr>
            @forelse($user->activityLogs ?? [] as $log)
                <div class="border-bottom py-2">
                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small><br>
                    <strong>{{ $log->action }}</strong> - {{ $log->description }}
                </div>
            @empty
                <p class="text-muted">No recent activity.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
