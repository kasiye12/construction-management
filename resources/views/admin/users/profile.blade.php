@extends('layouts.app')

@section('title', 'My Profile - CMS')

@push('styles')
<style>
    .profile-cover {
        background: linear-gradient(135deg, #1a237e, #3949ab);
        height: 140px;
        border-radius: 16px 16px 0 0;
        position: relative;
    }
    .profile-avatar-container {
        position: relative;
        margin-top: -60px;
        text-align: center;
    }
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        object-fit: cover;
        background: #3949ab;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 700;
        color: white;
        overflow: hidden;
    }
    .profile-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }
    .avatar-upload-btn {
        position: absolute;
        bottom: 5px;
        right: calc(50% - 70px);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #4f46e5;
        color: white;
        border: 2px solid white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.8rem;
        transition: all 0.2s;
    }
    .avatar-upload-btn:hover { background: #3730a3; transform: scale(1.1); }
    .profile-card {
        background: white;
        border-radius: 0 0 16px 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        padding: 30px 20px 20px;
        text-align: center;
        margin-bottom: 20px;
    }
    .profile-card h4 { font-weight: 700; color: #1a237e; margin-bottom: 2px; }
    .profile-card .role-badge {
        display: inline-block;
        padding: 4px 16px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .info-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .info-item:last-child { border-bottom: none; }
    .info-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem;
    }
    .nav-tabs .nav-link {
        border: none;
        color: #6b7280;
        font-weight: 500;
        padding: 10px 20px;
        border-radius: 8px 8px 0 0;
    }
    .nav-tabs .nav-link.active {
        color: #4f46e5;
        border-bottom: 2px solid #4f46e5;
        background: transparent;
    }
    .activity-timeline { position: relative; padding-left: 25px; }
    .activity-timeline::before {
        content: ''; position: absolute; left: 8px; top: 0; bottom: 0;
        width: 2px; background: #e5e7eb;
    }
    .activity-item {
        position: relative;
        padding-bottom: 20px;
    }
    .activity-item::before {
        content: ''; position: absolute; left: -21px; top: 4px;
        width: 12px; height: 12px; border-radius: 50%;
        background: #4f46e5; border: 2px solid white;
    }
</style>
@endpush

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
    <!-- Left Sidebar -->
    <div class="col-md-4">
        <!-- Profile Card -->
        <div class="profile-cover"></div>
        <div class="profile-avatar-container">
            <div class="profile-avatar" id="avatarPreview">
                @php
                    $avatarUrl = null;
                    if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                        $avatarUrl = asset('storage/' . $user->avatar);
                    }
                @endphp
                @if($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="Profile Photo">
                @else
                    {{ $user->initials }}
                @endif
            </div>
            <label class="avatar-upload-btn" for="avatarInput" title="Change Photo">
                <i class="fas fa-camera"></i>
            </label>
        </div>
        
        <div class="profile-card">
            <h4>{{ $user->name }}</h4>
            <p class="text-muted mb-1">{{ $user->position ?? 'No Position' }}</p>
            <span class="role-badge bg-{{ $user->getRoleName() == 'admin' ? 'danger' : ($user->getRoleName() == 'manager' ? 'primary' : 'success') }} text-white">
                {{ $user->role_label }}
            </span>
            
            <div class="mt-4 text-start">
                <div class="info-item">
                    <div class="info-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-envelope"></i></div>
                    <div><small class="text-muted">Email</small><br><strong>{{ $user->email }}</strong></div>
                </div>
                <div class="info-item">
                    <div class="info-icon bg-success bg-opacity-10 text-success"><i class="fas fa-phone"></i></div>
                    <div><small class="text-muted">Phone</small><br><strong>{{ $user->phone ?? 'Not set' }}</strong></div>
                </div>
                <div class="info-item">
                    <div class="info-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-building"></i></div>
                    <div><small class="text-muted">Department</small><br><strong>{{ $user->department ?? 'Not set' }}</strong></div>
                </div>
                <div class="info-item">
                    <div class="info-icon bg-info bg-opacity-10 text-info"><i class="fas fa-clock"></i></div>
                    <div><small class="text-muted">Last Login</small><br><strong>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</strong></div>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <div class="card-header"><h6 class="mb-0">🔒 Change Password</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.profile.password') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-2">
                        <label class="form-label small">Current Password</label>
                        <input type="password" name="current_password" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">New Password</label>
                        <input type="password" name="password" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-sm" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Update Password</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Content -->
    <div class="col-md-8">
        <ul class="nav nav-tabs mb-3" id="profileTabs">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#editProfile">✏️ Edit Profile</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#activity">📝 Recent Activity</a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="editProfile">
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">Edit Profile Information</h6></div>
                    <div class="card-body">
                        <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
                            @csrf @method('PUT')
                            <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/*" onchange="previewAvatar(this)">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
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
                                    <label class="form-label">Position</label>
                                    <input type="text" name="position" class="form-control" value="{{ old('position', $user->position) }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" class="form-control" value="{{ old('department', $user->department) }}">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="activity">
                <div class="card">
                    <div class="card-header"><h6 class="mb-0">Recent Activity</h6></div>
                    <div class="card-body">
                        @if($user->activityLogs && $user->activityLogs->count() > 0)
                        <div class="activity-timeline">
                            @foreach($user->activityLogs->take(20) as $log)
                            <div class="activity-item">
                                <strong>{{ ucfirst($log->action) }}</strong>
                                <span class="text-muted"> - {{ $log->description }}</span>
                                <br><small class="text-muted"><i class="far fa-clock me-1"></i> {{ $log->created_at->format('M d, Y h:i A') }}</small>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-muted text-center py-3">No recent activity.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">';
        }
        reader.readAsDataURL(input.files[0]);
        // Auto-submit form for immediate upload
        document.getElementById('profileForm').submit();
    }
}
</script>
@endpush
