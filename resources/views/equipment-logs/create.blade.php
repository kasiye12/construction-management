@extends('layouts.app')

@section('title', 'Log Spare Part / Maintenance Cost')

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-plus-circle text-success me-2"></i> Record Spare Part / Maintenance Transaction</h5>
                        <small class="text-muted">Daily workshop records, PO requisitions & parts expenditure</small>
                    </div>
                    <a href="{{ route('equipment-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Logs
                    </a>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('equipment-logs.store') }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Select Equipment from Fleet</label>
                                <select name="equipment_id" class="form-select" id="equipmentSelect">
                                    <option value="">-- Or enter custom plate below --</option>
                                    @foreach($equipments as $eq)
                                        <option value="{{ $eq->id }}" 
                                            data-type="{{ $eq->equipment_type }}"
                                            data-plate="{{ $eq->plate_serial_no }}"
                                            data-location="{{ $eq->location_name }}"
                                            {{ old('equipment_id', request('equipment_id')) == $eq->id ? 'selected' : '' }}>
                                            {{ $eq->plate_serial_no }} - {{ $eq->equipment_type }} ({{ $eq->make }} {{ $eq->model }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Equipment Type <span class="text-danger">*</span></label>
                                <input type="text" name="equipment_type" id="equipmentTypeInput" class="form-control" placeholder="e.g. SINO TRUCK" value="{{ old('equipment_type') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Plate / Serial No</label>
                                <input type="text" name="equipment_plate" id="equipmentPlateInput" class="form-control" placeholder="e.g. 3-50327" value="{{ old('equipment_plate') }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Spare Part Name / Work Description <span class="text-danger">*</span></label>
                                <input type="text" name="spare_part_name" class="form-control" placeholder="e.g. Return spring work adv, Differential housing welding, Hydraulic oil" value="{{ old('spare_part_name') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">PO / Requisition No</label>
                                <input type="text" name="po_no" class="form-control" placeholder="e.g. EQ-MPR000035, POEQ/16/00099" value="{{ old('po_no') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Unit of Measure <span class="text-danger">*</span></label>
                                <select name="unit" class="form-select" required>
                                    <option value="PCS" {{ old('unit', 'PCS') == 'PCS' ? 'selected' : '' }}>PCS (Pieces)</option>
                                    <option value="LIT" {{ old('unit') == 'LIT' ? 'selected' : '' }}>LIT (Liters)</option>
                                    <option value="SET" {{ old('unit') == 'SET' ? 'selected' : '' }}>SET (Sets)</option>
                                    <option value="HR" {{ old('unit') == 'HR' ? 'selected' : '' }}>HR (Hours / Labor)</option>
                                    <option value="KG" {{ old('unit') == 'KG' ? 'selected' : '' }}>KG (Kilograms)</option>
                                    <option value="CAN" {{ old('unit') == 'CAN' ? 'selected' : '' }}>CAN</option>
                                    <option value="PAIR" {{ old('unit') == 'PAIR' ? 'selected' : '' }}>PAIR</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Quantity <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="quantity" id="quantityInput" class="form-control" value="{{ old('quantity', 1) }}" required min="0.01">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Unit Cost (ETB) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="unit_cost" id="unitCostInput" class="form-control" placeholder="0.00" value="{{ old('unit_cost', 0) }}" required min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Total Cost with VAT (ETB)</label>
                                <input type="number" step="0.01" name="total_cost_with_vat" id="totalCostInput" class="form-control fw-bold" placeholder="Auto-calculated (15% VAT)" value="{{ old('total_cost_with_vat') }}">
                                <div class="form-text small">Auto-calculated with 15% VAT if left blank</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Transaction Date <span class="text-danger">*</span></label>
                                <input type="date" name="log_date" class="form-control" value="{{ old('log_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Supplier / Workshop Name</label>
                                <input type="text" name="supplier" class="form-control" placeholder="e.g. Kasma Engineering, Auto Zone, Abiye Eshetu" value="{{ old('supplier') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Project Site</label>
                                <select name="project_id" class="form-select">
                                    <option value="">Central Workshop / All Sites</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Remarks & Notes</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Urgent emergency repair for dump truck axle...">{{ old('remarks') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('equipment-logs.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-save me-1"></i> Save Transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('equipmentSelect').addEventListener('change', function() {
    let opt = this.options[this.selectedIndex];
    if (opt.value) {
        document.getElementById('equipmentTypeInput').value = opt.getAttribute('data-type') || '';
        document.getElementById('equipmentPlateInput').value = opt.getAttribute('data-plate') || '';
    }
});

function calcTotal() {
    let q = parseFloat(document.getElementById('quantityInput').value) || 0;
    let u = parseFloat(document.getElementById('unitCostInput').value) || 0;
    let totInput = document.getElementById('totalCostInput');
    if (q > 0 && u > 0 && !totInput.value) {
        totInput.value = (q * u * 1.15).toFixed(2);
    }
}
document.getElementById('quantityInput').addEventListener('input', calcTotal);
document.getElementById('unitCostInput').addEventListener('input', calcTotal);
</script>
@endsection
