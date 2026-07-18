@extends('layouts.app')

@section('title', 'Notifications - CMS')

@push('styles')
<style>
    .notification-card {
        border-left: 4px solid #e5e7eb;
        transition: all 0.2s;
        cursor: pointer;
        background: white;
        border-radius: 0 8px 8px 0;
        margin-bottom: 2px;
    }
    .notification-card:hover { background: #f9fafb; transform: translateX(2px); }
    .notification-card.unread { border-left-color: #4f46e5; background: #eef2ff; }
    .notification-card.unread:hover { background: #e0e7ff; }
    .notification-card .notif-icon {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
    }
    .notif-dot {
        width: 10px; height: 10px; border-radius: 50%;
        background: #4f46e5; display: inline-block;
        animation: pulse 2s infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.5} }
    
    .filter-chip {
        padding: 6px 14px; border-radius: 20px;
        font-size: 0.75rem; font-weight: 500; cursor: pointer;
        border: 1px solid #e5e7eb; background: white;
        transition: all 0.2s; text-decoration: none; color: #374151;
        display: inline-block;
    }
    .filter-chip:hover { border-color: #4f46e5; color: #4f46e5; }
    .filter-chip.active { background: #4f46e5; color: white; border-color: #4f46e5; }
    
    .stats-card {
        background: white; border-radius: 12px; padding: 16px;
        text-align: center; border: 1px solid #e5e7eb;
        transition: all 0.2s;
    }
    .stats-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2>🔔 Notifications</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Notifications</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @if($stats['unread'] > 0)
            <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-check-double me-1"></i> Mark All Read
                </button>
            </form>
            @endif
            @if($stats['read'] > 0)
            <form action="{{ route('notifications.delete-all-read') }}" method="POST" class="d-inline" onsubmit="return confirm('Delete all read notifications?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-trash me-1"></i> Clear Read
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h2 class="text-primary mb-0">{{ $stats['total'] }}</h2>
            <small class="text-muted">Total Notifications</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background:#eef2ff;">
            <h2 class="text-primary mb-0">{{ $stats['unread'] }}</h2>
            <small class="text-muted">Unread</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="background:#f0fdf4;">
            <h2 class="text-success mb-0">{{ $stats['read'] }}</h2>
            <small class="text-muted">Read</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h2 class="text-info mb-0">{{ $stats['this_week'] }}</h2>
            <small class="text-muted">This Week</small>
        </div>
    </div>
</div>

<!-- Filter Chips -->
<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="{{ route('notifications.index') }}" class="filter-chip {{ !request('filter') ? 'active' : '' }}">
        <i class="fas fa-inbox me-1"></i> All
    </a>
    <a href="?filter=unread" class="filter-chip {{ request('filter') == 'unread' ? 'active' : '' }}">
        <i class="fas fa-envelope me-1"></i> Unread
        @if($stats['unread'] > 0)<span class="badge bg-danger ms-1">{{ $stats['unread'] }}</span>@endif
    </a>
    <a href="?filter=ipc_submitted" class="filter-chip {{ request('filter') == 'ipc_submitted' ? 'active' : '' }}">
        <i class="fas fa-file-invoice me-1"></i> IPC Submitted
    </a>
    <a href="?filter=ipc_approved" class="filter-chip {{ request('filter') == 'ipc_approved' ? 'active' : '' }}">
        <i class="fas fa-check-circle me-1"></i> IPC Approved
    </a>
    <a href="?filter=ipc_rejected" class="filter-chip {{ request('filter') == 'ipc_rejected' ? 'active' : '' }}">
        <i class="fas fa-times-circle me-1"></i> IPC Rejected
    </a>
    <a href="?filter=budget_exceeded" class="filter-chip {{ request('filter') == 'budget_exceeded' ? 'active' : '' }}">
        <i class="fas fa-exclamation-triangle me-1"></i> Budget Alerts
    </a>
    <a href="?filter=deadline" class="filter-chip {{ request('filter') == 'deadline' ? 'active' : '' }}">
        <i class="fas fa-clock me-1"></i> Deadlines
    </a>
</div>

<!-- Notifications List -->
@forelse($notifications as $notification)
<div class="card notification-card {{ $notification->is_read ? '' : 'unread' }} mb-2" 
     onclick="window.location='{{ route('notifications.read', $notification) }}'">
    <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3">
            <!-- Icon -->
            <div class="notif-icon bg-{{ $notification->color }} bg-opacity-10 text-{{ $notification->color }}">
                <i class="fas fa-{{ $notification->icon }}"></i>
            </div>
            
            <!-- Content -->
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong class="{{ $notification->is_read ? '' : 'text-dark' }}">
                            {{ $notification->title }}
                        </strong>
                        @if(!$notification->is_read)
                            <span class="notif-dot ms-2"></span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted" style="white-space:nowrap;">
                            {{ $notification->created_at->diffForHumans() }}
                        </small>
                        <form action="{{ route('notifications.delete', $notification) }}" method="POST" class="d-inline" 
                              onclick="event.stopPropagation();" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm text-muted" style="padding:0;border:none;background:none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <p class="text-muted mb-1 small">{{ $notification->message }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-{{ $notification->color }} bg-opacity-10 text-{{ $notification->color }} small">
                        {{ strtoupper(str_replace('_', ' ', $notification->type)) }}
                    </span>
                    <small class="text-muted">
                        {{ $notification->created_at->format('M d, Y h:i A') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@empty
<div class="text-center py-5">
    <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
    <h4>No Notifications</h4>
    <p class="text-muted">
        @if(request('filter'))
            No notifications match this filter.
            <br><a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary mt-2">View All</a>
        @else
            You're all caught up! 🎉
        @endif
    </p>
</div>
@endforelse

<div class="mt-3">
    <div class="d-flex justify-content-between align-items-center px-3 py-2">
                <div class="pagination-info">Showing {{ $notifications->firstItem() ?? 0 }} - {{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }} results</div>
                {{ $notifications->links('vendor.pagination.custom') }}
            </div>
</div>
@endsection
