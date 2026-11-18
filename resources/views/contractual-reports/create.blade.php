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
                    <label class="form-label fw-bold">Project Location</label>
                    <input type="text" name="project_location" id="project_location" class="form-control" 
                           value="{{ old('project_location', $selectedProject?->location) }}" placeholder="e.g. Addis Ababa / Megenagna">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Client</label>
                    <input type="text" name="client_name" id="client_name" class="form-control" 
                           value="{{ old('client_name', $selectedProject?->client_name) }}" placeholder="Client Name">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Consultant</label>
                    <input type="text" name="consultant_name" id="consultant_name" class="form-control" 
                           value="{{ old('consultant_name', $selectedProject?->consultant_name) }}" placeholder="Consultant Name">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Contractor</label>
                    <input type="text" name="contractor_name" id="contractor_name" class="form-control" 
                           value="{{ old('contractor_name', $selectedProject?->contractor_name ?? 'TNT CONSTRUCTION AND TRADING') }}">
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
                <span class="badge bg-warning text-dark ms-1">Log & Action</span>
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
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-dark fw-bold">Causes of Claim - Follow up Form</h5>
                        <small class="text-muted">Check "Yes" for any day when a contractual delay or claim issue was encountered.</small>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllCheckboxes('claims')">
                            <i class="fas fa-undo me-1"></i> Clear All
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0 text-center">
                        <thead class="table-dark">
                            <tr>
                                <th rowspan="2" class="text-start" style="width: 40%;">Claim Issues encountered throughout the week</th>
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
                            @foreach($claimCategories as $catKey => $catData)
                                <tr class="table-primary text-start fw-bold">
                                    <td colspan="15" class="py-2">
                                        <i class="fas fa-folder-open me-2"></i>{{ $catData['title'] }}
                                    </td>
                                </tr>
                                @foreach($catData['items'] as $code => $title)
                                    <tr>
                                        <td class="text-start ps-4">
                                            <span class="badge bg-secondary me-2">{{ $code }}</span>
                                            {{ $title }}
                                        </td>
                                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                            @php 
                                                $fieldYes = "claims[{$code}][{$day}]";
                                                $isChecked = old("claims.{$code}.{$day}", false);
                                            @endphp
                                            <td class="bg-light">
                                                <input type="checkbox" name="{{ $fieldYes }}" value="1" 
                                                       class="form-check-input claim-check border-danger" 
                                                       data-day="{{ $day }}" data-code="{{ $code }}"
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
// Auto-fill project fields
document.getElementById('project_select').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if (selected && selected.value) {
        document.getElementById('project_location').value = selected.dataset.location || '';
        document.getElementById('client_name').value = selected.dataset.client || '';
        document.getElementById('consultant_name').value = selected.dataset.consultant || '';
        document.getElementById('contractor_name').value = selected.dataset.contractor || 'TNT CONSTRUCTION AND TRADING';
    }
});

// Auto-adjust Sunday date when Monday changes
document.getElementById('week_start_date').addEventListener('change', function() {
    if (this.value) {
        const start = new Date(this.value);
        if (!isNaN(start.getTime())) {
            const end = new Date(start);
            end.setDate(start.getDate() + 6);
            document.getElementById('week_end_date').value = end.toISOString().split('T')[0];
        }
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

function addClaimEventRow(data = {}) {
    const tbody = document.getElementById('claimEventsBody');
    const tr = document.createElement('tr');
    tr.id = 'event_row_' + eventIndex;

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
    if (row) row.remove();
    updateEventCount();
}

function updateEventCount() {
    const count = document.getElementById('claimEventsBody').children.length;
    document.getElementById('eventRowCount').innerText = count;
}

function clearAllCheckboxes(type) {
    const selector = type === 'claims' ? '.claim-check' : '.corresp-check';
    document.querySelectorAll(selector).forEach(cb => {
        cb.checked = false;
        highlightChecked(cb);
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
            document.getElementById('contractor_name').value = 'TNT CONSTRUCTION AND TRADING';
        }
        return;
    }

    const location = option.getAttribute('data-location') || '';
    const client = option.getAttribute('data-client') || '';
    const consultant = option.getAttribute('data-consultant') || '';
    const contractor = option.getAttribute('data-contractor') || 'TNT CONSTRUCTION AND TRADING';

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

        // Auto-fill if project is pre-selected (via URL parameter or old input)
        if (projectSelect.value) {
            const locInput = document.getElementById('project_location');
            const clientInput = document.getElementById('client_name');
            const consultantInput = document.getElementById('consultant_name');
            if (!locInput.value || !clientInput.value || !consultantInput.value) {
                applySelectedProjectDetails(false);
            }
        }
    }

    if (document.getElementById('claimEventsBody').children.length === 0) {
        addClaimEventRow();
    }
});
</script>
@endsection
