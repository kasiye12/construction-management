@extends('layouts.app')

@section('title', 'Weekly Report ' . $contractualReport->report_number . ' - CMS')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h2 class="mb-0">📋 {{ $contractualReport->report_number }}</h2>
                {!! $contractualReport->status_badge !!}
            </div>
            <p class="text-muted mb-0 mt-1">
                <strong>{{ $contractualReport->project?->name ?? 'Unknown / Archived Project' }}</strong> • 
                Period: {{ $contractualReport->week_start_date->format('M d, Y') }} to {{ $contractualReport->week_end_date->format('M d, Y') }} •
                Document: <span class="badge bg-light text-dark border">{{ $contractualReport->document_code }} (Issue {{ $contractualReport->issue_no }})</span>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('contractual-reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
            <a href="{{ route('contractual-reports.excel', $contractualReport) }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Export Official Excel
            </a>
            <a href="{{ route('contractual-reports.print', $contractualReport) }}" target="_blank" class="btn btn-secondary">
                <i class="fas fa-print me-1"></i> Print / PDF
            </a>
            @php
                $canEdit = $contractualReport->isDraft() || $contractualReport->isRejected() || auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->role === 'Contract';
                $canDelete = auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->role === 'Contract' || auth()->user()->hasPermission('contractual-reports.delete') || $contractualReport->prepared_by === auth()->id();
            @endphp
            @if($canEdit)
                <a href="{{ route('contractual-reports.edit', $contractualReport) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            @endif
            @if($canDelete)
                <form action="{{ route('contractual-reports.destroy', $contractualReport) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove report {{ $contractualReport->report_number }} from the list?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger" title="Remove Report">
                        <i class="fas fa-trash-alt me-1"></i> Remove
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<!-- Workflow Action Banner -->
@if($contractualReport->isRejected())
    <div class="alert alert-danger mb-4 shadow-sm border-0 d-flex align-items-center justify-content-between">
        <div>
            <i class="fas fa-exclamation-triangle fa-2x me-3 float-start text-danger"></i>
            <strong>This report was returned / rejected.</strong><br>
            <span class="text-dark">Reason: {{ $contractualReport->rejection_reason }}</span>
        </div>
        <a href="{{ route('contractual-reports.edit', $contractualReport) }}" class="btn btn-danger btn-sm">
            <i class="fas fa-edit me-1"></i> Make Corrections
        </a>
    </div>
@endif

<div class="card mb-4 shadow-sm border-0 bg-light">
    <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white p-2 border shadow-sm">
                <i class="fas fa-file-contract text-primary fa-lg"></i>
            </div>
            <div>
                <span class="fw-bold">Workflow Status:</span> {!! $contractualReport->status_badge !!}
                <div class="small text-muted">
                    Prepared by: <strong>{{ $contractualReport->prepared_name ?: ($contractualReport->preparedByUser?->name ?: 'N/A') }}</strong>
                    @if($contractualReport->prepared_at) on {{ $contractualReport->prepared_at->format('M d, Y H:i') }} @endif
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @if($contractualReport->isDraft())
                @if($canSubmit ?? true)
                    <form action="{{ route('contractual-reports.submit', $contractualReport) }}" method="POST" class="d-inline" onsubmit="return confirm('Submit this report for checking and verification?')">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane me-1"></i> Submit Report
                        </button>
                    </form>
                @endif
            @elseif($contractualReport->isSubmitted())
                @if($canCheck ?? true)
                    <form action="{{ route('contractual-reports.check', $contractualReport) }}" method="POST" class="d-inline" onsubmit="return confirm('Verify and sign this report as Checked?')">
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
            @elseif($contractualReport->isChecked())
                @if($canApprove ?? true)
                    <form action="{{ route('contractual-reports.approve', $contractualReport) }}" method="POST" class="d-inline" onsubmit="return confirm('Approve this contractual report officially?')">
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
            @elseif($contractualReport->isApproved())
                <span class="badge bg-success p-2"><i class="fas fa-check-circle me-1"></i> Officially Approved</span>
                @if(auth()->user()->isAdmin())
                    <form action="{{ route('contractual-reports.revert', $contractualReport) }}" method="POST" class="d-inline" onsubmit="return confirm('Admin: Revert this approved report back to draft?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-undo me-1"></i> Revert to Draft
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Navigation Tabs for Document Views -->
<ul class="nav nav-tabs mb-4" id="viewTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold py-2" id="vpage1-tab" data-bs-toggle="tab" data-bs-target="#vpage1" type="button" role="tab">
            <i class="fas fa-exclamation-circle text-danger me-1"></i> Page 1: Causes of Claim
            @if($contractualReport->active_claims_count > 0)
                <span class="badge bg-danger ms-1">{{ $contractualReport->active_claims_count }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold py-2" id="vpage2-tab" data-bs-toggle="tab" data-bs-target="#vpage2" type="button" role="tab">
            <i class="fas fa-list-ol text-warning me-1"></i> Page 2: Claim Event Details
            @if($contractualReport->claimEvents->count() > 0)
                <span class="badge bg-warning text-dark ms-1">{{ $contractualReport->claimEvents->count() }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold py-2" id="vpage3-tab" data-bs-toggle="tab" data-bs-target="#vpage3" type="button" role="tab">
            <i class="fas fa-envelope-open-text text-info me-1"></i> Page 3: Site Correspondence
            @if($contractualReport->documents->count() > 0)
                <span class="badge bg-info text-dark ms-1">{{ $contractualReport->documents->count() }} files</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold py-2" id="vaudit-tab" data-bs-toggle="tab" data-bs-target="#vaudit" type="button" role="tab">
            <i class="fas fa-history text-secondary me-1"></i> Signatures & Sign-offs
        </button>
    </li>
</ul>

<div class="tab-content" id="viewTabsContent">
    <!-- ========================================== -->
    <!-- TAB 1: Causes of Claim (Page 1 of 3) -->
    <!-- ========================================== -->
    <div class="tab-pane fade show active" id="vpage1" role="tabpanel">
        <div class="card shadow-sm border-0 mb-4">
            <!-- Official Header Box -->
            <div class="card-body border-bottom bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center text-md-start">
                        <img src="{{ asset('images/company-logo.png') }}" alt="TNT Logo" style="height:70px;width:70px;object-fit:contain;">
                    </div>
                    <div class="col-md-7 text-center">
                        <div class="text-muted small">Company name:</div>
                        <h4 class="fw-bold mb-0 text-primary">ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች</h4>
                        <h5 class="fw-bold mb-1">TNT CONSTRUCTION AND TRADING</h5>
                        <h6 class="text-uppercase tracking-wider fw-bold text-secondary mb-0">Weekly Contractual Follow-up Reporting Form</h6>
                    </div>
                    <div class="col-md-3 text-end">
                        <table class="table table-sm table-bordered text-start mb-0 small">
                            <tr><th class="bg-light" style="width:50%;">Document №:</th><td class="fw-bold">{{ $contractualReport->document_code }}</td></tr>
                            <tr><th class="bg-light">Issue №:</th><td>{{ $contractualReport->issue_no }}</td></tr>
                            <tr><th class="bg-light">Page №:</th><td class="fw-bold text-primary">Page 1 of 3</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Project Details Strip -->
            <div class="card-body bg-light border-bottom py-2">
                <div class="row g-2 small">
                    <div class="col-md-6"><strong>Project:</strong> {{ $contractualReport->project?->name ?? 'Unknown / Archived Project' }}</div>
                    <div class="col-md-6"><strong>Project Location:</strong> {{ $contractualReport->project_location ?: 'Site' }}</div>
                    <div class="col-md-6"><strong>Client:</strong> {{ $contractualReport->client_name ?: 'N/A' }}</div>
                    <div class="col-md-6"><strong>Consultant:</strong> {{ $contractualReport->consultant_name ?: 'N/A' }}</div>
                    <div class="col-md-6"><strong>Contractor:</strong> {{ $contractualReport->contractor_name }}</div>
                    <div class="col-md-6 text-md-end text-primary fw-bold">
                        Date: From {{ $contractualReport->week_start_date->format('d/m/Y') }} To {{ $contractualReport->week_end_date->format('d/m/Y') }}
                    </div>
                </div>
            </div>

            <!-- Page 1 Title -->
            <div class="card-header bg-white py-2 text-center border-bottom">
                <h5 class="mb-0 fw-bold">Causes of Claim - Follow up Form</h5>
            </div>

            <!-- Checklist Table -->
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 text-center">
                    <thead class="table-dark">
                        <tr>
                            <th rowspan="2" class="text-start" style="width: 42%;">Claim Issues encountered throughout the week</th>
                            <th colspan="2">Monday</th>
                            <th colspan="2">Tuesday</th>
                            <th colspan="2">Wednesday</th>
                            <th colspan="2">Thursday</th>
                            <th colspan="2">Friday</th>
                            <th colspan="2">Saturday</th>
                            <th colspan="2">Sunday</th>
                        </tr>
                        <tr class="table-secondary text-dark small">
                            <th>Yes</th><th>No</th>
                            <th>Yes</th><th>No</th>
                            <th>Yes</th><th>No</th>
                            <th>Yes</th><th>No</th>
                            <th>Yes</th><th>No</th>
                            <th>Yes</th><th>No</th>
                            <th>Yes</th><th>No</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $checksGrouped = $contractualReport->claimChecks->groupBy('category_title');
                        @endphp
                        @foreach($checksGrouped as $catTitle => $checks)
                            <tr class="table-primary text-start fw-bold">
                                <td colspan="15" class="py-2"><i class="fas fa-folder-open me-2"></i>{{ $catTitle }}</td>
                            </tr>
                            @foreach($checks as $chk)
                                <tr>
                                    <td class="text-start ps-4">
                                        <span class="badge bg-secondary me-2">{{ $chk->item_code }}</span>
                                        {{ $chk->item_title }}
                                    </td>
                                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $d)
                                        @if($chk->$d)
                                            <td class="bg-danger bg-opacity-10 text-danger fw-bold"><i class="fas fa-check"></i></td>
                                            <td class="text-muted opacity-25">-</td>
                                        @else
                                            <td class="text-muted opacity-25">-</td>
                                            <td class="text-success"><i class="fas fa-check opacity-75"></i></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-body border-top">
                <div class="alert alert-info py-2 small mb-3">
                    <strong>N.B:-</strong> Detailed information of the claim case encountered has to be filled and submitted weekly on the Format TNT/CAD/01 Page 02.
                </div>
                @if($contractualReport->page1_notes)
                    <div class="bg-light p-3 rounded mb-3">
                        <strong class="d-block mb-1">Notes:</strong>
                        <p class="mb-0">{{ $contractualReport->page1_notes }}</p>
                    </div>
                @endif

                <!-- Sign-off footer strip for Page 1 -->
                <div class="row pt-3 text-center border-top">
                    <div class="col-md-4">
                        <div class="text-muted small">Prepared By:</div>
                        <div class="fw-bold">{{ $contractualReport->prepared_name ?: ($contractualReport->preparedByUser?->name ?: '_______________') }}</div>
                        <div class="small text-muted">{{ $contractualReport->prepared_at ? $contractualReport->prepared_at->format('M d, Y') : '' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Checked By:</div>
                        <div class="fw-bold">{{ $contractualReport->checked_name ?: ($contractualReport->checkedByUser?->name ?: '_______________') }}</div>
                        <div class="small text-muted">{{ $contractualReport->checked_at ? $contractualReport->checked_at->format('M d, Y') : '' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Approved By:</div>
                        <div class="fw-bold">{{ $contractualReport->approved_name ?: ($contractualReport->approvedByUser?->name ?: '_______________') }}</div>
                        <div class="small text-muted">{{ $contractualReport->approved_at ? $contractualReport->approved_at->format('M d, Y') : '' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: Claim Event Details (Page 2 of 3) -->
    <!-- ========================================== -->
    <div class="tab-pane fade" id="vpage2" role="tabpanel">
        <div class="card shadow-sm border-0 mb-4">
            <!-- Official Header Box -->
            <div class="card-body border-bottom bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center text-md-start">
                        <img src="{{ asset('images/company-logo.png') }}" alt="TNT Logo" style="height:70px;width:70px;object-fit:contain;">
                    </div>
                    <div class="col-md-7 text-center">
                        <div class="text-muted small">Company name:</div>
                        <h4 class="fw-bold mb-0 text-primary">ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች</h4>
                        <h5 class="fw-bold mb-1">TNT CONSTRUCTION AND TRADING</h5>
                        <h6 class="text-uppercase tracking-wider fw-bold text-secondary mb-0">Weekly Contractual Follow-up Reporting Form</h6>
                    </div>
                    <div class="col-md-3 text-end">
                        <table class="table table-sm table-bordered text-start mb-0 small">
                            <tr><th class="bg-light" style="width:50%;">Document №:</th><td class="fw-bold">{{ $contractualReport->document_code }}</td></tr>
                            <tr><th class="bg-light">Issue №:</th><td>{{ $contractualReport->issue_no }}</td></tr>
                            <tr><th class="bg-light">Page №:</th><td class="fw-bold text-warning">Page 2 of 3</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Project Details Strip -->
            <div class="card-body bg-light border-bottom py-2">
                <div class="row g-2 small">
                    <div class="col-md-6"><strong>Project:</strong> {{ $contractualReport->project?->name ?? 'Unknown / Archived Project' }}</div>
                    <div class="col-md-6"><strong>Project Location:</strong> {{ $contractualReport->project_location ?: 'Site' }}</div>
                    <div class="col-md-6"><strong>Client:</strong> {{ $contractualReport->client_name ?: 'N/A' }}</div>
                    <div class="col-md-6"><strong>Consultant:</strong> {{ $contractualReport->consultant_name ?: 'N/A' }}</div>
                    <div class="col-md-6"><strong>Contractor:</strong> {{ $contractualReport->contractor_name }}</div>
                    <div class="col-md-6 text-md-end text-primary fw-bold">
                        Date: From {{ $contractualReport->week_start_date->format('d/m/Y') }} To {{ $contractualReport->week_end_date->format('d/m/Y') }}
                    </div>
                </div>
            </div>

            <!-- Page 2 Title -->
            <div class="card-header bg-white py-2 text-center border-bottom">
                <h5 class="mb-0 fw-bold">Detailed Information of the Claim Event</h5>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 12%;">Date</th>
                            <th style="width: 14%;">Issues enc. (No)</th>
                            <th style="width: 28%;">Detail description of the cause</th>
                            <th style="width: 20%;">Effect on the progress</th>
                            <th style="width: 16%;">Action taken</th>
                            <th style="width: 10%;">Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contractualReport->claimEvents as $evt)
                            <tr>
                                <td>{{ $evt->event_date ? $evt->event_date->format('d/m/Y') : '-' }}</td>
                                <td><span class="badge bg-danger">{{ $evt->issue_number }}</span></td>
                                <td>{{ $evt->cause_description }}</td>
                                <td>{{ $evt->effect_on_progress }}</td>
                                <td>{{ $evt->action_taken }}</td>
                                <td>{{ $evt->remark ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-success opacity-50"></i>
                                    <p class="mb-0">No specific claim events recorded for this week.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-body border-top">
                <div class="bg-light p-3 rounded mb-3">
                    <strong class="d-block mb-1">Remark:</strong>
                    <p class="mb-0">{{ $contractualReport->page2_remark ?: 'None' }}</p>
                </div>

                <!-- Sign-off strip -->
                <div class="row pt-3 text-center border-top">
                    <div class="col-md-4">
                        <div class="text-muted small">Prepared By:</div>
                        <div class="fw-bold">{{ $contractualReport->prepared_name ?: ($contractualReport->preparedByUser?->name ?: '_______________') }}</div>
                        <div class="small text-muted">{{ $contractualReport->prepared_at ? $contractualReport->prepared_at->format('M d, Y') : '' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Checked By:</div>
                        <div class="fw-bold">{{ $contractualReport->checked_name ?: ($contractualReport->checkedByUser?->name ?: '_______________') }}</div>
                        <div class="small text-muted">{{ $contractualReport->checked_at ? $contractualReport->checked_at->format('M d, Y') : '' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Approved By:</div>
                        <div class="fw-bold">{{ $contractualReport->approved_name ?: ($contractualReport->approvedByUser?->name ?: '_______________') }}</div>
                        <div class="small text-muted">{{ $contractualReport->approved_at ? $contractualReport->approved_at->format('M d, Y') : '' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 3: Site Correspondence (Page 3 of 3) -->
    <!-- ========================================== -->
    <div class="tab-pane fade" id="vpage3" role="tabpanel">
        <div class="card shadow-sm border-0 mb-4">
            <!-- Official Header Box -->
            <div class="card-body border-bottom bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center text-md-start">
                        <img src="{{ asset('images/company-logo.png') }}" alt="TNT Logo" style="height:70px;width:70px;object-fit:contain;">
                    </div>
                    <div class="col-md-7 text-center">
                        <div class="text-muted small">Company name:</div>
                        <h4 class="fw-bold mb-0 text-primary">ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች</h4>
                        <h5 class="fw-bold mb-1">TNT CONSTRUCTION AND TRADING</h5>
                        <h6 class="text-uppercase tracking-wider fw-bold text-secondary mb-0">Weekly Contractual Follow-up Reporting Form</h6>
                    </div>
                    <div class="col-md-3 text-end">
                        <table class="table table-sm table-bordered text-start mb-0 small">
                            <tr><th class="bg-light" style="width:50%;">Document №:</th><td class="fw-bold">{{ $contractualReport->document_code }}</td></tr>
                            <tr><th class="bg-light">Issue №:</th><td>{{ $contractualReport->issue_no }}</td></tr>
                            <tr><th class="bg-light">Page №:</th><td class="fw-bold text-info">Page 3 of 3</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Project Details Strip -->
            <div class="card-body bg-light border-bottom py-2">
                <div class="row g-2 small">
                    <div class="col-md-6"><strong>Project Location:</strong> {{ $contractualReport->project_location ?: 'Site' }}</div>
                    <div class="col-md-6"><strong>Client:</strong> {{ $contractualReport->client_name ?: 'N/A' }}</div>
                    <div class="col-md-6"><strong>Consultant:</strong> {{ $contractualReport->consultant_name ?: 'N/A' }}</div>
                    <div class="col-md-6"><strong>Contractor:</strong> {{ $contractualReport->contractor_name }}</div>
                    <div class="col-md-12 text-md-end text-primary fw-bold">
                        Date: From {{ $contractualReport->week_start_date->format('d/m/Y') }} To {{ $contractualReport->week_end_date->format('d/m/Y') }}
                    </div>
                </div>
            </div>

            <!-- Page 3 Title -->
            <div class="card-header bg-white py-2 text-center border-bottom">
                <h5 class="mb-0 fw-bold">Site Correspondence - Follow up Form</h5>
            </div>

            <!-- Checklist Table -->
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 text-center">
                    <thead class="table-dark">
                        <tr>
                            <th rowspan="2" class="text-start" style="width: 42%;">Correspondences</th>
                            <th colspan="2">Monday</th>
                            <th colspan="2">Tuesday</th>
                            <th colspan="2">Wednesday</th>
                            <th colspan="2">Thursday</th>
                            <th colspan="2">Friday</th>
                            <th colspan="2">Saturday</th>
                            <th colspan="2">Sunday</th>
                        </tr>
                        <tr class="table-secondary text-dark small">
                            <th>Yes</th><th>No</th>
                            <th>Yes</th><th>No</th>
                            <th>Yes</th><th>No</th>
                            <th>Yes</th><th>No</th>
                            <th>Yes</th><th>No</th>
                            <th>Yes</th><th>No</th>
                            <th>Yes</th><th>No</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $correspGrouped = $contractualReport->correspondenceChecks->groupBy('direction_title');
                        @endphp
                        @foreach($correspGrouped as $dirTitle => $checks)
                            <tr class="table-info text-start fw-bold">
                                <td colspan="15" class="py-2"><i class="fas fa-mail-bulk me-2"></i>{{ $dirTitle }}</td>
                            </tr>
                            @foreach($checks as $chk)
                                <tr>
                                    <td class="text-start ps-4">
                                        <span class="badge bg-secondary me-2">{{ $chk->item_code }}</span>
                                        {{ $chk->party_title }}
                                    </td>
                                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $d)
                                        @if($chk->$d)
                                            <td class="bg-primary bg-opacity-10 text-primary fw-bold"><i class="fas fa-check"></i></td>
                                            <td class="text-muted opacity-25">-</td>
                                        @else
                                            <td class="text-muted opacity-25">-</td>
                                            <td class="text-success"><i class="fas fa-check opacity-75"></i></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-body border-top">
                <div class="bg-light p-3 rounded mb-3">
                    <strong class="d-block mb-1">Remark:</strong>
                    <p class="mb-0">{{ $contractualReport->page3_remark ?: 'None' }}</p>
                </div>

                <div class="alert alert-secondary py-2 small mb-3">
                    <strong>Note:</strong> The Letters shall be attached.
                </div>

                <!-- Attached Letters Section -->
                <div class="card border mb-3">
                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                        <span class="fw-bold"><i class="fas fa-paperclip text-primary me-2"></i> Attached Letters & Correspondence Files</span>
                        <span class="badge bg-primary">{{ $contractualReport->documents->count() }} file(s)</span>
                    </div>
                    <div class="card-body">
                        @if($contractualReport->documents->count() > 0)
                            <div class="row g-2">
                                @foreach($contractualReport->documents as $doc)
                                    <div class="col-md-6">
                                        <div class="border rounded p-2 d-flex justify-content-between align-items-center bg-light">
                                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                <i class="fas {{ $doc->file_icon }} fa-2x text-primary"></i>
                                                <div class="text-truncate">
                                                    <div class="fw-bold text-truncate" title="{{ $doc->name }}">{{ $doc->name }}</div>
                                                    <small class="text-muted">{{ $doc->file_size_formatted }} • by {{ $doc->uploader?->name ?? 'User' }}</small>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('documents.download', $doc) }}" class="btn btn-sm btn-outline-primary" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                @if(auth()->user()->isAdmin() || auth()->id() == $doc->uploaded_by)
                                                    <form action="{{ route('documents.destroy', $doc) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this attached file?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0 small">No letters attached yet.</p>
                        @endif

                        <!-- Quick Upload Form -->
                        <div class="mt-3 pt-3 border-top">
                            <form action="{{ route('documents.upload') }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-center">
                                @csrf
                                <input type="hidden" name="documentable_type" value="{{ get_class($contractualReport) }}">
                                <input type="hidden" name="documentable_id" value="{{ $contractualReport->id }}">
                                <div class="col-md-6">
                                    <input type="file" name="file" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="File Title / Reference (optional)">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-upload me-1"></i> Attach
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sign-off strip -->
                <div class="row pt-3 text-center border-top">
                    <div class="col-md-4">
                        <div class="text-muted small">Prepared By:</div>
                        <div class="fw-bold">{{ $contractualReport->prepared_name ?: ($contractualReport->preparedByUser?->name ?: '_______________') }}</div>
                        <div class="small text-muted">{{ $contractualReport->prepared_at ? $contractualReport->prepared_at->format('M d, Y') : '' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Checked By:</div>
                        <div class="fw-bold">{{ $contractualReport->checked_name ?: ($contractualReport->checkedByUser?->name ?: '_______________') }}</div>
                        <div class="small text-muted">{{ $contractualReport->checked_at ? $contractualReport->checked_at->format('M d, Y') : '' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Approved By:</div>
                        <div class="fw-bold">{{ $contractualReport->approved_name ?: ($contractualReport->approvedByUser?->name ?: '_______________') }}</div>
                        <div class="small text-muted">{{ $contractualReport->approved_at ? $contractualReport->approved_at->format('M d, Y') : '' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 4: Audit & Signatures Log -->
    <!-- ========================================== -->
    <div class="tab-pane fade" id="vaudit" role="tabpanel">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-shield-alt text-primary me-2"></i> Document Verification & Approval Trail</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="p-3 border rounded h-100 bg-light">
                            <h6 class="fw-bold text-uppercase text-muted small"><i class="fas fa-user-edit me-1 text-primary"></i> 1. Prepared By</h6>
                            <hr class="my-2">
                            <p class="mb-1 fw-bold fs-5">{{ $contractualReport->prepared_name ?: ($contractualReport->preparedByUser?->name ?: 'Not recorded') }}</p>
                            <p class="small text-muted mb-0">
                                Date: {{ $contractualReport->prepared_at ? $contractualReport->prepared_at->format('F d, Y - H:i:s') : 'Pending' }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded h-100 bg-light">
                            <h6 class="fw-bold text-uppercase text-muted small"><i class="fas fa-user-check me-1 text-warning"></i> 2. Checked By</h6>
                            <hr class="my-2">
                            <p class="mb-1 fw-bold fs-5">{{ $contractualReport->checked_name ?: ($contractualReport->checkedByUser?->name ?: 'Pending Review') }}</p>
                            <p class="small text-muted mb-0">
                                Date: {{ $contractualReport->checked_at ? $contractualReport->checked_at->format('F d, Y - H:i:s') : 'Pending Verification' }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded h-100 bg-light">
                            <h6 class="fw-bold text-uppercase text-muted small"><i class="fas fa-check-double me-1 text-success"></i> 3. Approved By</h6>
                            <hr class="my-2">
                            <p class="mb-1 fw-bold fs-5">{{ $contractualReport->approved_name ?: ($contractualReport->approvedByUser?->name ?: 'Pending Approval') }}</p>
                            <p class="small text-muted mb-0">
                                Date: {{ $contractualReport->approved_at ? $contractualReport->approved_at->format('F d, Y - H:i:s') : 'Pending Final Sign-off' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold mb-3">System Metadata:</h6>
                    <ul class="list-unstyled small text-muted">
                        <li><strong>Report Reference:</strong> {{ $contractualReport->report_number }}</li>
                        <li><strong>Standard Format Code:</strong> {{ $contractualReport->document_code }} (Issue {{ $contractualReport->issue_no }})</li>
                        <li><strong>Created In System:</strong> {{ $contractualReport->created_at->format('M d, Y H:i:s') }}</li>
                        <li><strong>Last Updated:</strong> {{ $contractualReport->updated_at->format('M d, Y H:i:s') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Reject Report -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('contractual-reports.reject', $contractualReport) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="rejectModalLabel"><i class="fas fa-times-circle me-2"></i> Reject / Return Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Please state the reason for returning this contractual report. The preparer will be notified to make corrections.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" required placeholder="Describe what needs to be corrected or verified..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-undo me-1"></i> Return Report</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
