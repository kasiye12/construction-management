@extends('layouts.app')

@section('title', 'Create Preventive Maintenance Schedule (OF/EAMD/074)')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-dark fw-bold">
                <i class="fas fa-plus-circle text-primary me-2"></i> Prepare Preventive Maintenance Schedule
            </h1>
            <p class="text-muted small mb-0">Document №: <strong>OF/EAMD/074</strong> • TNT Construction & Trading • Equipment Administration & Maintenance Department</p>
        </div>
        <a href="{{ route('pm-schedules.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Schedules
        </a>
    </div>

    <form action="{{ route('pm-schedules.store') }}" method="POST" id="scheduleForm">
        @csrf

        {{-- Meta Card --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-file-alt text-primary me-2"></i> Schedule Information</h6>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Schedule Code <span class="text-danger">*</span></label>
                        <input type="text" name="schedule_code" class="form-control form-control-sm" value="{{ old('schedule_code', $code) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Schedule Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-sm" value="{{ old('title', 'Preventive Maintenance Schedule') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Project Site / Workshop</label>
                        <select name="project_id" class="form-select form-select-sm" id="projectSelect">
                            <option value="">All Projects / Central Workshop</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Location Name</label>
                        <input type="text" name="project_location" class="form-control form-control-sm" placeholder="e.g. Central Workshop, Ambo Referral" value="{{ old('project_location', 'All Sites / Central Workshop') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Schedule Date <span class="text-danger">*</span></label>
                        <input type="date" name="schedule_date" class="form-control form-control-sm" value="{{ old('schedule_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Period Start</label>
                        <input type="date" name="period_start" class="form-control form-control-sm" value="{{ old('period_start', now()->startOfWeek()->toDateString()) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Period End</label>
                        <input type="date" name="period_end" class="form-control form-control-sm" value="{{ old('period_end', now()->endOfWeek()->toDateString()) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Quick Populate</label>
                        <button type="button" class="btn btn-outline-primary btn-sm w-100" id="populateFromFleetBtn">
                            <i class="fas fa-sync-alt me-1"></i> Preload Registered Fleet
                        </button>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Notes & Instructions</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. High priority on loader LD-2232 and excavator EX-0601 hydraulic filters..." value="{{ old('notes') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Schedule Items Table Card --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list-check text-primary me-2"></i> Equipment Schedule Rows</h6>
                <button type="button" class="btn btn-sm btn-outline-success" id="addRowBtn">
                    <i class="fas fa-plus me-1"></i> Add Custom Row
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0" id="scheduleItemsTable" style="font-size: 0.8rem;">
                        <thead class="table-light text-center text-muted">
                            <tr>
                                <th style="width: 3%;">#</th>
                                <th style="width: 12%;">Category</th>
                                <th style="width: 10%;">Type</th>
                                <th style="width: 8%;">Make</th>
                                <th style="width: 8%;">Model</th>
                                <th style="width: 8%;">Plate / SN</th>
                                <th style="width: 8%;">Location</th>
                                <th style="width: 7%;">Current Reading</th>
                                <th style="width: 7%;">Last Reading</th>
                                <th style="width: 5%;">Last Type</th>
                                <th style="width: 7%;">Next Target</th>
                                <th style="width: 5%;">Next Type</th>
                                <th style="width: 13%;">Remarks</th>
                                <th style="width: 3%;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            @php $index = 0; @endphp
                            @forelse($equipments as $eq)
                                <tr data-row="{{ $index }}">
                                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="items[{{ $index }}][equipment_id]" value="{{ $eq->id }}">
                                        <select name="items[{{ $index }}][category]" class="form-select form-select-sm p-1">
                                            <option value="EARTH MOVING MACHINE" {{ $eq->category == 'EARTH MOVING MACHINE' ? 'selected' : '' }}>Earth Moving</option>
                                            <option value="DUMP TRUCK" {{ $eq->category == 'DUMP TRUCK' ? 'selected' : '' }}>Dump Truck</option>
                                            <option value="LIGHT VEHICLES" {{ $eq->category == 'LIGHT VEHICLES' ? 'selected' : '' }}>Light Vehicles</option>
                                            <option value="AUXILIARY" {{ $eq->category == 'AUXILIARY' ? 'selected' : '' }}>Auxiliary & Plant</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="items[{{ $index }}][equipment_type]" class="form-control form-control-sm p-1" value="{{ $eq->equipment_type }}" required></td>
                                    <td><input type="text" name="items[{{ $index }}][make]" class="form-control form-control-sm p-1" value="{{ $eq->make }}"></td>
                                    <td><input type="text" name="items[{{ $index }}][model]" class="form-control form-control-sm p-1" value="{{ $eq->model }}"></td>
                                    <td><input type="text" name="items[{{ $index }}][plate_serial_no]" class="form-control form-control-sm p-1 fw-bold" value="{{ $eq->plate_serial_no }}"></td>
                                    <td><input type="text" name="items[{{ $index }}][location]" class="form-control form-control-sm p-1" value="{{ $eq->location_name }}"></td>
                                    <td><input type="number" step="0.1" name="items[{{ $index }}][current_reading]" class="form-control form-control-sm p-1 text-end" value="{{ $eq->current_reading }}"></td>
                                    <td><input type="number" step="0.1" name="items[{{ $index }}][last_service_reading]" class="form-control form-control-sm p-1 text-end" value="{{ $eq->last_service_reading }}"></td>
                                    <td><input type="text" name="items[{{ $index }}][last_service_type]" class="form-control form-control-sm p-1 text-center" value="{{ $eq->last_service_type }}"></td>
                                    <td><input type="number" step="0.1" name="items[{{ $index }}][next_service_reading]" class="form-control form-control-sm p-1 text-end fw-bold text-primary" value="{{ $eq->next_service_reading }}"></td>
                                    <td><input type="text" name="items[{{ $index }}][next_service_type]" class="form-control form-control-sm p-1 text-center fw-bold" value="{{ $eq->next_service_type ?: 'A' }}"></td>
                                    <td><input type="text" name="items[{{ $index }}][remark]" class="form-control form-control-sm p-1" value="{{ $eq->remarks }}"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm p-0 px-1 remove-row-btn" title="Remove row">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                @php $index++; @endphp
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center py-3 text-muted">No equipment loaded. Click "Add Custom Row" to start.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted" id="rowCountDisplay">Total items: {{ $equipments->count() }}</span>
                <div class="d-flex gap-2">
                    <a href="{{ route('pm-schedules.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fas fa-save me-1"></i> Save Draft Schedule
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let rowIndex = {{ $equipments->count() }};

document.getElementById('addRowBtn').addEventListener('click', function() {
    let tbody = document.getElementById('itemsTableBody');
    let tr = document.createElement('tr');
    tr.setAttribute('data-row', rowIndex);
    tr.innerHTML = `
        <td class="text-center fw-bold">${rowIndex + 1}</td>
        <td>
            <select name="items[${rowIndex}][category]" class="form-select form-select-sm p-1">
                <option value="EARTH MOVING MACHINE">Earth Moving</option>
                <option value="DUMP TRUCK">Dump Truck</option>
                <option value="LIGHT VEHICLES">Light Vehicles</option>
                <option value="AUXILIARY">Auxiliary & Plant</option>
            </select>
        </td>
        <td><input type="text" name="items[${rowIndex}][equipment_type]" class="form-control form-control-sm p-1" placeholder="Type" required></td>
        <td><input type="text" name="items[${rowIndex}][make]" class="form-control form-control-sm p-1" placeholder="Make"></td>
        <td><input type="text" name="items[${rowIndex}][model]" class="form-control form-control-sm p-1" placeholder="Model"></td>
        <td><input type="text" name="items[${rowIndex}][plate_serial_no]" class="form-control form-control-sm p-1 fw-bold" placeholder="Plate/SN"></td>
        <td><input type="text" name="items[${rowIndex}][location]" class="form-control form-control-sm p-1" placeholder="Location"></td>
        <td><input type="number" step="0.1" name="items[${rowIndex}][current_reading]" class="form-control form-control-sm p-1 text-end" placeholder="0"></td>
        <td><input type="number" step="0.1" name="items[${rowIndex}][last_service_reading]" class="form-control form-control-sm p-1 text-end" placeholder="0"></td>
        <td><input type="text" name="items[${rowIndex}][last_service_type]" class="form-control form-control-sm p-1 text-center" placeholder="A"></td>
        <td><input type="number" step="0.1" name="items[${rowIndex}][next_service_reading]" class="form-control form-control-sm p-1 text-end fw-bold text-primary" placeholder="Target"></td>
        <td><input type="text" name="items[${rowIndex}][next_service_type]" class="form-control form-control-sm p-1 text-center fw-bold" value="A"></td>
        <td><input type="text" name="items[${rowIndex}][remark]" class="form-control form-control-sm p-1" placeholder="Remark"></td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm p-0 px-1 remove-row-btn" title="Remove row">
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
