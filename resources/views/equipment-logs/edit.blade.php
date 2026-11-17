@extends('layouts.app')

@section('title', 'Edit Maintenance Log Entry')

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-edit text-primary me-2"></i> Edit Spare Part / Maintenance Transaction</h5>
                        <small class="text-muted">PO: <strong>{{ $equipmentLog->po_no ?: 'N/A' }}</strong> • Equipment: <strong>{{ $equipmentLog->equipment_plate }}</strong></small>
                    </div>
                    <a href="{{ route('equipment-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Logs
                    </a>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('equipment-logs.update', $equipmentLog) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Select Equipment from Fleet</label>
                                <select name="equipment_id" class="form-select" id="equipmentSelect">
                                    <option value="">-- Custom / Unlinked --</option>
                                    @foreach($equipments as $eq)
                                        <option value="{{ $eq->id }}" 
                                            data-type="{{ $eq->equipment_type }}"
                                            data-plate="{{ $eq->plate_serial_no }}"
                                            {{ old('equipment_id', $equipmentLog->equipment_id) == $eq->id ? 'selected' : '' }}>
                                            {{ $eq->plate_serial_no }} - {{ $eq->equipment_type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Equipment Type <span class="text-danger">*</span></label>
                                <input type="text" name="equipment_type" class="form-control" value="{{ old('equipment_type', $equipmentLog->equipment_type) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Plate / Serial No</label>
                                <input type="text" name="equipment_plate" class="form-control" value="{{ old('equipment_plate', $equipmentLog->equipment_plate) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Spare Part Name / Work Description <span class="text-danger">*</span></label>
                                <input type="text" name="spare_part_name" class="form-control" value="{{ old('spare_part_name', $equipmentLog->spare_part_name) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">PO / Requisition No</label>
                                <input type="text" name="po_no" class="form-control" value="{{ old('po_no', $equipmentLog->po_no) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Unit of Measure <span class="text-danger">*</span></label>
                                <select name="unit" class="form-select" required>
                                    <option value="PCS" {{ old('unit', $equipmentLog->unit) == 'PCS' ? 'selected' : '' }}>PCS</option>
                                    <option value="LIT" {{ old('unit', $equipmentLog->unit) == 'LIT' ? 'selected' : '' }}>LIT</option>
                                    <option value="SET" {{ old('unit', $equipmentLog->unit) == 'SET' ? 'selected' : '' }}>SET</option>
                                    <option value="HR" {{ old('unit', $equipmentLog->unit) == 'HR' ? 'selected' : '' }}>HR</option>
                                    <option value="KG" {{ old('unit', $equipmentLog->unit) == 'KG' ? 'selected' : '' }}>KG</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Quantity <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="quantity" class="form-control" value="{{ old('quantity', $equipmentLog->quantity) }}" required min="0.01">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Unit Cost (ETB) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="unit_cost" class="form-control" value="{{ old('unit_cost', $equipmentLog->unit_cost) }}" required min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Total Cost with VAT (ETB)</label>
                                <input type="number" step="0.01" name="total_cost_with_vat" class="form-control fw-bold" value="{{ old('total_cost_with_vat', $equipmentLog->total_cost_with_vat) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Transaction Date <span class="text-danger">*</span></label>
                                <input type="date" name="log_date" class="form-control" value="{{ old('log_date', $equipmentLog->log_date?->format('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Supplier / Workshop Name</label>
                                <input type="text" name="supplier" class="form-control" value="{{ old('supplier', $equipmentLog->supplier) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Project Site</label>
                                <select name="project_id" class="form-select">
                                    <option value="">Central Workshop / All Sites</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}" {{ old('project_id', $equipmentLog->project_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Remarks & Notes</label>
                                <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $equipmentLog->remarks) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('equipment-logs.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Update Transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
