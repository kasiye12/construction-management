@extends('layouts.app')

@section('title', 'Fleet & Equipment Registry (EAMD)')

@section('content')
<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark fw-bold">
                <i class="fas fa-truck-monster text-primary me-2"></i> Fleet & Equipment Registry
            </h1>
            <p class="text-muted small mb-0">Equipment Administration & Maintenance Department (EAMD) • Master Fleet Assets</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pm-schedules.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-calendar-check me-1"></i> PM Schedules (OF/074)
            </a>
            <a href="{{ route('equipment-breakdowns.index') }}" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-tools me-1"></i> Breakdown Reports (OF/030)
            </a>
            <a href="{{ route('equipment-logs.index') }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-receipt me-1"></i> Spare Parts Log
            </a>
            <a href="{{ route('equipment.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Add Equipment
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-primary border-4">
                <div class="card-body p-3">
                    <span class="text-muted small text-uppercase fw-semibold">Total Fleet</span>
                    <h3 class="fw-bold mb-0 text-primary mt-1">{{ $totalCount }}</h3>
                    <small class="text-muted">Registered units</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-success border-4">
                <div class="card-body p-3">
                    <span class="text-muted small text-uppercase fw-semibold">Active & Operational</span>
                    <h3 class="fw-bold mb-0 text-success mt-1">{{ $activeCount }}</h3>
                    <small class="text-muted">Available on site</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-warning border-4">
                <div class="card-body p-3">
                    <span class="text-muted small text-uppercase fw-semibold">Service Due Soon</span>
                    <h3 class="fw-bold mb-0 text-warning mt-1">{{ $dueCount }}</h3>
                    <small class="text-muted">&le; 50 hr/km left</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-danger border-4">
                <div class="card-body p-3">
                    <span class="text-muted small text-uppercase fw-semibold">Service Overdue</span>
                    <h3 class="fw-bold mb-0 text-danger mt-1">{{ $overdueCount }}</h3>
                    <small class="text-danger fw-semibold">Exceeded schedule</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 border-start border-dark border-4">
                <div class="card-body p-3">
                    <span class="text-muted small text-uppercase fw-semibold">Down / Repair</span>
                    <h3 class="fw-bold mb-0 text-danger mt-1">{{ $downCount }}</h3>
                    <small class="text-muted">In maintenance/breakdown</small>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-light">
                <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                    <a href="{{ route('pm-schedules.download-template') }}" class="btn btn-outline-secondary btn-sm mb-1">
                        <i class="fas fa-file-excel me-1 text-success"></i> Excel Template
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('equipment.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Search Fleet</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Plate, Make, Model, Type..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $catKey => $catLabel)
                            <option value="{{ $catKey }}" {{ request('category') == $catKey ? 'selected' : '' }}>{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="due_service" {{ request('status') == 'due_service' ? 'selected' : '' }}>Due For Service</option>
                        <option value="under_maintenance" {{ request('status') == 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                        <option value="breakdown" {{ request('status') == 'breakdown' ? 'selected' : '' }}>Breakdown / Down</option>
                        <option value="idle" {{ request('status') == 'idle' ? 'selected' : '' }}>Idle</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Project Site</label>
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">All Locations / Projects</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'category', 'status', 'project_id']))
                        <a href="{{ route('equipment.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Equipment Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th>Equipment / Specification</th>
                        <th>Category</th>
                        <th>Location / Site</th>
                        <th class="text-end">Current Reading</th>
                        <th>Last Service</th>
                        <th>Next Service</th>
                        <th class="text-center">Remain</th>
                        <th class="text-center">Condition</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipments as $item)
                        @php
                            $remain = $item->remain_reading;
                            $svcStatus = $item->calculated_service_status;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle p-2 me-2 {{ $item->reading_unit == 'km' ? 'bg-info-subtle text-info' : 'bg-primary-subtle text-primary' }}">
                                        <i class="fas {{ $item->category == 'LIGHT VEHICLES' ? 'fa-car' : ($item->category == 'DUMP TRUCK' ? 'fa-truck' : 'fa-tractor') }}"></i>
                                    </div>
                                    <div>
                                        <a href="{{ route('equipment.show', $item) }}" class="fw-bold text-dark text-decoration-none">
                                            {{ $item->equipment_type }}
                                        </a>
                                        <div class="small text-muted">
                                            {{ $item->make }} {{ $item->model }} 
                                            @if($item->plate_serial_no)
                                                <span class="badge bg-secondary-subtle text-dark ms-1">{{ $item->plate_serial_no }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="small text-muted">{{ $item->category }}</span></td>
                            <td>
                                <span class="small">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                    {{ $item->location_name ?: ($item->project?->name ?: 'Central Workshop') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold">{{ number_format($item->current_reading, 1) }}</span>
                                <span class="text-muted small">{{ $item->reading_unit }}</span>
                                @if($item->current_reading_date)
                                    <div class="small text-muted">{{ $item->current_reading_date->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td>
                                @if($item->last_service_reading)
                                    <div>{{ number_format($item->last_service_reading, 1) }} {{ $item->reading_unit }}</div>
                                    <div class="small text-muted">Type: <strong>{{ $item->last_service_type ?: '-' }}</strong> {{ $item->last_service_date ? '• ' . $item->last_service_date->format('d/m/Y') : '' }}</div>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($item->next_service_reading)
                                    <div>{{ number_format($item->next_service_reading, 1) }} {{ $item->reading_unit }}</div>
                                    <div class="small text-muted">Type: <strong>{{ $item->next_service_type ?: '-' }}</strong></div>
                                @else
                                    <span class="text-muted small">Not Scheduled</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($remain !== null)
                                    <div class="fw-bold {{ $remain < 0 ? 'text-danger' : ($remain <= 50 ? 'text-warning' : 'text-success') }}">
                                        {{ $remain < 0 ? '+' . abs($remain) . ' overdue' : number_format($remain, 1) . ' ' . $item->reading_unit }}
                                    </div>
                                    <div>{!! $item->service_status_badge !!}</div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {!! $item->status_badge !!}
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateReadingModal{{ $item->id }}" title="Update Meter Reading">
                                        <i class="fas fa-tachometer-alt"></i>
                                    </button>
                                    <a href="{{ route('equipment.show', $item) }}" class="btn btn-outline-secondary" title="View Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('equipment.edit', $item) }}" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>

                                {{-- Quick Meter Reading Modal --}}
                                <div class="modal fade" id="updateReadingModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content text-start">
                                            <form action="{{ route('equipment.update-reading', $item) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-header">
                                                    <h5 class="modal-title h6">
                                                        <i class="fas fa-tachometer-alt text-primary me-2"></i> Update Reading: {{ $item->plate_serial_no }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Equipment</label>
                                                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $item->full_display_name }}" readonly>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label small fw-semibold">Current Reading ({{ $item->reading_unit }})</label>
                                                            <input type="number" step="0.1" name="current_reading" class="form-control form-control-sm" value="{{ $item->current_reading }}" required min="{{ (float)$item->current_reading }}">
                                                            <div class="form-text small">Previous: {{ number_format($item->current_reading, 1) }} {{ $item->reading_unit }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-semibold">Reading Date</label>
                                                            <input type="date" name="current_reading_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Location / Project Site</label>
                                                        <input type="text" name="location_name" class="form-control form-control-sm" value="{{ $item->location_name }}">
                                                    </div>
                                                    <div class="mb-0">
                                                        <label class="form-label small fw-semibold">Remarks</label>
                                                        <input type="text" name="remarks" class="form-control form-control-sm" placeholder="e.g. Daily shift reading, site transfer...">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary btn-sm">Save Reading</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-truck-monster fa-3x mb-3 text-secondary"></i>
                                <p class="mb-2">No equipment found matching criteria.</p>
                                <a href="{{ route('equipment.create') }}" class="btn btn-primary btn-sm">Add First Equipment</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($equipments->hasPages())
            <div class="card-footer bg-white py-2">
                {{ $equipments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
