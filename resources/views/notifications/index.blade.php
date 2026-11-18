@extends('layouts.app')

@section('title', 'Notification & Document News Center - CMS')

@push('styles')
<style>
    .notif-header-banner {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
        border-radius: 16px;
        padding: 24px 28px;
        color: white;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(49, 46, 129, 0.15);
    }
    .stats-card-modern {
        background: white;
        border-radius: 14px;
        padding: 18px 20px;
        border: 1px solid #e2e8f0;
        transition: all 0.25s ease;
        height: 100%;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .stats-card-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    }
    .stats-card-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .doc-counter-card {
        background: white;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 18px;
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .doc-counter-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        border-color: #cbd5e1;
    }
    .doc-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .notification-card {
        border-left: 4px solid #e2e8f0;
        transition: all 0.2s ease;
        background: white;
        border-radius: 0 12px 12px 0;
        margin-bottom: 8px;
        border-top: 1px solid #f1f5f9;
        border-right: 1px solid #f1f5f9;
        border-bottom: 1px solid #f1f5f9;
    }
    .notification-card:hover {
        background: #f8fafc;
        transform: translateX(3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
    .notification-card.unread {
        border-left-color: #4f46e5;
        background: #f5f7ff;
    }
    .notification-card.unread:hover {
        background: #eef2ff;
    }
    .notif-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #4f46e5;
        display: inline-block;
        animation: pulseNotif 2s infinite;
    }
    @keyframes pulseNotif {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(1.15); }
    }
    .filter-chip-modern {
        padding: 7px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid #e2e8f0;
        background: white;
        transition: all 0.2s;
        text-decoration: none;
        color: #475569;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .filter-chip-modern:hover {
        border-color: #4f46e5;
        color: #4f46e5;
        background: #f8fafc;
    }
    .filter-chip-modern.active {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
        box-shadow: 0 2px 8px rgba(79,70,229,0.3);
    }
    .metric-pill {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Executive Header Banner --}}
    <div class="notif-header-banner">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-warning text-dark"><i class="fas fa-satellite-dish me-1"></i> Live Operational Hub</span>
                    <span class="badge bg-light text-dark">{{ auth()->user()->role_label }}</span>
                </div>
                <h3 class="mb-1 text-white">🔔 Notification & Document News Center</h3>
                <p class="mb-0 opacity-75 small">Real-time status tracking, document pending action alerts, and engineering workflow news.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <form action="{{ route('notifications.sync-news') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm fw-bold shadow-sm text-dark">
                        <i class="fas fa-sync-alt me-1"></i> Sync & Refresh News
                    </button>
                </form>
                @if($stats['unread'] > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-light btn-sm text-primary fw-bold shadow-sm">
                        <i class="fas fa-check-double me-1"></i> Mark All Read
                    </button>
                </form>
                @endif
                @if($stats['read'] > 0)
                <form action="{{ route('notifications.delete-all-read') }}" method="POST" class="d-inline" onsubmit="return confirm('Clear all read notifications?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-light btn-sm opacity-75">
                        <i class="fas fa-trash me-1"></i> Clear Read
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Top Operational Metrics Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card-modern">
                <div class="stats-card-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold text-dark">{{ $stats['unread'] }}</h3>
                    <div class="text-muted small text-uppercase fw-semibold" style="letter-spacing:0.5px;">Unread News Alerts</div>
                    <small class="text-primary">{{ $stats['total'] }} total received</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card-modern">
                <div class="stats-card-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold text-dark">{{ $stats['action_required'] }}</h3>
                    <div class="text-muted small text-uppercase fw-semibold" style="letter-spacing:0.5px;">Pending Documents</div>
                    <small class="text-warning">Requiring review or check</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card-modern">
                <div class="stats-card-icon bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold text-dark">{{ $stats['urgent_alerts'] }}</h3>
                    <div class="text-muted small text-uppercase fw-semibold" style="letter-spacing:0.5px;">Urgent Equipment Alerts</div>
                    <small class="text-danger">Overdue PM & down units</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card-modern">
                <div class="stats-card-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold text-dark">{{ $stats['total_monitored'] }}</h3>
                    <div class="text-muted small text-uppercase fw-semibold" style="letter-spacing:0.5px;">Monitored Records</div>
                    <small class="text-success">Across authorized modules</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Live Document Status & News Counters Grid --}}
    @if(!empty($docStats['categories']))
    <div class="card mb-4 border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-clipboard-list text-primary me-2"></i> Live Document Status & Action Counters</h5>
                <small class="text-muted">Real-time counts for all necessary project documents filtered by your active role permissions</small>
            </div>
            <span class="badge bg-light text-muted border px-2 py-1">Auto-Updated Live</span>
        </div>
        <div class="card-body pt-0">
            <div class="row g-3">
                @foreach($docStats['categories'] as $cat)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="doc-counter-card">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="doc-icon-wrap bg-{{ $cat['color'] }} bg-opacity-10 text-{{ $cat['color'] }}">
                                    <i class="{{ $cat['icon'] }}"></i>
                                </div>
                                <span class="badge bg-{{ $cat['status_color'] }} text-{{ $cat['status_color'] === 'warning' ? 'dark' : 'white' }} small">
                                    {{ $cat['status_summary'] }}
                                </span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">{{ $cat['title'] }}</h6>
                            <p class="text-muted small mb-3">{{ $cat['subtitle'] }}</p>

                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <span class="text-muted small">Total Registered:</span>
                                <span class="fw-bold fs-6">{{ $cat['total'] }}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <span class="text-muted small">Pending Action:</span>
                                @if($cat['pending'] > 0)
                                    <span class="badge bg-warning text-dark fw-bold">{{ $cat['pending'] }} Pending</span>
                                @else
                                    <span class="badge bg-light text-muted">0 Pending</span>
                                @endif
                            </div>

                            @if(isset($cat['extra_label']))
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted small">{{ $cat['extra_label'] }}:</span>
                                <span class="fw-semibold text-secondary small">{{ $cat['extra_value'] }}</span>
                            </div>
                            @endif
                        </div>

                        <div>
                            <a href="{{ $cat['route'] }}" class="btn btn-sm btn-outline-{{ $cat['color'] }} w-100 fw-semibold">
                                View Registry <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Filter Chips Navigation --}}
    <div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
        <span class="text-muted small fw-bold me-1"><i class="fas fa-filter me-1"></i> Filter News:</span>
        <a href="{{ route('notifications.index') }}" class="filter-chip-modern {{ !request('filter') ? 'active' : '' }}">
            <i class="fas fa-inbox"></i> All News
            <span class="badge bg-white text-dark ms-1">{{ $stats['total'] }}</span>
        </a>
        <a href="?filter=unread" class="filter-chip-modern {{ request('filter') == 'unread' ? 'active' : '' }}">
            <i class="fas fa-envelope"></i> Unread
            @if($stats['unread'] > 0)<span class="badge bg-danger text-white ms-1">{{ $stats['unread'] }}</span>@endif
        </a>

        @if(auth()->user()->hasPermission('contractual-reports.view'))
        <a href="?filter=cad01" class="filter-chip-modern {{ request('filter') == 'cad01' ? 'active' : '' }}">
            <i class="fas fa-file-contract text-danger"></i> Contractual (CAD-01)
            @if(($filterCounts['cad01'] ?? 0) > 0)<span class="badge bg-secondary ms-1">{{ $filterCounts['cad01'] }}</span>@endif
        </a>
        @endif

        @if(auth()->user()->hasPermission('ipc.view'))
        <a href="?filter=ipc" class="filter-chip-modern {{ request('filter') == 'ipc' ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar text-success"></i> Payment (IPCs)
            @if(($filterCounts['ipc'] ?? 0) > 0)<span class="badge bg-secondary ms-1">{{ $filterCounts['ipc'] }}</span>@endif
        </a>
        @endif

        @if(auth()->user()->hasPermission('pm-schedules.view') || auth()->user()->hasPermission('equipment-breakdowns.view'))
        <a href="?filter=equipment" class="filter-chip-modern {{ request('filter') == 'equipment' ? 'active' : '' }}">
            <i class="fas fa-tools text-warning"></i> Fleet & Maintenance
            @if(($filterCounts['equipment'] ?? 0) > 0)<span class="badge bg-secondary ms-1">{{ $filterCounts['equipment'] }}</span>@endif
        </a>
        @endif

        @if(auth()->user()->hasPermission('deliveries.view'))
        <a href="?filter=deliveries" class="filter-chip-modern {{ request('filter') == 'deliveries' ? 'active' : '' }}">
            <i class="fas fa-truck-loading text-info"></i> Deliveries
            @if(($filterCounts['deliveries'] ?? 0) > 0)<span class="badge bg-secondary ms-1">{{ $filterCounts['deliveries'] }}</span>@endif
        </a>
        @endif

        @if(auth()->user()->hasPermission('takeoff.view'))
        <a href="?filter=takeoff" class="filter-chip-modern {{ request('filter') == 'takeoff' ? 'active' : '' }}">
            <i class="fas fa-ruler-combined text-primary"></i> Takeoff (QTO)
            @if(($filterCounts['takeoff'] ?? 0) > 0)<span class="badge bg-secondary ms-1">{{ $filterCounts['takeoff'] }}</span>@endif
        </a>
        @endif

        @if(auth()->user()->hasPermission('projects.view'))
        <a href="?filter=projects" class="filter-chip-modern {{ request('filter') == 'projects' ? 'active' : '' }}">
            <i class="fas fa-hard-hat text-secondary"></i> Projects & Deadlines
            @if(($filterCounts['projects'] ?? 0) > 0)<span class="badge bg-secondary ms-1">{{ $filterCounts['projects'] }}</span>@endif
        </a>
        @endif
    </div>

    {{-- Notifications News List --}}
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-3">
            @forelse($notifications as $notification)
            <div class="notification-card {{ $notification->is_read ? '' : 'unread' }} p-3" 
                 onclick="window.location='{{ route('notifications.read', $notification) }}'" style="cursor:pointer;">
                <div class="d-flex align-items-start gap-3">
                    <!-- Icon Box -->
                    <div class="doc-icon-wrap bg-{{ $notification->color }} bg-opacity-10 text-{{ $notification->color }}">
                        <i class="fas fa-{{ $notification->icon }}"></i>
                    </div>
                    
                    <!-- Content Area -->
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-1">
                            <div>
                                <strong class="{{ $notification->is_read ? 'text-secondary' : 'text-dark fw-bold' }} fs-6">
                                    {{ $notification->title }}
                                </strong>
                                @if(!$notification->is_read)
                                    <span class="notif-dot ms-2" title="Unread News Alert"></span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-muted border">
                                    <i class="far fa-clock me-1"></i> {{ $notification->created_at->diffForHumans() }}
                                </span>
                                <form action="{{ route('notifications.delete', $notification) }}" method="POST" class="d-inline" 
                                      onclick="event.stopPropagation();" onsubmit="return confirm('Delete this notification?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-link text-muted p-0 ms-1" title="Delete Notification">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <p class="text-muted mb-2 small">{{ $notification->message }}</p>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-1 border-top">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-{{ $notification->color }} bg-opacity-10 text-{{ $notification->color }} small">
                                    {{ strtoupper(str_replace('_', ' ', $notification->type)) }}
                                </span>
                                @if($notification->link)
                                    <span class="text-primary small fw-semibold"><i class="fas fa-external-link-alt me-1"></i> Open Record</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                {{ $notification->created_at->format('M d, Y • h:i A') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <div class="mb-3">
                    <span class="rounded-circle bg-light p-3 d-inline-flex align-items-center justify-content-center text-muted" style="width:72px;height:72px;">
                        <i class="fas fa-bell-slash fa-2x text-muted opacity-50"></i>
                    </span>
                </div>
                <h5 class="fw-bold text-dark">No Notifications Found</h5>
                <p class="text-muted small mb-3">
                    @if(request('filter'))
                        No document news matches the selected filter category.
                    @else
                        You are all caught up! No active news updates require your attention.
                    @endif
                </p>
                <div class="d-flex justify-content-center gap-2">
                    @if(request('filter'))
                        <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">View All News</a>
                    @endif
                    <form action="{{ route('notifications.sync-news') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-sync-alt me-1"></i> Check Document Status
                        </button>
                    </form>
                </div>
            </div>
            @endforelse

            @if($notifications->hasPages())
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-2">
                    <div class="text-muted small">Showing {{ $notifications->firstItem() ?? 0 }} - {{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }} updates</div>
                    {{ $notifications->links('vendor.pagination.custom') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
