@extends('layouts.app')

@section('title', 'Record Equipment Breakdown Report (OF/030)')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark fw-bold">
                <i class="fas fa-plus-circle text-danger me-2"></i> Record Equipment Breakdown Report
            </h1>
            <p class="text-muted small mb-0">Document №: <strong>OF/TNT/ EAMD/030</strong> • TNT Construction & Trading • Equipment Administration & Maintenance Department</p>
        </div>
        <a href="{{ route('equipment-breakdowns.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Reports
        </a>
    </div>

    <form action="{{ route('equipment-breakdowns.store') }}" method="POST" id="breakdownForm">
        @csrf

        {{-- Meta Card --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-file-alt text-primary me-2"></i> Report Information</h6>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Report Code <span class="text-danger">*</span></label>
                        <input type="text" name="report_code" class="form-control form-control-sm" value="{{ old('report_code', $code) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Report Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-sm" value="{{ old('title', 'Maintenance Report') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Report Date <span class="text-danger">*</span></label>
                        <input type="date" name="report_date" class="form-control form-control-sm" value="{{ old('report_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Project Site</label>
                        <select name="project_id" class="form-select form-select-sm">
                            <option value="">All Sites / Central Workshop</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Location / Scope Name</label>
                        <input type="text" name="location_name" class="form-control form-control-sm" value="{{ old('location_name', 'Central Workshop & Project Sites') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Notes & Remarks</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. Weekly fleet breakdown log..." value="{{ old('notes') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Breakdown Items Table Card --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list text-primary me-2"></i> Breakdown Case Records</h6>
                <button type="button" class="btn btn-sm btn-outline-success" id="addRowBtn">
                    <i class="fas fa-plus me-1"></i> Add Breakdown Entry
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0" id="breakdownTable" style="font-size: 0.8rem;">
                        <thead class="table-light text-center text-muted">
                            <tr>
                                <th style="width: 3%;">#</th>
                                <th style="width: 14%;">Category</th>
                                <th style="width: 12%;">Equipment Type</th>
                                <th style="width: 5%;">QTY</th>
                                <th style="width: 9%;">Plate No / SN</th>
                                <th style="width: 8%;">Down Date</th>
                                <th style="width: 15%;">Reason of Down</th>
                                <th style="width: 10%;">Action Taken</th>
                                <th style="width: 9%;">Location</th>
                                <th style="width: 9%;">Repair Status</th>
                                <th style="width: 8%;">Finished Date</th>
                                <th style="width: 3%;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <tr data-row="0">
                                <td class="text-center fw-bold">1</td>
                                <td>
                                    <select name="items[0][category]" class="form-select form-select-sm p-1">
                                        <option value="EARTH MOVING MACHINE AND PLANT">Earth Moving & Plant</option>
                                        <option value="DUMP TRUCK">Dump Truck</option>
                                        <option value="LIGHT VEHICLES">Light Vehicles</option>
                                        <option value="AUXILIARY">Auxiliary</option>
                                        <option value="INSURANCE CASE">Insurance Case</option>
                                    </select>
                                </td>
                                <td><input type="text" name="items[0][equipment_type]" class="form-control form-control-sm p-1" placeholder="e.g. EXCAVATOR" required></td>
                                <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm p-1 text-center" value="1" min="1" required></td>
                                <td><input type="text" name="items[0][plate_no_sn]" class="form-control form-control-sm p-1 fw-bold" placeholder="e.g. EX-0889"></td>
                                <td><input type="date" name="items[0][down_date]" class="form-control form-control-sm p-1" value="{{ date('Y-m-d') }}"></td>
                                <td><input type="text" name="items[0][reason_of_down]" class="form-control form-control-sm p-1" placeholder="e.g. Engine Overhaul, Hydraulic Leak"></td>
                                <td><input type="text" name="items[0][action_taken]" class="form-control form-control-sm p-1" placeholder="e.g. Down, Under Maintenance"></td>
                                <td><input type="text" name="items[0][location]" class="form-control form-control-sm p-1" placeholder="Site / Workshop" value="Central"></td>
                                <td>
                                    <select name="items[0][repair_status]" class="form-select form-select-sm p-1">
                                        <option value="down">Down</option>
                                        <option value="under_maintenance">Under Maintenance</option>
                                        <option value="proforma">Waiting Proforma</option>
                                        <option value="purchase">Waiting Purchase</option>
                                        <option value="repaired">Finished / Repaired</option>
                                    </select>
                                </td>
                                <td><input type="date" name="items[0][finished_date]" class="form-control form-control-sm p-1"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm p-0 px-1 remove-row-btn" title="Remove">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted" id="rowCountDisplay">Total items: 1</span>
                <div class="d-flex gap-2">
                    <a href="{{ route('equipment-breakdowns.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fas fa-save me-1"></i> Save Draft Report
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let rowIndex = 1;

document.getElementById('addRowBtn').addEventListener('click', function() {
    let tbody = document.getElementById('itemsTableBody');
    let tr = document.createElement('tr');
    tr.setAttribute('data-row', rowIndex);
    tr.innerHTML = `
        <td class="text-center fw-bold">${rowIndex + 1}</td>
        <td>
            <select name="items[${rowIndex}][category]" class="form-select form-select-sm p-1">
                <option value="EARTH MOVING MACHINE AND PLANT">Earth Moving & Plant</option>
                <option value="DUMP TRUCK">Dump Truck</option>
                <option value="LIGHT VEHICLES">Light Vehicles</option>
                <option value="AUXILIARY">Auxiliary</option>
                <option value="INSURANCE CASE">Insurance Case</option>
            </select>
        </td>
        <td><input type="text" name="items[${rowIndex}][equipment_type]" class="form-control form-control-sm p-1" placeholder="Type" required></td>
        <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm p-1 text-center" value="1" min="1" required></td>
        <td><input type="text" name="items[${rowIndex}][plate_no_sn]" class="form-control form-control-sm p-1 fw-bold" placeholder="Plate/SN"></td>
        <td><input type="date" name="items[${rowIndex}][down_date]" class="form-control form-control-sm p-1" value="${new Date().toISOString().split('T')[0]}"></td>
        <td><input type="text" name="items[${rowIndex}][reason_of_down]" class="form-control form-control-sm p-1" placeholder="Reason of down"></td>
        <td><input type="text" name="items[${rowIndex}][action_taken]" class="form-control form-control-sm p-1" placeholder="Action taken"></td>
        <td><input type="text" name="items[${rowIndex}][location]" class="form-control form-control-sm p-1" placeholder="Location"></td>
        <td>
            <select name="items[${rowIndex}][repair_status]" class="form-select form-select-sm p-1">
                <option value="down">Down</option>
                <option value="under_maintenance">Under Maintenance</option>
                <option value="proforma">Waiting Proforma</option>
                <option value="purchase">Waiting Purchase</option>
                <option value="repaired">Finished / Repaired</option>
            </select>
        </td>
        <td><input type="date" name="items[${rowIndex}][finished_date]" class="form-control form-control-sm p-1"></td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm p-0 px-1 remove-row-btn" title="Remove">
                <i class="fas fa-trash-alt"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    rowIndex++;
    updateCount();
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-row-btn')) {
        let tr = e.target.closest('tr');
        tr.remove();
        updateCount();
    }
});

function updateCount() {
    let rows = document.querySelectorAll('#itemsTableBody tr');
    document.getElementById('rowCountDisplay').innerText = `Total items: ${rows.length}`;
}
</script>
@endsection
