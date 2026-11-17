@extends('layouts.app')

@section('title', 'PM Schedule: ' . $pmSchedule->schedule_code)

@section('content')
<div class="container-fluid py-3">
    {{-- Action & Workflow Bar --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 bg-white p-3 rounded-3 shadow-sm border">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('pm-schedules.index') }}" class="btn btn-outline-secondary btn-sm" title="Back to PM Schedules">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <span class="text-muted small">Schedule Code:</span>
                <span class="fw-bold text-dark fs-5 ms-1">{{ $pmSchedule->schedule_code }}</span>
                <span class="ms-2">{!! $pmSchedule->status_badge !!}</span>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            {{-- Workflow Buttons --}}
            @if($pmSchedule->isDraft())
                @if($canSubmit ?? true)
                    <form action="{{ route('pm-schedules.submit', $pmSchedule) }}" method="POST" class="d-inline" onsubmit="return confirm('Submit this schedule for checking and verification?')">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane me-1"></i> Submit Schedule
                        </button>
                    </form>
                @endif
                @if($canEdit ?? true)
                    <a href="{{ route('pm-schedules.edit', $pmSchedule) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                @endif
            @elseif($pmSchedule->isSubmitted())
                @if($canCheck ?? true)
                    <form action="{{ route('pm-schedules.check', $pmSchedule) }}" method="POST" class="d-inline" onsubmit="return confirm('Verify and sign this schedule as Checked?')">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm text-dark">
                            <i class="fas fa-user-check me-1"></i> Verify & Sign (Checked)
                        </button>
                    </form>
                @endif
                @if($canReject ?? true)
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times me-1"></i> Reject
                    </button>
                @endif
            @elseif($pmSchedule->isChecked())
                @if($canApprove ?? true)
                    <form action="{{ route('pm-schedules.approve', $pmSchedule) }}" method="POST" class="d-inline" onsubmit="return confirm('Approve this PM schedule officially?')">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-check-double me-1"></i> Approve Officially
                        </button>
                    </form>
                @endif
                @if($canReject ?? true)
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times me-1"></i> Reject
                    </button>
                @endif
            @endif

            @if(auth()->user()?->isAdmin() && !$pmSchedule->isDraft())
                <form action="{{ route('pm-schedules.revert', $pmSchedule) }}" method="POST" class="d-inline" onsubmit="return confirm('Revert this schedule back to Draft?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-undo me-1"></i> Revert to Draft
                    </button>
                </form>
            @endif

            {{-- Export & Print --}}
            <a href="{{ route('pm-schedules.export', $pmSchedule) }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export Excel (OF/074)
            </a>
            <a href="{{ route('pm-schedules.print', $pmSchedule) }}" target="_blank" class="btn btn-secondary btn-sm">
                <i class="fas fa-print me-1"></i> Print A4
            </a>
        </div>
    </div>

    {{-- Rejection Alert if Rejected --}}
    @if($pmSchedule->isRejected() && $pmSchedule->rejection_reason)
        <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center mb-4">
            <i class="fas fa-exclamation-circle fa-2x me-3 text-danger"></i>
            <div>
                <strong class="d-block">Schedule Rejected:</strong>
                <p class="mb-0 small">{{ $pmSchedule->rejection_reason }}</p>
            </div>
        </div>
    @endif

    {{-- Official Document Canvas --}}
    <div class="card border shadow-sm rounded-3 bg-white mb-4">
        <div class="card-body p-4">
            {{-- Document Header Table --}}
            <div class="table-responsive mb-3">
                <table class="table table-bordered border-dark align-middle text-center mb-0" style="font-size: 0.9rem;">
                    <tbody>
                        <tr>
                            <td rowspan="3" style="width: 15%; vertical-align: middle;">
                                <img src="{{ asset('images/company-logo.png') }}" alt="TNT Logo" style="max-height: 55px;" onerror="this.onerror=null; this.src='{{ asset('images/tnt-logo.png') }}';">
                                <div class="fw-bold text-dark mt-1" style="font-size: 0.75rem;">TNT CONSTRUCTION</div>
                            </td>
                            <td style="width: 55%;">
                                <div class="small text-muted">Company Name:</div>
                                <div class="fw-bold" style="font-size: 1.1rem; color: #1e3a8a;">ቲ ኤን ቲ ኮንስትራክሽንና ንግድ ሥራዎች</div>
                                <div class="fw-bold" style="font-size: 1.15rem; color: #1e3a8a;">TNT CONSTRUCTION AND TRADING</div>
                            </td>
                            <td class="text-start" style="width: 30%;">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Document №:</span>
                                    <strong class="text-danger">{{ $pmSchedule->document_code ?: 'OF/EAMD/074' }}</strong>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="2">
                                <div class="small text-muted">Title:</div>
                                <h4 class="fw-bold mb-0 text-dark">Preventive Maintenance Schedule</h4>
                                <div class="small text-muted mt-1">Equipment Administration & Maintenance Department (EAMD)</div>
                            </td>
                            <td class="text-start">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Issue №:</span>
                                    <strong>{{ $pmSchedule->issue_no ?: '1' }}</strong>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-start">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Page №:</span>
                                    <strong>{{ $pmSchedule->page_no ?: 'Page 1 of 1' }}</strong>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Document Meta Strip --}}
            <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded border">
                <div>
                    <span class="text-muted small">Location / Project:</span>
                    <strong><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $pmSchedule->project_location ?: ($pmSchedule->project?->name ?: 'All Project Sites / Central Workshop') }}</strong>
                </div>
                <div>
                    <span class="text-muted small">Schedule Date:</span>
                    <strong>{{ $pmSchedule->schedule_date ? $pmSchedule->schedule_date->format('d/m/Y') : date('d/m/Y') }}</strong>
                    @if($pmSchedule->period_start && $pmSchedule->period_end)
                        <span class="text-muted small ms-2">Period:</span>
                        <strong>{{ $pmSchedule->period_start->format('d/m/Y') }} – {{ $pmSchedule->period_end->format('d/m/Y') }}</strong>
                    @endif
                </div>
            </div>

            {{-- Schedule Items Table --}}
            <div class="table-responsive">
                <table class="table table-bordered border-dark align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="text-center align-middle" style="background-color: #f1f5f9;">
                        <tr>
                            <th rowspan="2" style="width: 3%;">No.</th>
                            <th colspan="5">EQUIPMENT SPECIFICATION</th>
                            <th rowspan="2" style="width: 10%;">Location</th>
                            <th colspan="3">Last Service</th>
                            <th colspan="2">Next Service</th>
                            <th colspan="2">Current Reading</th>
                            <th rowspan="2" style="width: 8%;">Remain<br>Hour/Km</th>
                            <th rowspan="2" style="width: 8%;">Service<br>Status</th>
                            <th rowspan="2" style="width: 12%;">Remark</th>
                        </tr>
                        <tr>
                            <th style="width: 9%;">Type</th>
                            <th style="width: 8%;">Make</th>
                            <th style="width: 8%;">Model</th>
                            <th style="width: 8%;">Plate / SN</th>
                            <th style="width: 8%;">Serial No</th>
                            <th style="width: 6%;">HR/Km</th>
                            <th style="width: 6%;">Date</th>
                            <th style="width: 4%;">Type</th>
                            <th style="width: 6%;">HR/KM</th>
                            <th style="width: 4%;">Type</th>
                            <th style="width: 6%;">HR/KM</th>
                            <th style="width: 6%;">DATE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $categoryNames = [
                                'EARTH MOVING MACHINE' => 'EARTH MOVING MACHINE',
                                'DUMP TRUCK' => 'HEAVY TRUCKS / DUMP TRUCK',
                                'LIGHT VEHICLES' => 'LIGHT VEHICLES / PICKUPS',
                                'AUXILIARY' => 'AUXILIARY & PLANT EQUIPMENT',
                            ];
                        @endphp

                        @foreach($itemsGrouped as $categoryKey => $items)
                            <tr class="table-secondary">
                                <td colspan="17" class="fw-bold text-uppercase py-2 ps-3" style="background-color: #e2e8f0; font-size: 0.9rem;">
                                    <i class="fas fa-tag me-1 text-primary"></i> {{ $categoryNames[$categoryKey] ?? $categoryKey }}
                                </td>
                            </tr>
                            @php $rowNo = 1; @endphp
                            @foreach($items as $item)
                                @php
                                    $remain = $item->remain_reading;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $rowNo++ }}</td>
                                    <td>
                                        @if($item->equipment_id)
                                            <a href="{{ route('equipment.show', $item->equipment_id) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $item->equipment_type }}
                                            </a>
                                        @else
                                            <strong>{{ $item->equipment_type }}</strong>
                                        @endif
                                    </td>
                                    <td>{{ $item->make }}</td>
                                    <td>{{ $item->model }}</td>
                                    <td class="fw-semibold">{{ $item->plate_serial_no }}</td>
                                    <td class="small text-muted">{{ $item->serial_no ?: '-' }}</td>
                                    <td class="small">{{ $item->location }}</td>
                                    <td class="text-end">{{ $item->last_service_reading !== null ? number_format($item->last_service_reading, 1) : '-' }}</td>
                                    <td class="text-center small">{{ $item->last_service_date ? $item->last_service_date->format('d/m/Y') : '-' }}</td>
                                    <td class="text-center fw-bold">{{ $item->last_service_type ?: '-' }}</td>
                                    <td class="text-end fw-bold">{{ $item->next_service_reading !== null ? number_format($item->next_service_reading, 1) : '-' }}</td>
                                    <td class="text-center fw-bold">{{ $item->next_service_type ?: '-' }}</td>
                                    <td class="text-end">{{ $item->current_reading !== null ? number_format($item->current_reading, 1) : '-' }}</td>
                                    <td class="text-center small">{{ $item->current_reading_date ? $item->current_reading_date->format('d/m/Y') : '-' }}</td>
                                    <td class="text-end fw-bold {{ $remain < 0 ? 'text-danger' : ($remain <= 50 ? 'text-warning' : 'text-success') }}">
                                        {{ $remain !== null ? number_format($remain, 1) : '-' }}
                                    </td>
                                    <td class="text-center">
                                        {!! $item->service_status_badge !!}
                                    </td>
                                    <td class="small">{{ $item->remark ?: '-' }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Service Type Reference Legend Table --}}
            <div class="row mt-4 g-3">
                <div class="col-md-7">
                    <div class="p-3 border rounded bg-light" style="font-size: 0.8rem;">
                        <h6 class="fw-bold mb-2 text-dark"><i class="fas fa-book-open text-primary me-1"></i> Service Type Reference Legend</h6>
                        <div class="row">
                            <div class="col-6">
                                <div class="fw-bold text-primary mb-1">Earth Moving & Heavy Machinery:</div>
                                <div>• <strong>Type A:</strong> 250 hr (Engine oil & filter, lube)</div>
                                <div>• <strong>Type B:</strong> 500 hr (Fuel & air filters, oil)</div>
                                <div>• <strong>Type C:</strong> 1,000 hr (Hydraulic & transmission)</div>
                                <div>• <strong>Type D:</strong> 2,000 hr (Major overhaul / fluids)</div>
                            </div>
                            <div class="col-6">
                                <div class="fw-bold text-success mb-1">Light Vehicles & Pickups:</div>
                                <div>• <strong>Type A2:</strong> 5,000 km (Oil & safety inspection)</div>
                                <div>• <strong>Type B2:</strong> 10,000 km (Filters, brake check)</div>
                                <div>• <strong>Type C2:</strong> 20,000 km (Major service)</div>
                                <div>• <strong>Type D2:</strong> 40,000 km (Complete overhaul)</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sign-off Summary --}}
                <div class="col-md-5">
                    <div class="border rounded p-3 h-100 bg-white" style="font-size: 0.85rem;">
                        <h6 class="fw-bold mb-3 text-dark border-bottom pb-2">Document Approvals & Signatures</h6>
                        <div class="mb-2">
                            <span class="text-muted">Prepared By:</span>
                            <strong>{{ $pmSchedule->prepared_name ?: ($pmSchedule->preparedByUser?->name ?: '________________') }}</strong>
                            @if($pmSchedule->prepared_at)
                                <span class="small text-muted">({{ $pmSchedule->prepared_at->format('d/m/Y') }})</span>
                            @endif
                        </div>
                        <div class="mb-2">
                            <span class="text-muted">Checked By:</span>
                            <strong>{{ $pmSchedule->checked_name ?: ($pmSchedule->checkedByUser?->name ?: '________________') }}</strong>
                            @if($pmSchedule->checked_at)
                                <span class="small text-muted">({{ $pmSchedule->checked_at->format('d/m/Y') }})</span>
                            @endif
                        </div>
                        <div class="mb-0">
                            <span class="text-muted">Approved By:</span>
                            <strong>{{ $pmSchedule->approved_name ?: ($pmSchedule->approvedByUser?->name ?: '________________') }}</strong>
                            @if($pmSchedule->approved_at)
                                <span class="small text-muted">({{ $pmSchedule->approved_at->format('d/m/Y') }})</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('pm-schedules.reject', $pmSchedule) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title h6 text-danger"><i class="fas fa-times-circle me-1"></i> Reject Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">Please provide comments explaining why this schedule is rejected. It will be returned to Draft status.</p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Specify incorrect meter readings, missed equipment, or scheduling conflicts..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm">Reject Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
