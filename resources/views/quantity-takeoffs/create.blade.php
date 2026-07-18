@extends('layouts.app')

@section('title', 'New Measurement - CMS')

@section('content')
<div class="page-header">
    <h2>📐 New Quantity Take-Off (Measurement)</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('quantity-takeoffs.index') }}">Quantity Take-Off</a></li>
            <li class="breadcrumb-item active">New Measurement</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Measurement Details</h5></div>
            <div class="card-body">
                <form action="{{ route('quantity-takeoffs.store') }}" method="POST">
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
                            <label class="form-label">BOQ Item <span class="text-danger">*</span></label>
                            <select name="boq_item_id" class="form-select" required>
                                <option value="">Select BOQ Item</option>
                                @foreach($boqItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->item_number }} - {{ Str::limit($item->description, 40) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Structure Type</label>
                            <input type="text" name="structure_type" class="form-control" placeholder="e.g., Foundation Footing, BITUMINOUS DAMP PROOFING">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Element ID</label>
                            <input type="text" name="element_id" class="form-control" placeholder="e.g., F1, F2, F3, Pad">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location / Axis Details</label>
                        <input type="text" name="location_axis" class="form-control" placeholder="e.g., B/n axis(( B-I //10-3) & 2B,2C&2I))">
                    </div>
                    <h6 class="mt-3 mb-2">📏 Dimensions</h6>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Quantity Count <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_count" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Length (L) <small>m</small></label>
                            <input type="number" name="length" class="form-control" step="0.001" min="0" placeholder="0.95+3.4+0.95">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Width (W) <small>m</small></label>
                            <input type="number" name="width" class="form-control" step="0.001" min="0" value="1">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Height/Depth (H/D) <small>m</small></label>
                            <input type="number" name="height_depth" class="form-control" step="0.001" min="0" value="1">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Measurement Date <span class="text-danger">*</span></label>
                            <input type="date" name="measurement_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Measured By</label>
                            <input type="text" name="measured_by" class="form-control" value="{{ auth()->user()->name }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Total <small class="text-muted">(Auto-calculated)</small></label>
                            <div class="form-control bg-light">Qty × L × W × H = Auto</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Measurement</button>
                    <a href="{{ route('quantity-takeoffs.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">🧮 Calculation Formula</h5></div>
            <div class="card-body">
                <div class="text-center p-3 bg-light rounded">
                    <h4>Total = Qty × L × W × H</h4>
                    <p class="text-muted mb-0">Auto-calculated on save</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">📋 Workflow</h5></div>
            <div class="card-body">
                <ol class="small mb-0">
                    <li class="mb-2">📝 <strong>Draft</strong> - Initial measurement</li>
                    <li class="mb-2">✅ <strong>Verified</strong> - Checked by supervisor</li>
                    <li class="mb-2">✔️ <strong>Approved</strong> - Ready for payment</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
