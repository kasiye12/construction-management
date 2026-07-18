@extends('layouts.app')

@section('title', 'Edit Delivery - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>✏️ Edit Delivery: {{ $materialDelivery->gate_pass_number ?? 'N/A' }}</h2>
        <form action="{{ route('material-deliveries.destroy', $materialDelivery) }}" method="POST" onsubmit="return confirm('Delete this delivery?')" class="d-inline">
            @csrf @method('DELETE')
            <button class="btn btn-danger"><i class="fas fa-trash me-1"></i> Delete</button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Edit Delivery Details</h5></div>
            <div class="card-body">
                <form action="{{ route('material-deliveries.update', $materialDelivery) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Project <span class="text-danger">*</span></label>
                            <select name="project_id" class="form-select" required>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ $materialDelivery->project_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subcontractor</label>
                            <select name="subcontractor_id" class="form-select">
                                <option value="">Select</option>
                                @foreach($subcontractors as $sub)
                                    <option value="{{ $sub->id }}" {{ $materialDelivery->subcontractor_id == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Material Description <span class="text-danger">*</span></label>
                        <input type="text" name="item_description" class="form-control" value="{{ $materialDelivery->item_description }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3"><label class="form-label">Unit</label><select name="unit" class="form-select"><option value="roll" {{ $materialDelivery->unit=='roll'?'selected':'' }}>Roll</option><option value="m2" {{ $materialDelivery->unit=='m2'?'selected':'' }}>m²</option><option value="m3" {{ $materialDelivery->unit=='m3'?'selected':'' }}>m³</option><option value="kg" {{ $materialDelivery->unit=='kg'?'selected':'' }}>kg</option><option value="pcs" {{ $materialDelivery->unit=='pcs'?'selected':'' }}>pcs</option></select></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Quantity</label><input type="number" name="quantity" class="form-control" value="{{ $materialDelivery->quantity }}" step="0.01" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Multiplier</label><input type="number" name="unit_multiplier" class="form-control" value="{{ $materialDelivery->unit_multiplier }}" step="0.01"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Conv. Qty</label><input type="text" class="form-control bg-light" value="{{ number_format($materialDelivery->converted_quantity, 2) }}" readonly></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Delivery Date</label><input type="date" name="delivery_date" class="form-control" value="{{ $materialDelivery->delivery_date->format('Y-m-d') }}" required></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Gate Pass</label><input type="text" name="gate_pass_number" class="form-control" value="{{ $materialDelivery->gate_pass_number }}"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Source</label><input type="text" name="source_location" class="form-control" value="{{ $materialDelivery->source_location }}"></div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
                    <a href="{{ route('material-deliveries.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
