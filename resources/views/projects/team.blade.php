@extends('layouts.app')

@section('title', 'Project Team - ' . $project->name)

@push('styles')
<style>
    .team-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 16px;
        transition: all 0.2s;
    }
    .team-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
    .member-avatar {
        width: 48px; height: 48px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; font-weight: 700;
    }
    .role-badge {
        padding: 3px 10px; border-radius: 12px;
        font-size: 0.7rem; font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>👥 Project Team</h2>
            <p class="text-muted">
                Project: <strong>{{ $project->name }}</strong> | 
                Members: <strong>{{ $teamMembers->count() }}</strong>
            </p>
        </div>
        <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Project
        </a>
    </div>
</div>

<div class="row">
    <!-- Current Team Members -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">✅ Assigned Team Members ({{ $teamMembers->count() }})</h5>
            </div>
            <div class="card-body">
                @if($teamMembers->count() > 0)
                    @php $groupedMembers = $teamMembers->groupBy('pivot.role'); @endphp
                    
                    @foreach($roles as $roleKey => $roleLabel)
                        @if(isset($groupedMembers[$roleKey]))
                        <div class="mb-4">
                            <h6 class="text-primary mb-2">{{ $roleLabel }}</h6>
                            <div class="row g-3">
                                @foreach($groupedMembers[$roleKey] as $member)
                                <div class="col-md-6">
                                    <div class="team-card d-flex align-items-center gap-3">
                                        <div class="member-avatar bg-primary text-white">
                                            {{ $member->initials }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong>{{ $member->name }}</strong>
                                            <br><small class="text-muted">{{ $member->email }}</small>
                                            @if($member->pivot->responsibilities)
                                                <br><small class="text-info">📋 {{ $member->pivot->responsibilities }}</small>
                                            @endif
                                            <br><small class="text-muted">📅 Assigned: {{ $member->pivot->assigned_date ? date('M d, Y', strtotime($member->pivot->assigned_date)) : 'N/A' }}</small>
                                        </div>
                                        <div>
                                            @if($member->pivot->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                            <form action="{{ route('projects.team.remove', [$project, $member]) }}" method="POST" class="mt-1" onsubmit="return confirm('Remove this member?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-user-minus"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5>No Team Members</h5>
                        <p class="text-muted">Assign team members to this project from the form.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Assign New Member -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">➕ Assign Team Member</h5></div>
            <div class="card-body">
                @if($availableUsers->count() > 0)
                <form action="{{ route('projects.team.assign', $project) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">User <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Select User --</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role_label }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            @foreach($roles as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Responsibilities</label>
                        <textarea name="responsibilities" class="form-control" rows="2" placeholder="e.g., Supervise foundation works"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-user-plus me-1"></i> Assign to Project
                    </button>
                </form>
                @else
                    <p class="text-muted text-center">All active users are assigned.</p>
                @endif
            </div>
        </div>

        <!-- Quick Info -->
        <div class="card">
            <div class="card-header"><h5 class="mb-0">ℹ️ Team Roles</h5></div>
            <div class="card-body p-0">
                @foreach($roles as $key => $label)
                <div class="border-bottom p-2">
                    <span class="role-badge bg-{{ $key == 'project_manager' ? 'primary' : ($key == 'site_engineer' ? 'success' : 'secondary') }}">
                        {{ $key }}
                    </span>
                    <br><small class="text-muted">{{ $label }}</small>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
