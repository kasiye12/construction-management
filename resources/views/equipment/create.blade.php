@extends('layouts.app')

@section('title', 'Add New Equipment')

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-plus-circle text-primary me-2"></i> Register New Equipment / Fleet Asset</h5>
                        <small class="text-muted">TNT Construction & Trading • Equipment Administration & Maintenance Department (EAMD)</small>
                    </div>
                    <a href="{{ route('equipment.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Fleet
                    </a>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('equipment.store') }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-12"><h6 class="text-primary fw-bold border-bottom pb-2">1. Equipment Identification & Specification</h6></div>
                            
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Equipment Type <span class="text-danger">*</span></label>
                                <input type="text" name="equipment_type" class="form-control" placeholder="e.g. EXCAVATOR, DUMP TRUCK, PICK UP, GENERATOR" value="{{ old('equipment_type') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Fleet Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required id="categorySelect">
                                    @foreach($categories as $catKey => $catLabel)
                                        <option value="{{ $catKey }}" {{ old('category') == $catKey ? 'selected' : '' }}>{{ $catLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Plate / Serial No <span class="text-danger">*</span></label>
                                <input type="text" name="plate_serial_no" class="form-control" placeholder="e.g. EX-0889, 3-50327, 36KVA" value="{{ old('plate_serial_no') }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Make / Manufacturer</label>
                                <input type="text" name="make" class="form-control" placeholder="e.g. CATERPILLAR, KOMATSU, SINOTRUK, TOYOTA" value="{{ old('make') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Model</label>
                                <input type="text" name="model" class="form-control" placeholder="e.g. PC200-8, CAT 320D, HOWO 371" value="{{ old('model') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Serial / VIN / Chassis No</label>
                                <input type="text" name="serial_no" class="form-control" placeholder="e.g. KMT20098231" value="{{ old('serial_no') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Meter Reading Unit <span class="text-danger">*</span></label>
                                <select name="reading_unit" class="form-select" required id="readingUnitSelect">
                                    <option value="hr" {{ old('reading_unit', 'hr') == 'hr' ? 'selected' : '' }}>Hours (hr) - Heavy & Plant</option>
                                    <option value="km" {{ old('reading_unit') == 'km' ? 'selected' : '' }}>Kilometers (km) - Light Vehicles / Trucks</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12"><h6 class="text-primary fw-bold border-bottom pb-2">2. Deployment & Operational Status</h6></div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Assigned Project</label>
                                <select name="project_id" class="form-select">
                                    <option value="">Central Workshop / Depot (Unassigned)</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Location / Project Site Name</label>
                                <input type="text" name="location_name" class="form-control" placeholder="e.g. Central Workshop, Ambo Referral, Arba Minch" value="{{ old('location_name', 'Central Workshop') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Operational Condition <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active / Operational</option>
                                    <option value="due_service" {{ old('status') == 'due_service' ? 'selected' : '' }}>Due For Service</option>
                                    <option value="under_maintenance" {{ old('status') == 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                                    <option value="breakdown" {{ old('status') == 'breakdown' ? 'selected' : '' }}>Down / Breakdown</option>
                                    <option value="idle" {{ old('status') == 'idle' ? 'selected' : '' }}>Idle / Standby</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12"><h6 class="text-primary fw-bold border-bottom pb-2">3. Service Meter Readings & Maintenance Targets</h6></div>

                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Current Reading <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" name="current_reading" class="form-control" placeholder="0.0" value="{{ old('current_reading', 0) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Current Reading Date</label>
                                <input type="date" name="current_reading_date" class="form-control" value="{{ old('current_reading_date', date('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Next Service Target Reading</label>
                                <input type="number" step="0.1" name="next_service_reading" class="form-control" placeholder="e.g. 250, 500, 5000" value="{{ old('next_service_reading') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Next Service Type</label>
                                <input type="text" name="next_service_type" class="form-control" placeholder="e.g. A, B, C, D or A2, B2" value="{{ old('next_service_type', 'A') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Last Service Reading</label>
                                <input type="number" step="0.1" name="last_service_reading" class="form-control" placeholder="e.g. 0" value="{{ old('last_service_reading') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Last Service Date</label>
                                <input type="date" name="last_service_date" class="form-control" value="{{ old('last_service_date') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Last Service Type</label>
                                <input type="text" name="last_service_type" class="form-control" placeholder="e.g. A, B, C or Overhaul" value="{{ old('last_service_type') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Remarks & Notes</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Specific filters required, warranty details, previous owner...">{{ old('remarks') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('equipment.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Save Equipment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('categorySelect').addEventListener('change', function() {
    var unitSelect = document.getElementById('readingUnitSelect');
    if (this.value === 'LIGHT VEHICLES' || this.value === 'DUMP TRUCK') {
        unitSelect.value = 'km';
    } else {
        unitSelect.value = 'hr';
    }
});
</script>
@endsection
