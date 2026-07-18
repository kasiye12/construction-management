@extends('layouts.app')

@section('title', 'Record Material Delivery - CMS')

@section('content')
<div class="page-header">
    <h2>📦 Record Material Delivery</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('material-deliveries.index') }}">Material Deliveries</a></li>
            <li class="breadcrumb-item active">New Delivery</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Delivery Details</h5></div>
            <div class="card-body">
                <form action="{{ route('material-deliveries.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Project <span class="text-danger">*</span></label>
                            <select name="project_id" class="form-select" required onchange="window.location.href='?project_id='+this.value">
                                <option value="">Select Project</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ ($projectId==$p->id)?'selected':'' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subcontractor</label>
                            <select name="subcontractor_id" class="form-select">
                                <option value="">Select Subcontractor</option>
                                @foreach($subcontractors as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Material Description <span class="text-danger">*</span></label>
                        <input type="text" name="item_description" class="form-control" placeholder="e.g., Water Proof Membrane 4mm, Geo Membrane" required>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select" required>
                                <option value="roll">Roll</option><option value="m2">m²</option><option value="m3">m³</option>
                                <option value="kg">kg</option><option value="pcs">pcs</option><option value="liter">Liter</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Unit Multiplier <small class="text-muted">(e.g., 1 roll = 10 m²)</small></label>
                            <input type="number" name="unit_multiplier" class="form-control" value="1" step="0.01" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Converted Qty <small class="text-muted">(Auto)</small></label>
                            <input type="text" class="form-control bg-light" readonly value="Auto-calculated on save">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Delivery Date <span class="text-danger">*</span></label>
                            <input type="date" name="delivery_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gate Pass / Reference No.</label>
                            <input type="text" name="gate_pass_number" class="form-control" placeholder="Auto-generated if blank">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Source Location</label>
                            <input type="text" name="source_location" class="form-control" placeholder="e.g., Head Office">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Record Delivery</button>
                    <a href="{{ route('material-deliveries.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">💡 Tips</h5></div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2">📌 Select project to filter subcontractors</li>
                    <li class="mb-2">🔄 Unit multiplier converts units (e.g., rolls→m²)</li>
                    <li class="mb-2">🔢 Gate pass auto-generates if left blank</li>
                    <li class="mb-2">📝 All changes are logged in audit trail</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
