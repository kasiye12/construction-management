@extends('layouts.app')

@section('title', 'Equipment: ' . $equipment->full_display_name)

@section('content')
<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('equipment.index') }}" class="btn btn-outline-secondary btn-sm" title="Back to Fleet">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="h3 mb-0 text-dark fw-bold">{{ $equipment->full_display_name }}</h1>
                {!! $equipment->status_badge !!}
                {!! $equipment->service_status_badge !!}
            </div>
            <p class="text-muted small mb-0">
                Category: <strong>{{ $equipment->category }}</strong> • 
                Location: <strong><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $equipment->location_name ?: ($equipment->project?->name ?: 'Central Workshop') }}</strong>
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updateReadingModal">
                <i class="fas fa-tachometer-alt me-1"></i> Update Meter Reading
            </button>
            <a href="{{ route('equipment-logs.create', ['equipment_id' => $equipment->id]) }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-plus me-1"></i> Log Spare Part / Cost
            </a>
            <a href="{{ route('equipment.edit', $equipment) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small text-uppercase fw-semibold">Current Meter Reading</span>
                    <h3 class="fw-bold mb-1 text-primary mt-1">
                        {{ number_format($equipment->current_reading, 1) }}
                        <span class="fs-6 text-muted">{{ $equipment->reading_unit }}</span>
                    </h3>
                    <small class="text-muted">
                        Updated: {{ $equipment->current_reading_date ? $equipment->current_reading_date->format('d/m/Y') : 'Not recorded' }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small text-uppercase fw-semibold">Next Scheduled Service</span>
                    <h3 class="fw-bold mb-1 {{ $equipment->calculated_service_status == 'Overdue' ? 'text-danger' : ($equipment->calculated_service_status == 'Due' ? 'text-warning' : 'text-success') }} mt-1">
                        @if($equipment->next_service_reading)
                            {{ number_format($equipment->next_service_reading, 1) }}
                            <span class="fs-6 text-muted">{{ $equipment->reading_unit }} (Type {{ $equipment->next_service_type ?: '-' }})</span>
                        @else
                            <span class="fs-5 text-muted">Not Set</span>
                        @endif
                    </h3>
                    <small class="text-muted">
                        @php $rem = $equipment->remain_reading; @endphp
                        @if($rem !== null)
                            Remain: <strong>{{ number_format($rem, 1) }} {{ $equipment->reading_unit }}</strong>
                        @else
                            -
                        @endif
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small text-uppercase fw-semibold">Last Service Done</span>
                    <h3 class="fw-bold mb-1 text-secondary mt-1">
                        @if($equipment->last_service_reading)
                            {{ number_format($equipment->last_service_reading, 1) }}
                            <span class="fs-6 text-muted">{{ $equipment->reading_unit }}</span>
                        @else
                            <span class="fs-5 text-muted">-</span>
                        @endif
                    </h3>
                    <small class="text-muted">
                        Type: {{ $equipment->last_service_type ?: '-' }} • Date: {{ $equipment->last_service_date ? $equipment->last_service_date->format('d/m/Y') : '-' }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3">
                    <span class="text-muted small text-uppercase fw-semibold">Total Maintenance Spend</span>
                    <h3 class="fw-bold mb-1 text-dark mt-1">
                        {{ number_format($totalSpent, 2) }}
                        <span class="fs-6 text-muted">ETB</span>
                    </h3>
                    <small class="text-muted">{{ $maintenanceLogs->count() }} spare part / repair transactions</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Specs Card --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-info-circle text-primary me-2"></i> Asset Technical Specifications</h6>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <small class="text-muted d-block">Plate / Serial Number</small>
                    <span class="fw-bold">{{ $equipment->plate_serial_no ?: '-' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Make / Manufacturer</small>
                    <span class="fw-semibold">{{ $equipment->make ?: '-' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Model</small>
                    <span class="fw-semibold">{{ $equipment->model ?: '-' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">VIN / Chassis / Engine SN</small>
                    <span class="text-muted">{{ $equipment->serial_no ?: '-' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Assigned Project</small>
                    <span>{{ $equipment->project?->name ?: 'Central Fleet Pool' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Location Name</small>
                    <span>{{ $equipment->location_name ?: 'Central Workshop' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Reading Unit</small>
                    <span>{{ $equipment->reading_unit == 'km' ? 'Kilometers (km)' : 'Engine Hours (hr)' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">System Code</small>
                    <span class="badge bg-light text-dark border">{{ $equipment->code }}</span>
                </div>
                @if($equipment->remarks)
                    <div class="col-12">
                        <small class="text-muted d-block">Remarks</small>
                        <p class="small text-muted mb-0 bg-light p-2 rounded">{{ $equipment->remarks }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabs for PM History, Breakdowns, Spare Parts --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs card-header-tabs m-0 border-0" id="equipmentTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active px-4 py-3 fw-semibold border-0 border-bottom border-primary border-3" id="pm-tab" data-bs-toggle="tab" data-bs-target="#pm" type="button" role="tab">
                        <i class="fas fa-calendar-check me-2 text-primary"></i> PM Schedules (OF/074)
                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $pmItems->count() }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link px-4 py-3 fw-semibold border-0" id="breakdown-tab" data-bs-toggle="tab" data-bs-target="#breakdown" type="button" role="tab">
                        <i class="fas fa-tools me-2 text-danger"></i> Breakdown & Repair History (OF/030)
                        <span class="badge bg-danger-subtle text-danger ms-1">{{ $breakdowns->count() }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link px-4 py-3 fw-semibold border-0" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab">
                        <i class="fas fa-receipt me-2 text-success"></i> Spare Parts & Cost Log
                        <span class="badge bg-success-subtle text-success ms-1">{{ $maintenanceLogs->count() }}</span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-3">
            <div class="tab-content" id="equipmentTabsContent">
                {{-- PM Schedules Tab --}}
                <div class="tab-pane fade show active" id="pm" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase small text-muted">
                                <tr>
                                    <th>Schedule Code</th>
                                    <th>Period / Date</th>
                                    <th>Service Type</th>
                                    <th>Target Reading</th>
                                    <th>Current Reading</th>
                                    <th>Remain</th>
                                    <th>Service Status</th>
                                    <th>Schedule Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pmItems as $pmi)
                                    <tr>
                                        <td>
                                            <a href="{{ route('pm-schedules.show', $pmi->pm_schedule_id) }}" class="fw-bold text-decoration-none">
                                                {{ $pmi->schedule?->schedule_code }}
                                            </a>
                                        </td>
                                        <td>{{ $pmi->schedule?->schedule_date ? $pmi->schedule->schedule_date->format('d/m/Y') : '-' }}</td>
                                        <td><strong>{{ $pmi->next_service_type ?: '-' }}</strong></td>
                                        <td>{{ number_format($pmi->next_service_reading, 1) }}</td>
                                        <td>{{ number_format($pmi->current_reading, 1) }}</td>
                                        <td>
                                            <span class="fw-bold {{ $pmi->remain_reading < 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($pmi->remain_reading, 1) }}
                                            </span>
                                        </td>
                                        <td>{!! $pmi->service_status_badge !!}</td>
                                        <td>{!! $pmi->schedule?->status_badge !!}</td>
                                        <td class="text-end">
                                            <a href="{{ route('pm-schedules.show', $pmi->pm_schedule_id) }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-eye"></i> View Schedule
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">No PM schedule entries recorded yet for this equipment.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Breakdowns Tab --}}
                <div class="tab-pane fade" id="breakdown" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase small text-muted">
                                <tr>
                                    <th>Report Code</th>
                                    <th>Down Date</th>
                                    <th>Reason of Down</th>
                                    <th>Action Taken</th>
                                    <th>Location</th>
                                    <th>Repair Status</th>
                                    <th>Finished Date</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($breakdowns as $b)
                                    <tr>
                                        <td>
                                            <a href="{{ route('equipment-breakdowns.show', $b->breakdown_report_id) }}" class="fw-bold text-decoration-none">
                                                {{ $b->report?->report_code }}
                                            </a>
                                        </td>
                                        <td>{{ $b->down_date ? $b->down_date->format('d/m/Y') : '-' }}</td>
                                        <td class="fw-semibold text-danger">{{ $b->reason_of_down }}</td>
                                        <td>{{ $b->action_taken }}</td>
                                        <td>{{ $b->location }}</td>
                                        <td>{!! $b->repair_status_badge !!}</td>
                                        <td>{{ $b->finished_date ? $b->finished_date->format('d/m/Y') : 'In progress' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('equipment-breakdowns.show', $b->breakdown_report_id) }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-eye"></i> View Report
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No breakdowns recorded for this equipment. Operating reliably!</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Spare Parts & Cost Tab --}}
                <div class="tab-pane fade" id="logs" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase small text-muted">
                                <tr>
                                    <th>Date</th>
                                    <th>Spare Part / Work Description</th>
                                    <th>PO / Ref No</th>
                                    <th>Qty & Unit</th>
                                    <th class="text-end">Unit Cost</th>
                                    <th class="text-end">Total Cost (VAT)</th>
                                    <th>Supplier / Workshop</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($maintenanceLogs as $log)
                                    <tr>
                                        <td>{{ $log->log_date ? $log->log_date->format('d/m/Y') : '-' }}</td>
                                        <td class="fw-semibold">{{ $log->spare_part_name }}</td>
                                        <td><code>{{ $log->po_no ?: '-' }}</code></td>
                                        <td>{{ $log->quantity }} {{ $log->unit }}</td>
                                        <td class="text-end">{{ number_format($log->unit_cost, 2) }} ETB</td>
                                        <td class="text-end fw-bold text-dark">{{ number_format($log->total_cost_with_vat, 2) }} ETB</td>
                                        <td>{{ $log->supplier ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No spare part or maintenance cost logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Update Reading Modal --}}
    <div class="modal fade" id="updateReadingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-start">
                <form action="{{ route('equipment.update-reading', $equipment) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title h6">
                            <i class="fas fa-tachometer-alt text-primary me-2"></i> Update Reading: {{ $equipment->plate_serial_no }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Current Reading ({{ $equipment->reading_unit }})</label>
                                <input type="number" step="0.1" name="current_reading" class="form-control" value="{{ $equipment->current_reading }}" required min="{{ (float)$equipment->current_reading }}">
                                <div class="form-text small">Previous: {{ number_format($equipment->current_reading, 1) }} {{ $equipment->reading_unit }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Reading Date</label>
                                <input type="date" name="current_reading_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Current Location / Project Site</label>
                            <input type="text" name="location_name" class="form-control" value="{{ $equipment->location_name }}">
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-semibold">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="e.g. Regular shift inspection...">
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
</div>
@endsection
