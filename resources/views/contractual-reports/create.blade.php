@extends('layouts.app')

@section('title', 'New Weekly Contractual Follow-up Report (OF/TNT/CAD/01) - CMS')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📝 New Weekly Contractual Follow-up Report</h2>
            <p class="text-muted mb-0">Format: <strong>OF/TNT/CAD/01</strong> (TNT Construction and Trading)</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 mt-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('contractual-reports.index') }}">Contractual Reports</a></li>
                    <li class="breadcrumb-item active">New Report</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('contractual-reports.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>

<form action="{{ route('contractual-reports.store') }}" method="POST" enctype="multipart/form-data" id="contractualReportForm">
    @csrf

    <!-- Document Header & Project Info Card -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/company-logo.png') }}" alt="TNT Logo" style="height:32px;width:32px;background:#fff;border-radius:4px;padding:2px;">
                    <div>
                        <h5 class="mb-0 fw-bold">TNT CONSTRUCTION AND TRADING</h5>
                        <small class="opacity-75">ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች</small>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-primary px-3 py-2 fw-bold fs-6">Document №: OF/TNT/CAD/01</span>
                    <span class="badge bg-white text-dark ms-1">Issue №: 1</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Project <span class="text-danger">*</span></label>
                    <select name="project_id" id="project_select" class="form-select @error('project_id') is-invalid @enderror" required>
                        <option value="">-- Choose Project --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" 
                                data-location="{{ $p->location ?? '' }}"
                                data-client="{{ $p->client_name ?? '' }}"
                                data-consultant="{{ $p->consultant_name ?? '' }}"
                                data-contractor="{{ $p->contractor_name ?? 'TNT Construction and Trading' }}"
                                {{ old('project_id', $projectId) == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                        <span>Project Location</span>
                        <small class="text-muted fw-normal"><i class="fas fa-lock me-1"></i>Auto</small>
                    </label>
                    <input type="text" name="project_location" id="project_location" class="form-control bg-light" 
                           value="{{ old('project_location', $selectedProject?->location) }}" placeholder="Auto-populated from project" readonly tabindex="-1" style="cursor: not-allowed;">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                        <span>Client</span>
                        <small class="text-muted fw-normal"><i class="fas fa-lock me-1"></i>Auto</small>
                    </label>
                    <input type="text" name="client_name" id="client_name" class="form-control bg-light" 
                           value="{{ old('client_name', $selectedProject?->client_name) }}" placeholder="Auto-populated from project" readonly tabindex="-1" style="cursor: not-allowed;">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                        <span>Consultant</span>
                        <small class="text-muted fw-normal"><i class="fas fa-lock me-1"></i>Auto</small>
                    </label>
                    <input type="text" name="consultant_name" id="consultant_name" class="form-control bg-light" 
                           value="{{ old('consultant_name', $selectedProject?->consultant_name) }}" placeholder="Auto-populated from project" readonly tabindex="-1" style="cursor: not-allowed;">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                        <span>Contractor</span>
                        <small class="text-muted fw-normal"><i class="fas fa-lock me-1"></i>Auto</small>
                    </label>
                    <input type="text" name="contractor_name" id="contractor_name" class="form-control bg-light" 
                           value="{{ old('contractor_name', $selectedProject?->contractor_name ?? 'TNT Construction and Trading') }}" placeholder="Auto-populated from project" readonly tabindex="-1" style="cursor: not-allowed;">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Document Code & Issue</label>
                    <div class="input-group">
                        <input type="text" name="document_code" class="form-control" value="OF/TNT/CAD/01" readonly>
                        <span class="input-group-text">Issue</span>
                        <input type="text" name="issue_no" class="form-control" style="max-width:70px;" value="1">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Week Start Date (Monday) <span class="text-danger">*</span></label>
                    <input type="date" name="week_start_date" id="week_start_date" class="form-control @error('week_start_date') is-invalid @enderror" 
                           value="{{ old('week_start_date', $startOfWeek->format('Y-m-d')) }}" required>
                    @error('week_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Week End Date (Sunday) <span class="text-danger">*</span></label>
                    <input type="date" name="week_end_date" id="week_end_date" class="form-control @error('week_end_date') is-invalid @enderror" 
                           value="{{ old('week_end_date', $endOfWeek->format('Y-m-d')) }}" required>
                    @error('week_end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Preview Report Reference</label>
                    <div class="form-control bg-light text-muted">
                        <i class="fas fa-barcode me-2"></i><strong>{{ $previewNumber }}</strong> (Auto-generated upon submission)
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-tasks text-primary me-1"></i> Further Action</span>
                        <small class="text-muted fw-normal">Contractual follow-up required?</small>
                    </label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="needs_further_action" id="action_no" value="0" {{ old('needs_further_action', '0') == '0' ? 'checked' : '' }} onchange="toggleFurtherActionNotes(this.value)">
                        <label class="btn btn-outline-secondary fw-semibold py-2" for="action_no">
                            <i class="fas fa-check-circle me-1 text-success"></i> No (Not Needed)
                        </label>

                        <input type="radio" class="btn-check" name="needs_further_action" id="action_yes" value="1" {{ old('needs_further_action') == '1' ? 'checked' : '' }} onchange="toggleFurtherActionNotes(this.value)">
                        <label class="btn btn-outline-danger fw-semibold py-2" for="action_yes">
                            <i class="fas fa-exclamation-triangle me-1"></i> Yes (Need Further Action)
                        </label>
                    </div>
                </div>

                <div class="col-12" id="further_action_notes_div" style="{{ old('needs_further_action') == '1' ? '' : 'display: none;' }}">
                    <div class="p-3 bg-light rounded border border-danger border-opacity-50">
                        <label class="form-label fw-bold text-danger mb-1">
                            <i class="fas fa-pen me-1"></i> Further Action Description / Action Plan <small class="text-muted fw-normal">(Optional)</small>
                        </label>
                        <input type="text" name="further_action_notes" class="form-control" 
                               value="{{ old('further_action_notes') }}" 
                               placeholder="e.g. Issue contractual delay notice to consultant, pursue BOQ variation order, follow up with design team...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs for the 3 Pages of OF/TNT/CAD/01 -->
    <ul class="nav nav-pills nav-fill mb-3" id="formTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold py-3 shadow-sm border" id="page1-tab" data-bs-toggle="tab" data-bs-target="#page1" type="button" role="tab">
                <i class="fas fa-exclamation-circle me-2"></i> Page 1: Causes of Claim
                <span class="badge bg-primary ms-1">Checklist</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold py-3 shadow-sm border" id="page2-tab" data-bs-toggle="tab" data-bs-target="#page2" type="button" role="tab">
                <i class="fas fa-list-ol me-2"></i> Page 2: Claim Event Details
                <span class="badge bg-warning text-dark ms-1" id="page2TabCount">0 events</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold py-3 shadow-sm border" id="page3-tab" data-bs-toggle="tab" data-bs-target="#page3" type="button" role="tab">
                <i class="fas fa-envelope-open-text me-2"></i> Page 3: Site Correspondence
                <span class="badge bg-info text-dark ms-1">Letters & Files</span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="formTabsContent">
        <!-- ========================================== -->
        <!-- TAB 1: Causes of Claim - Follow up Form -->
        <!-- ========================================== -->
        <div class="tab-pane fade show active" id="page1" role="tabpanel">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0 text-dark fw-bold">Causes of Claim - Follow up Form</h5>
                        <small class="text-muted">Check "Yes" for any day when a contractual delay or claim issue was encountered.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm" onclick="openAddClaimModal()">
                            <i class="fas fa-plus-circle me-1"></i> Add Cause of Claim
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllCheckboxes('claims')">
                            <i class="fas fa-undo me-1"></i> Clear All
                        </button>
                    </div>
                </div>
                <div class="px-3 pt-3">
                    <div class="alert alert-info border-info d-flex align-items-center mb-0 py-2 px-3 small">
                        <i class="fas fa-magic text-primary fs-5 me-3"></i>
                        <div>
                            <strong>Smart Claim Event Sync Active:</strong> Checking <strong>"Yes"</strong> on any weekday automatically adds a corresponding entry to <strong>Page 2 (Detailed Information of the Claim Event)</strong> with the exact date, issue code, and description. Unchecking removes it.
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 text-center" id="claimsTable">
                        <thead class="table-dark">
                            <tr>
                                <th rowspan="2" class="text-start" style="width: 38%;">Claim Issues encountered throughout the week</th>
                                <th colspan="2">Monday</th>
                                <th colspan="2">Tuesday</th>
                                <th colspan="2">Wednesday</th>
                                <th colspan="2">Thursday</th>
                                <th colspan="2">Friday</th>
                                <th colspan="2">Saturday</th>
                                <th colspan="2">Sunday</th>
                                <th rowspan="2" class="text-center" style="width: 70px;">Action</th>
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
                        <tbody id="claimsTableBody">
                            @foreach($claimCategories as $catKey => $catData)
                                <tr class="table-primary text-start fw-bold category-header-row" data-cat-key="{{ $catKey }}">
                                    <td colspan="16" class="py-2">
                                        <i class="fas fa-folder-open me-2"></i>{{ $catData['title'] }}
                                    </td>
                                </tr>
                                @foreach($catData['items'] as $code => $title)
                                    <tr class="claim-item-row" data-cat-key="{{ $catKey }}" data-code="{{ $code }}" data-title="{{ $title }}">
                                        <td class="text-start ps-4">
                                            <span class="badge bg-secondary me-2">{{ $code }}</span>
                                            {{ $title }}
                                        </td>
                                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                            @php 
                                                $fieldYes = "claims[{$code}][{$day}]";
                                                $isChecked = old("claims.{$code}.{$day}", false);
                                            @endphp
                                            <td class="bg-light {{ $isChecked ? 'bg-warning bg-opacity-25' : '' }}">
                                                <input type="checkbox" name="{{ $fieldYes }}" value="1" 
                                                       class="form-check-input claim-check border-danger" 
                                                       data-day="{{ $day }}" data-code="{{ $code }}" data-title="{{ $title }}"
                                                       {{ $isChecked ? 'checked' : '' }}
                                                       onchange="onClaimCheckChanged(this)">
                                            </td>
                                            <td class="text-muted small">
                                                <i class="fas fa-times opacity-25"></i>
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <span class="badge bg-light text-muted border small">Standard</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center p-3 bg-light border-top flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAddClaimModal()">
                        <i class="fas fa-plus-circle me-1"></i> Add New Cause of Claim (Custom Item)
                    </button>
                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i> You can add extra contractual claim causes under any category with daily tracking</small>
                </div>
                <div class="card-body border-top">
                    <div class="alert alert-warning mb-3 small">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>N.B:</strong> Detailed information of any claim case encountered must be filled and submitted on <strong>Page 02</strong> (the next tab).
                    </div>
                    <label class="form-label fw-bold">Page 1 General Notes / Observations</label>
                    <textarea name="page1_notes" class="form-control" rows="2" placeholder="Optional notes for Page 1...">{{ old('page1_notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: Detailed Information of Claim Event -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="page2" role="tabpanel">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-dark fw-bold">Detailed Information of the Claim Event</h5>
                        <small class="text-muted">Format TNT/CAD/01 Page 02 - Record individual occurrences, impacts on progress, and corrective actions.</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addClaimEventRow()">
                        <i class="fas fa-plus me-1"></i> Add Claim Event Row
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" id="claimEventsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 140px;">Date</th>
                                <th style="width: 140px;">Issue No.</th>
                                <th>Detail Description of the Cause</th>
                                <th>Effect on the Progress</th>
                                <th>Action Taken</th>
                                <th>Remark</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="claimEventsBody">
                            <!-- Rows added dynamically or rendered from old input -->
                        </tbody>
                    </table>
                </div>
                <div class="card-body border-top">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addClaimEventRow()">
                            <i class="fas fa-plus me-1"></i> Add Another Event Row
                        </button>
                        <span class="text-muted small"><span id="eventRowCount">0</span> claim event(s) recorded</span>
                    </div>

                    <label class="form-label fw-bold">Overall Claim Events Remark (Page 2)</label>
                    <textarea name="page2_remark" class="form-control" rows="3" placeholder="Summary remarks on weekly claim events, time extensions requested, or contractual impacts...">{{ old('page2_remark') }}</textarea>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: Site Correspondence - Follow up Form -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="page3" role="tabpanel">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-dark fw-bold">Site Correspondence - Follow up Form</h5>
                        <small class="text-muted">Track all incoming and outgoing letters exchanged throughout the week.</small>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllCheckboxes('corresp')">
                        <i class="fas fa-undo me-1"></i> Clear All
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 text-center">
                        <thead class="table-dark">
                            <tr>
                                <th rowspan="2" class="text-start" style="width: 40%;">Correspondences</th>
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
                            @foreach($correspondenceCategories as $dirKey => $dirData)
                                <tr class="table-info text-start fw-bold">
                                    <td colspan="15" class="py-2">
                                        <i class="fas {{ $dirKey == 'incoming' ? 'fa-inbox' : 'fa-paper-plane' }} me-2"></i>{{ $dirData['title'] }}
                                    </td>
                                </tr>
                                @foreach($dirData['items'] as $code => $party)
                                    @php $key = $dirKey . '_' . str_replace('.', '_', $code); @endphp
                                    <tr>
                                        <td class="text-start ps-4">
                                            <span class="badge bg-secondary me-2">{{ $code }}</span>
                                            {{ $party }}
                                        </td>
                                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                            @php 
                                                $fieldYes = "correspondences[{$key}][{$day}]";
                                                $isChecked = old("correspondences.{$key}.{$day}", false);
                                            @endphp
                                            <td class="bg-light">
                                                <input type="checkbox" name="{{ $fieldYes }}" value="1" 
                                                       class="form-check-input corresp-check border-info" 
                                                       {{ $isChecked ? 'checked' : '' }}
                                                       onchange="highlightChecked(this)">
                                            </td>
                                            <td class="text-muted small">
                                                <i class="fas fa-times opacity-25"></i>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-body border-top">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Correspondence Remark</label>
                            <textarea name="page3_remark" class="form-control" rows="3" placeholder="Remarks regarding letters, responses, or pending correspondence...">{{ old('page3_remark') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <i class="fas fa-paperclip text-primary me-1"></i> Attach Letters & Documents
                                <span class="badge bg-light text-dark ms-1">Note: The Letters shall be attached</span>
                            </label>
                            <input type="file" name="attachments[]" class="form-control mb-2" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx">
                            <div class="form-text small">
                                You can upload scanned copies of incoming/outgoing letters, work orders, drawings, or site photos (PDF, Word, Images up to 10MB each).
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sign-off & Submit Bar -->
    <div class="card shadow-sm border-0 mb-5">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="fw-bold">Prepared By: <span class="text-primary">{{ auth()->user()?->name ?? 'Site Engineer' }}</span></div>
                <small class="text-muted">Report will be saved in <strong>Draft</strong> status and can be submitted for verification anytime.</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('contractual-reports.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary px-4 fw-bold">
                    <i class="fas fa-save me-1"></i> Save Report
                </button>
            </div>
        </div>
    </div>
</form>

<script>
function toggleFurtherActionNotes(val) {
    const div = document.getElementById('further_action_notes_div');
    if (div) {
        div.style.display = (val === '1' || val === 1) ? 'block' : 'none';
        if (val === '1' || val === 1) {
            const input = div.querySelector('input[name="further_action_notes"]');
            if (input) input.focus();
        }
    }
}

// Day to Monday offset mapping
const dayOffsetMap = {
    'monday': 0,
    'tuesday': 1,
    'wednesday': 2,
    'thursday': 3,
    'friday': 4,
    'saturday': 5,
    'sunday': 6
};

// Calculate specific calendar date (YYYY-MM-DD) for a given weekday based on week_start_date
function calculateEventDate(day) {
    const weekStartInput = document.getElementById('week_start_date');
    if (!weekStartInput || !weekStartInput.value) {
        return '';
    }
    const offset = dayOffsetMap[day.toLowerCase()];
    if (offset === undefined) return weekStartInput.value;

    const parts = weekStartInput.value.split('-');
    if (parts.length !== 3) return weekStartInput.value;

    const year = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10) - 1;
    const date = parseInt(parts[2], 10);

    const d = new Date(year, month, date);
    d.setDate(d.getDate() + offset);

    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
}

// Auto-adjust Sunday date when Monday changes and update synced claim events
document.getElementById('week_start_date').addEventListener('change', function() {
    if (this.value) {
        const parts = this.value.split('-');
        if (parts.length === 3) {
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;
            const d = parseInt(parts[2], 10);
            const end = new Date(y, m, d);
            end.setDate(end.getDate() + 6);
            const ey = end.getFullYear();
            const em = String(end.getMonth() + 1).padStart(2, '0');
            const ed = String(end.getDate()).padStart(2, '0');
            document.getElementById('week_end_date').value = `${ey}-${em}-${ed}`;
        }

        // Auto-recalculate dates on all synced claim event rows on Page 2
        document.querySelectorAll('#claimEventsBody tr[data-sync-day]').forEach(tr => {
            const day = tr.getAttribute('data-sync-day');
            const newDate = calculateEventDate(day);
            if (newDate) {
                const dateInput = tr.querySelector('input[type="date"]');
                if (dateInput) {
                    dateInput.value = newDate;
                    flashField(dateInput);
                }
            }
        });
    }
});

// Dynamic Claim Events table
let eventIndex = 0;
const issueOptions = [
    { code: '1.1', title: '1.1 Miss drawing' },
    { code: '1.2', title: '1.2 Design discrepancies' },
    { code: '1.3', title: '1.3 Design modification at site level' },
    { code: '2.1', title: '2.1 Works indicated on drawing & missed in BOQ' },
    { code: '2.2', title: '2.2 Additional work order given' },
    { code: '2.3', title: '2.3 Change in work item (quality, shape/dim, qty)' },
    { code: '3.1', title: '3.1 Bad Weather conditions & natural hazards' },
    { code: '3.2', title: '3.2 Delay due to inspection / approval' },
    { code: '3.3', title: '3.3 Force majeure issues' },
    { code: '3.4', title: '3.4 Artificial conditions / physical obstructions' },
    { code: '3.5', title: '3.5 Other external factors' },
    { code: 'Other', title: 'Other Issue' }
];

function ensureIssueOptionExists(code, title) {
    if (!code) return;
    const exists = issueOptions.some(opt => opt.code === code);
    if (!exists) {
        issueOptions.push({
            code: code,
            title: title ? `${code} ${title}` : code
        });
    }
}

function isClaimEventRowBlank(tr) {
    const select = tr.querySelector('select[name$="[issue_number]"]');
    const causeDesc = tr.querySelector('textarea[name$="[cause_description]"]');
    const effect = tr.querySelector('textarea[name$="[effect_on_progress]"]');
    const action = tr.querySelector('textarea[name$="[action_taken]"]');
    const remark = tr.querySelector('input[name$="[remark]"]');

    const issueVal = select ? select.value.trim() : '';
    const causeVal = causeDesc ? causeDesc.value.trim() : '';
    const effectVal = effect ? effect.value.trim() : '';
    const actionVal = action ? action.value.trim() : '';
    const remarkVal = remark ? remark.value.trim() : '';

    return !issueVal && !causeVal && !effectVal && !actionVal && !remarkVal;
}

function addClaimEventRow(data = {}) {
    const tbody = document.getElementById('claimEventsBody');
    const tr = document.createElement('tr');
    tr.id = 'event_row_' + eventIndex;

    if (data.sync_key) {
        tr.setAttribute('data-sync-key', data.sync_key);
        tr.setAttribute('data-sync-day', data.sync_day);
        tr.setAttribute('data-sync-code', data.sync_code);
    }

    if (data.issue_number) {
        ensureIssueOptionExists(data.issue_number, data.cause_description);
    }

    let optionsHtml = '<option value="">-- Issue # --</option>';
    issueOptions.forEach(opt => {
        const sel = (data.issue_number === opt.code) ? 'selected' : '';
        optionsHtml += `<option value="${opt.code}" ${sel}>${opt.title}</option>`;
    });

    const defaultDate = data.event_date || document.getElementById('week_start_date').value;

    tr.innerHTML = `
        <td>
            <input type="date" name="events[${eventIndex}][event_date]" class="form-control form-control-sm" value="${defaultDate}">
        </td>
        <td>
            <select name="events[${eventIndex}][issue_number]" class="form-select form-select-sm" required>
                ${optionsHtml}
            </select>
        </td>
        <td>
            <textarea name="events[${eventIndex}][cause_description]" class="form-control form-control-sm" rows="2" placeholder="Cause details...">${data.cause_description || ''}</textarea>
        </td>
        <td>
            <textarea name="events[${eventIndex}][effect_on_progress]" class="form-control form-control-sm" rows="2" placeholder="Impact on critical path/delays...">${data.effect_on_progress || ''}</textarea>
        </td>
        <td>
            <textarea name="events[${eventIndex}][action_taken]" class="form-control form-control-sm" rows="2" placeholder="Mitigation / site instruction...">${data.action_taken || ''}</textarea>
        </td>
        <td>
            <input type="text" name="events[${eventIndex}][remark]" class="form-control form-control-sm" value="${data.remark || ''}" placeholder="Remark">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeClaimEventRow(${eventIndex})" title="Remove row">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    eventIndex++;
    updateEventCount();
}

function removeClaimEventRow(idx) {
    const row = document.getElementById('event_row_' + idx);
    if (row) {
        const syncCode = row.getAttribute('data-sync-code');
        const syncDay = row.getAttribute('data-sync-day');

        row.remove();

        // Reverse-sync: uncheck corresponding checkbox on Page 1
        if (syncCode && syncDay) {
            const cb = document.querySelector(`.claim-check[data-code="${syncCode}"][data-day="${syncDay}"]`);
            if (cb && cb.checked) {
                cb.checked = false;
                highlightChecked(cb);
            }
        }
        updateEventCount();
    }
}

function updateEventCount() {
    const tbody = document.getElementById('claimEventsBody');
    const count = tbody ? tbody.children.length : 0;

    const countEl = document.getElementById('eventRowCount');
    if (countEl) countEl.innerText = count;

    const tabBadge = document.getElementById('page2TabCount');
    if (tabBadge) {
        tabBadge.innerText = count + (count === 1 ? ' event' : ' events');
        if (count > 0) {
            tabBadge.className = 'badge bg-success text-white ms-1';
        } else {
            tabBadge.className = 'badge bg-warning text-dark ms-1';
        }
    }
}

function onClaimCheckChanged(cb) {
    highlightChecked(cb);
    syncClaimEventFromCheckbox(cb);
}

function syncClaimEventFromCheckbox(cb) {
    const code = cb.getAttribute('data-code');
    const day = cb.getAttribute('data-day');
    const title = cb.getAttribute('data-title') || code;
    if (!code || !day) return;

    const syncKey = `claim_${code}_${day}`;
    const tbody = document.getElementById('claimEventsBody');
    if (!tbody) return;

    if (cb.checked) {
        // Prevent duplicate row for this code + day
        const existing = tbody.querySelector(`tr[data-sync-key="${syncKey}"]`);
        if (existing) return;

        // If currently the only row in tbody is blank, remove it so the new event replaces the blank row
        if (tbody.children.length === 1 && isClaimEventRowBlank(tbody.children[0])) {
            tbody.children[0].remove();
        }

        const eventDate = calculateEventDate(day);
        ensureIssueOptionExists(code, title);

        addClaimEventRow({
            event_date: eventDate,
            issue_number: code,
            cause_description: title,
            effect_on_progress: '',
            action_taken: '',
            remark: '',
            sync_key: syncKey,
            sync_day: day,
            sync_code: code
        });

        const dayName = day.charAt(0).toUpperCase() + day.slice(1);
        showSyncToast(`Synced to Page 2: ${code} - ${title} (${dayName} ${eventDate})`);
    } else {
        const existing = tbody.querySelector(`tr[data-sync-key="${syncKey}"]`);
        if (existing) {
            existing.remove();
            updateEventCount();
            const dayName = day.charAt(0).toUpperCase() + day.slice(1);
            showSyncToast(`Removed from Page 2: ${code} (${dayName})`);
        }
    }
}

function showSyncToast(message) {
    let toastContainer = document.getElementById('syncToastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'syncToastContainer';
        toastContainer.style.position = 'fixed';
        toastContainer.style.bottom = '20px';
        toastContainer.style.right = '20px';
        toastContainer.style.zIndex = '9999';
        toastContainer.style.display = 'flex';
        toastContainer.style.flexDirection = 'column';
        toastContainer.style.gap = '8px';
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.className = 'alert alert-primary py-2 px-3 shadow-lg border-primary d-flex align-items-center mb-0 fade show';
    toast.style.minWidth = '300px';
    toast.style.maxWidth = '440px';
    toast.style.fontSize = '0.85rem';
    toast.innerHTML = `
        <i class="fas fa-bolt text-warning me-2 fs-6"></i>
        <div class="flex-grow-1">${message}</div>
        <button type="button" class="btn-close ms-2" style="font-size: 0.65rem;" onclick="this.parentElement.remove()"></button>
    `;
    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 2800);
}

function clearAllCheckboxes(type) {
    const selector = type === 'claims' ? '.claim-check' : '.corresp-check';
    document.querySelectorAll(selector).forEach(cb => {
        if (cb.checked) {
            cb.checked = false;
            highlightChecked(cb);
            if (type === 'claims') {
                syncClaimEventFromCheckbox(cb);
            }
        }
    });
}

function highlightChecked(cb) {
    if (cb.checked) {
        cb.closest('td').classList.add('bg-warning', 'bg-opacity-25');
    } else {
        cb.closest('td').classList.remove('bg-warning', 'bg-opacity-25');
    }
}

// Auto-fill project details when a project is selected
function applySelectedProjectDetails(isUserChange = false) {
    const select = document.getElementById('project_select');
    if (!select) return;
    const option = select.options[select.selectedIndex];
    if (!option || !option.value) {
        if (isUserChange) {
            document.getElementById('project_location').value = '';
            document.getElementById('client_name').value = '';
            document.getElementById('consultant_name').value = '';
            document.getElementById('contractor_name').value = '';
        }
        return;
    }

    const location = option.getAttribute('data-location') || '';
    const client = option.getAttribute('data-client') || '';
    const consultant = option.getAttribute('data-consultant') || '';
    const contractor = option.getAttribute('data-contractor') || 'TNT Construction and Trading';

    const locInput = document.getElementById('project_location');
    const clientInput = document.getElementById('client_name');
    const consultantInput = document.getElementById('consultant_name');
    const contractorInput = document.getElementById('contractor_name');

    if (locInput) {
        locInput.value = location;
        if (isUserChange) flashField(locInput);
    }
    if (clientInput) {
        clientInput.value = client;
        if (isUserChange) flashField(clientInput);
    }
    if (consultantInput) {
        consultantInput.value = consultant;
        if (isUserChange) flashField(consultantInput);
    }
    if (contractorInput) {
        contractorInput.value = contractor;
        if (isUserChange) flashField(contractorInput);
    }
}

function flashField(element) {
    element.classList.add('bg-success', 'bg-opacity-10', 'border-success');
    setTimeout(() => {
        element.classList.remove('bg-success', 'bg-opacity-10', 'border-success');
    }, 1200);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const projectSelect = document.getElementById('project_select');
    if (projectSelect) {
        projectSelect.addEventListener('change', function() {
            applySelectedProjectDetails(true);
        });

        // Always auto-fill project details if a project is selected
        if (projectSelect.value) {
            applySelectedProjectDetails(false);
        }
    }

    @if(old('custom_claims'))
        const oldCustomClaims = @json(old('custom_claims'));
        Object.values(oldCustomClaims).forEach(c => {
            appendCustomClaimRow({
                category: c.category || 'other',
                category_title: c.category_title || 'Other Contractual Causes',
                item_code: c.item_code || '',
                item_title: c.item_title || '',
                monday: !!c.monday,
                tuesday: !!c.tuesday,
                wednesday: !!c.wednesday,
                thursday: !!c.thursday,
                friday: !!c.friday,
                saturday: !!c.saturday,
                sunday: !!c.sunday
            });
        });
    @endif

    @if(old('events'))
        const oldEvents = @json(old('events'));
        Object.values(oldEvents).forEach(evt => {
            const formattedDate = evt.event_date || '';
            let syncKey = null;
            let syncDay = null;
            let syncCode = evt.issue_number;
            if (formattedDate && evt.issue_number) {
                for (const [dayName, offset] of Object.entries(dayOffsetMap)) {
                    if (calculateEventDate(dayName) === formattedDate) {
                        syncKey = `claim_${evt.issue_number}_${dayName}`;
                        syncDay = dayName;
                        break;
                    }
                }
            }
            addClaimEventRow({
                event_date: formattedDate,
                issue_number: evt.issue_number,
                cause_description: evt.cause_description,
                effect_on_progress: evt.effect_on_progress,
                action_taken: evt.action_taken,
                remark: evt.remark,
                sync_key: syncKey,
                sync_day: syncDay,
                sync_code: syncCode
            });
        });
    @else
        if (document.getElementById('claimEventsBody').children.length === 0) {
            addClaimEventRow();
        }
        // If checkboxes were restored via old('claims'), sync them to Page 2
        document.querySelectorAll('#claimsTableBody .claim-check:checked').forEach(cb => {
            syncClaimEventFromCheckbox(cb);
        });
    @endif
    updateEventCount();
});

let customClaimIndex = 0;
let addClaimModalInstance = null;

function openAddClaimModal() {
    if (!addClaimModalInstance) {
        const modalEl = document.getElementById('addClaimModal');
        if (modalEl) addClaimModalInstance = new bootstrap.Modal(modalEl);
    }
    
    // Reset inputs
    document.getElementById('newClaimTitle').value = '';
    document.getElementById('customCategoryTitle').value = '';
    document.getElementById('customCategoryDiv').style.display = 'none';
    
    ['m_mon', 'm_tue', 'm_wed', 'm_thu', 'm_fri', 'm_sat', 'm_sun'].forEach(id => {
        const cb = document.getElementById(id);
        if (cb) cb.checked = false;
    });

    const select = document.getElementById('newClaimCategorySelect');
    if (select) {
        select.value = 'suspension_of_works';
        handleCategorySelection('suspension_of_works');
    }

    if (addClaimModalInstance) addClaimModalInstance.show();
}

function handleCategorySelection(val) {
    const customDiv = document.getElementById('customCategoryDiv');
    if (val === '__custom__') {
        customDiv.style.display = 'block';
        document.getElementById('newClaimCode').value = suggestNextClaimCode('custom');
    } else {
        customDiv.style.display = 'none';
        document.getElementById('newClaimCode').value = suggestNextClaimCode(val);
    }
}

function suggestNextClaimCode(catKey) {
    const select = document.getElementById('newClaimCategorySelect');
    const opt = select.querySelector(`option[value="${catKey}"]`);
    const prefix = opt ? opt.getAttribute('data-prefix') : 'C-';
    
    if (catKey === 'custom' || !prefix) {
        return 'C-' + (document.querySelectorAll('.custom-claim-row').length + 1);
    }

    let maxSub = 0;
    document.querySelectorAll('#claimsTableBody tr[data-code]').forEach(tr => {
        const code = tr.getAttribute('data-code');
        if (code && code.startsWith(prefix)) {
            const parts = code.split('.');
            if (parts.length >= 2) {
                const sub = parseInt(parts[1], 10);
                if (!isNaN(sub) && sub > maxSub) maxSub = sub;
            }
        }
    });

    return prefix + (maxSub + 1);
}

function confirmAddClaimRow() {
    const select = document.getElementById('newClaimCategorySelect');
    const catVal = select.value;
    let catTitle = '';
    let catKey = catVal;

    if (catVal === '__custom__') {
        catTitle = document.getElementById('customCategoryTitle').value.trim();
        if (!catTitle) {
            alert('Please enter a custom category title.');
            document.getElementById('customCategoryTitle').focus();
            return;
        }
        catKey = 'custom_' + catTitle.toLowerCase().replace(/[^a-z0-9]/g, '_');
    } else {
        const opt = select.options[select.selectedIndex];
        catTitle = opt.getAttribute('data-title') || opt.text;
    }

    const code = document.getElementById('newClaimCode').value.trim();
    const title = document.getElementById('newClaimTitle').value.trim();

    if (!code) {
        alert('Please enter an item code (e.g. 3.6).');
        document.getElementById('newClaimCode').focus();
        return;
    }
    if (!title) {
        alert('Please enter a description for the cause of claim.');
        document.getElementById('newClaimTitle').focus();
        return;
    }

    const exists = document.querySelector(`#claimsTableBody tr[data-code="${code}"]`);
    if (exists) {
        alert(`An item with code "${code}" already exists in the checklist.`);
        document.getElementById('newClaimCode').focus();
        return;
    }

    const mon = document.getElementById('m_mon').checked;
    const tue = document.getElementById('m_tue').checked;
    const wed = document.getElementById('m_wed').checked;
    const thu = document.getElementById('m_thu').checked;
    const fri = document.getElementById('m_fri').checked;
    const sat = document.getElementById('m_sat').checked;
    const sun = document.getElementById('m_sun').checked;

    appendCustomClaimRow({
        category: catKey,
        category_title: catTitle,
        item_code: code,
        item_title: title,
        monday: mon,
        tuesday: tue,
        wednesday: wed,
        thursday: thu,
        friday: fri,
        saturday: sat,
        sunday: sun
    });

    // Also register into Page 2 issueOptions so it can be picked in Claim Events
    issueOptions.push({ code: code, title: `${code} ${title}` });

    if (addClaimModalInstance) addClaimModalInstance.hide();
}

function appendCustomClaimRow(data) {
    const tbody = document.getElementById('claimsTableBody');
    const idx = customClaimIndex++;

    let headerRow = tbody.querySelector(`tr.category-header-row[data-cat-key="${data.category}"]`);
    if (!headerRow) {
        headerRow = document.createElement('tr');
        headerRow.className = 'table-primary text-start fw-bold category-header-row';
        headerRow.setAttribute('data-cat-key', data.category);
        headerRow.innerHTML = `<td colspan="16" class="py-2"><i class="fas fa-folder-plus me-2"></i>${data.category_title}</td>`;
        tbody.appendChild(headerRow);
    }

    const tr = document.createElement('tr');
    tr.className = 'custom-claim-row table-warning bg-opacity-10';
    tr.setAttribute('data-cat-key', data.category);
    tr.setAttribute('data-code', data.item_code);
    tr.setAttribute('data-title', data.item_title);

    const safeTitle = (data.item_title || '').replace(/"/g, '&quot;');

    const days = [
        { key: 'monday', checked: data.monday },
        { key: 'tuesday', checked: data.tuesday },
        { key: 'wednesday', checked: data.wednesday },
        { key: 'thursday', checked: data.thursday },
        { key: 'friday', checked: data.friday },
        { key: 'saturday', checked: data.saturday },
        { key: 'sunday', checked: data.sunday }
    ];

    let daysHtml = '';
    days.forEach(d => {
        const isChecked = d.checked ? 'checked' : '';
        const bgCls = d.checked ? 'bg-warning bg-opacity-25' : '';
        daysHtml += `
            <td class="bg-light ${bgCls}">
                <input type="checkbox" name="custom_claims[${idx}][${d.key}]" value="1"
                       class="form-check-input claim-check border-danger" ${isChecked}
                       data-day="${d.key}" data-code="${data.item_code}" data-title="${safeTitle}"
                       onchange="onClaimCheckChanged(this)">
            </td>
            <td class="text-muted small">
                <i class="fas fa-times opacity-25"></i>
            </td>
        `;
    });

    tr.innerHTML = `
        <td class="text-start ps-4">
            <input type="hidden" name="custom_claims[${idx}][category]" value="${data.category}">
            <input type="hidden" name="custom_claims[${idx}][category_title]" value="${data.category_title}">
            <input type="hidden" name="custom_claims[${idx}][item_code]" value="${data.item_code}">
            <input type="hidden" name="custom_claims[${idx}][item_title]" value="${data.item_title}">
            <span class="badge bg-primary me-2">${data.item_code}</span>
            <span class="fw-semibold">${data.item_title}</span>
            <span class="badge bg-info text-dark ms-2 small">Custom</span>
        </td>
        ${daysHtml}
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCustomClaimRow(this, '${data.item_code}')" title="Delete this custom Cause of Claim">
                <i class="fas fa-trash-alt"></i>
            </button>
        </td>
    `;

    let lastRow = headerRow;
    let nextEl = headerRow.nextElementSibling;
    while (nextEl && nextEl.getAttribute('data-cat-key') === data.category && !nextEl.classList.contains('category-header-row')) {
        lastRow = nextEl;
        nextEl = nextEl.nextElementSibling;
    }
    lastRow.insertAdjacentElement('afterend', tr);

    // Sync any pre-checked days to Page 2
    tr.querySelectorAll('.claim-check:checked').forEach(cb => {
        syncClaimEventFromCheckbox(cb);
    });
}

function removeCustomClaimRow(btn, code) {
    if (confirm(`Remove custom cause of claim ${code ? '"' + code + '"' : ''}?`)) {
        const row = btn.closest('tr');
        const catKey = row.getAttribute('data-cat-key');
        row.remove();

        // Also remove any Page 2 claim events linked to this custom code
        if (code) {
            document.querySelectorAll(`#claimEventsBody tr[data-sync-code="${code}"]`).forEach(evtRow => {
                evtRow.remove();
            });
            updateEventCount();
        }

        const remaining = document.querySelectorAll(`#claimsTableBody tr[data-cat-key="${catKey}"]:not(.category-header-row)`);
        if (remaining.length === 0) {
            const header = document.querySelector(`#claimsTableBody tr.category-header-row[data-cat-key="${catKey}"]`);
            if (header && catKey.startsWith('custom_')) header.remove();
        }
    }
}
</script>

<!-- Modal: Add Custom Cause of Claim -->
<div class="modal fade" id="addClaimModal" tabindex="-1" aria-labelledby="addClaimModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addClaimModalLabel">
                    <i class="fas fa-plus-circle me-2"></i> Add New Cause of Claim
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Category <span class="text-danger">*</span></label>
                    <select class="form-select" id="newClaimCategorySelect" onchange="handleCategorySelection(this.value)">
                        <option value="design_issues" data-title="Design related issues" data-prefix="1.">1. Design related issues</option>
                        <option value="work_orders" data-title="Work Orders" data-prefix="2.">2. Work Orders</option>
                        <option value="suspension_of_works" data-title="Suspension of works" data-prefix="3.">3. Suspension of works</option>
                        <option value="row_issues" data-title="Right of Way (ROW) issues" data-prefix="4.">4. Right of Way (ROW) issues</option>
                        <option value="financial_issues" data-title="Payment & Financial issues" data-prefix="5.">5. Payment & Financial issues</option>
                        <option value="material_issues" data-title="Materials & Equipment issues" data-prefix="6.">6. Materials & Equipment issues</option>
                        <option value="other" data-title="Other Contractual Causes" data-prefix="7.">7. Other Contractual Causes</option>
                        <option value="__custom__">➕ Add New Custom Category...</option>
                    </select>
                </div>

                <div class="mb-3" id="customCategoryDiv" style="display: none;">
                    <label class="form-label fw-bold">Custom Category Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="customCategoryTitle" placeholder="e.g. Environmental Obstacles & Local Permits">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Item Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="newClaimCode" placeholder="e.g. 3.6" required>
                        <small class="text-muted">Unique identifier</small>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Cause of Claim Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="newClaimTitle" placeholder="e.g. Delay in employer payment certificate" required>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold small text-muted text-uppercase">Pre-check Applicable Days (Optional):</label>
                    <div class="d-flex justify-content-between bg-light p-2 rounded border">
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input" type="checkbox" id="m_mon">
                            <label class="form-check-label small" for="m_mon">Mon</label>
                        </div>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input" type="checkbox" id="m_tue">
                            <label class="form-check-label small" for="m_tue">Tue</label>
                        </div>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input" type="checkbox" id="m_wed">
                            <label class="form-check-label small" for="m_wed">Wed</label>
                        </div>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input" type="checkbox" id="m_thu">
                            <label class="form-check-label small" for="m_thu">Thu</label>
                        </div>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input" type="checkbox" id="m_fri">
                            <label class="form-check-label small" for="m_fri">Fri</label>
                        </div>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input" type="checkbox" id="m_sat">
                            <label class="form-check-label small" for="m_sat">Sat</label>
                        </div>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input" type="checkbox" id="m_sun">
                            <label class="form-check-label small" for="m_sun">Sun</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmAddClaimRow()">
                    <i class="fas fa-plus me-1"></i> Add to Checklist
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
