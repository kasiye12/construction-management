@extends('layouts.app')

@section('title', 'Maintenance Report: ' . $equipmentBreakdown->report_code)

@section('content')
<div class="container-fluid py-3">
    {{-- Action & Workflow Bar --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 bg-white p-3 rounded-3 shadow-sm border">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('equipment-breakdowns.index') }}" class="btn btn-outline-secondary btn-sm" title="Back to Reports">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <span class="text-muted small">Report Code:</span>
                <span class="fw-bold text-dark fs-5 ms-1">{{ $equipmentBreakdown->report_code }}</span>
                <span class="ms-2">{!! $equipmentBreakdown->status_badge !!}</span>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            {{-- Workflow Buttons --}}
            @if($equipmentBreakdown->isDraft())
                @if($canSubmit ?? true)
                    <form action="{{ route('equipment-breakdowns.submit', $equipmentBreakdown) }}" method="POST" class="d-inline" onsubmit="return confirm('Submit this maintenance report for checking?')">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane me-1"></i> Submit Report
                        </button>
                    </form>
                @endif
                @if($canEdit ?? true)
                    <a href="{{ route('equipment-breakdowns.edit', $equipmentBreakdown) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                @endif
            @elseif($equipmentBreakdown->isSubmitted())
                @if($canCheck ?? true)
                    <form action="{{ route('equipment-breakdowns.check', $equipmentBreakdown) }}" method="POST" class="d-inline" onsubmit="return confirm('Verify and sign this report as Checked?')">
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
            @elseif($equipmentBreakdown->isChecked())
                @if($canApprove ?? true)
                    <form action="{{ route('equipment-breakdowns.approve', $equipmentBreakdown) }}" method="POST" class="d-inline" onsubmit="return confirm('Approve this maintenance report officially?')">
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

            @if(auth()->user()?->isAdmin() && !$equipmentBreakdown->isDraft())
                <form action="{{ route('equipment-breakdowns.revert', $equipmentBreakdown) }}" method="POST" class="d-inline" onsubmit="return confirm('Revert this report back to Draft?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-undo me-1"></i> Revert to Draft
                    </button>
                </form>
            @endif

            {{-- Export & Print --}}
            <a href="{{ route('equipment-breakdowns.export', $equipmentBreakdown) }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export Excel (OF/030)
            </a>
            <a href="{{ route('equipment-breakdowns.print', $equipmentBreakdown) }}" target="_blank" class="btn btn-secondary btn-sm">
                <i class="fas fa-print me-1"></i> Print A4
            </a>
        </div>
    </div>

    {{-- Rejection Alert if Rejected --}}
    @if($equipmentBreakdown->isRejected() && $equipmentBreakdown->rejection_reason)
        <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center mb-4">
            <i class="fas fa-exclamation-circle fa-2x me-3 text-danger"></i>
            <div>
                <strong class="d-block">Report Rejected:</strong>
                <p class="mb-0 small">{{ $equipmentBreakdown->rejection_reason }}</p>
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
                                <div class="fw-bold" style="font-size: 1.1rem; color: #1e3a8a;">ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች</div>
                                <div class="fw-bold" style="font-size: 1.15rem; color: #1e3a8a;">TNT CONSTRUCTION AND TRADING</div>
                            </td>
                            <td class="text-start" style="width: 30%;">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Document №:</span>
                                    <strong class="text-danger">{{ $equipmentBreakdown->document_code ?: 'OF/TNT/ EAMD/030' }}</strong>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="2">
                                <div class="small text-muted">Title:</div>
                                <h4 class="fw-bold mb-0 text-dark">Maintenance Report</h4>
                                <div class="small text-muted mt-1">Equipment Administration & Maintenance Department (EAMD)</div>
                            </td>
                            <td class="text-start">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Issue №:</span>
                                    <strong>{{ $equipmentBreakdown->issue_no ?: '1' }}</strong>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-start">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted small">Page №:</span>
                                    <strong>{{ $equipmentBreakdown->page_no ?: 'Page 1 of 1' }}</strong>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Meta Strip --}}
            <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded border">
                <div>
                    <span class="text-muted small">Location / Project:</span>
                    <strong><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $equipmentBreakdown->location_name ?: ($equipmentBreakdown->project?->name ?: 'Central Workshop & All Sites') }}</strong>
                </div>
                <div>
                    <span class="text-muted small">Report Date:</span>
                    <strong>{{ $equipmentBreakdown->report_date ? $equipmentBreakdown->report_date->format('d/m/Y') : date('d/m/Y') }}</strong>
                </div>
            </div>

            {{-- Breakdown Table --}}
            <div class="table-responsive">
                <table class="table table-bordered border-dark align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="text-center align-middle" style="background-color: #f1f5f9;">
                        <tr>
                            <th style="width: 4%;">No.</th>
                            <th style="width: 18%;">Equipment Type</th>
                            <th style="width: 5%;">QTY</th>
                            <th style="width: 12%;">Plate No / SN</th>
                            <th style="width: 9%;">Down Date</th>
                            <th style="width: 18%;">Reason of Down</th>
                            <th style="width: 12%;">Action Taken</th>
                            <th style="width: 10%;">Location</th>
                            <th style="width: 9%;">Repair Status</th>
                            <th style="width: 9%;">Finished Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itemsGrouped as $categoryKey => $items)
                            <tr class="table-secondary">
                                <td colspan="10" class="fw-bold text-uppercase py-2 ps-3" style="background-color: #e2e8f0; font-size: 0.9rem;">
                                    <i class="fas fa-tools me-1 text-danger"></i> {{ $categoryKey }}
                                </td>
                            </tr>
                            @php $rowNo = 1; @endphp
                            @foreach($items as $item)
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
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="fw-semibold">{{ $item->plate_no_sn ?: '-' }}</td>
                                    <td class="text-center small">{{ $item->down_date ? $item->down_date->format('d/m/Y') : '-' }}</td>
                                    <td class="fw-semibold text-danger">{{ $item->reason_of_down }}</td>
                                    <td class="small">{{ $item->action_taken }}</td>
                                    <td class="small">{{ $item->location }}</td>
                                    <td class="text-center">
                                        {!! $item->repair_status_badge !!}
                                    </td>
                                    <td class="text-center small">{{ $item->finished_date ? $item->finished_date->format('d/m/Y') : 'In progress' }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Signatures Footer --}}
            <div class="row mt-4 pt-3 border-top g-3">
                <div class="col-md-4 text-center">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block mb-2">PREPARED BY</small>
                        <strong class="d-block text-dark">{{ $equipmentBreakdown->prepared_name ?: ($equipmentBreakdown->preparedByUser?->name ?: '________________') }}</strong>
                        <span class="small text-muted">{{ $equipmentBreakdown->prepared_at ? $equipmentBreakdown->prepared_at->format('d/m/Y') : 'Date: ___/___/2026' }}</span>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block mb-2">CHECKED BY</small>
                        <strong class="d-block text-dark">{{ $equipmentBreakdown->checked_name ?: ($equipmentBreakdown->checkedByUser?->name ?: '________________') }}</strong>
                        <span class="small text-muted">{{ $equipmentBreakdown->checked_at ? $equipmentBreakdown->checked_at->format('d/m/Y') : 'Date: ___/___/2026' }}</span>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="border rounded p-3 bg-light">
                        <small class="text-muted d-block mb-2">APPROVED BY</small>
                        <strong class="d-block text-dark">{{ $equipmentBreakdown->approved_name ?: ($equipmentBreakdown->approvedByUser?->name ?: '________________') }}</strong>
                        <span class="small text-muted">{{ $equipmentBreakdown->approved_at ? $equipmentBreakdown->approved_at->format('d/m/Y') : 'Date: ___/___/2026' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('equipment-breakdowns.reject', $equipmentBreakdown) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title h6 text-danger"><i class="fas fa-times-circle me-1"></i> Reject Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">Please provide comments explaining why this breakdown report is rejected.</p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Specify discrepancies, parts verification needed..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm">Reject Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
