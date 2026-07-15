@extends('layouts.app')

@section('title', 'Notifications - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>🔔 Notifications</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Notifications</li>
                </ol>
            </nav>
        </div>
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-check-double me-1"></i> Mark All Read
            </button>
        </form>
    </div>
</div>

<div class="table-card">
    @forelse($notifications as $notification)
    <div class="border-bottom p-3 d-flex align-items-center {{ $notification->is_read ? '' : 'bg-light' }}">
        <div class="me-3">
            <span class="badge bg-{{ $notification->color }} p-2 rounded-circle">
                <i class="fas fa-{{ $notification->icon }}"></i>
            </span>
        </div>
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between">
                <strong class="{{ $notification->is_read ? '' : 'text-dark' }}">
                    {{ $notification->title }}
                </strong>
                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
            </div>
            <p class="mb-1 text-muted">{{ $notification->message }}</p>
            @if($notification->link)
                <a href="{{ route('notifications.read', $notification) }}" class="btn btn-sm btn-link p-0">
                    View Details →
                </a>
            @endif
        </div>
        @if(!$notification->is_read)
            <span class="badge bg-primary ms-2">New</span>
        @endif
    </div>
    @empty
    <div class="text-center py-5">
        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
        <h5>No Notifications</h5>
        <p class="text-muted">You're all caught up!</p>
    </div>
    @endforelse
    
    {{ $notifications->links() }}
</div>
@endsection
