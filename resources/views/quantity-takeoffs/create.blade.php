@extends('layouts.app')

@section('title', 'New Quantity Take-Off - CMS')

@push('styles')
<style>
    .takeoff-form .card { border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
    .takeoff-form .section-header {
        background: linear-gradient(135deg, #1a237e, #3949ab);
        color: white;
        padding: 12px 16px;
        border-radius: 10px 10px 0 0;
        font-weight: 700;
        font-size: 0.9rem;
    }
    .measurement-card {
        background: white;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 10px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
        position: relative;
    }
    .measurement-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .measurement-card.left-card { border-left: 4px solid #4f46e5; }
    .measurement-card.right-card { border-left: 4px solid #10b981; }
    .measurement-card .card-number {
        position: absolute;
        top: -10px;
        left: 12px;
        background: #4f46e5;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
    }
    .measurement-card.right-card .card-number { background: #10b981; }
    
    .btn-add-row {
        border: 2px dashed #cbd5e1;
        color: #64748b;
        background: transparent;
        padding: 10px;
        border-radius: 8px;
        width: 100%;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-add-row:hover {
        border-color: #4f46e5;
        color: #4f46e5;
        background: #eef2ff;
    }
    .formula-box {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        border: 2px solid #f59e0b;
    }
    .formula-box h3 { color: #92400e; font-weight: 800; }
</style>
@endpush

@section('content')
<div class="takeoff-form">
    <div class="page-header">
        <h2>📐 New Quantity Take-Off Sheet</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('quantity-takeoffs.index') }}">Take-Off Sheets</a></li>
                <li class="breadcrumb-item active">Create New</li>
            </ol>
        </nav>
    </div>

    <form action="{{ route('quantity-takeoffs.store-multiple') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                
                <!-- GENERAL INFORMATION -->
                <div class="card mb-4">
                    <div class="section-header">
                        <i class="fas fa-info-circle me-2"></i> General Information
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Project <span class="text-danger">*</span></label>
                                <select name="project_id" class="form-select" required onchange="window.location.href='?project_id='+this.value">
                                    <option value="">-- Select Project --</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}" {{ ($projectId==$p->id)?'selected':'' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">BOQ Item <span class="text-danger">*</span></label>
                                <select name="boq_item_id" class="form-select" required>
                                    <option value="">-- Select BOQ Item --</option>
                                    @foreach($boqItems as $item)
                                        <option value="{{ $item->id }}">{{ $item->item_number }} - {{ Str::limit($item->description, 45) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Structure Type</label>
                                <input type="text" name="structure_type" class="form-control" 
                                       placeholder="e.g., BITUMINOUS DAMP PROOFING">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Location / Axis</label>
                                <input type="text" name="location_axis" class="form-control" 
                                       placeholder="e.g., Foundation Footing Pad">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Measurement Date <span class="text-danger">*</span></label>
                                <input type="date" name="measurement_date" class="form-control" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Measured By</label>
                                <input type="text" name="measured_by" class="form-control" 
                                       value="{{ auth()->user()->name }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MEASUREMENTS - TWO COLUMNS -->
                <div class="row">
                    <!-- LEFT SIDE -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="section-header" style="background: linear-gradient(135deg, #4f46e5, #6366f1);">
                                <i class="fas fa-ruler-combined me-2"></i> Left Side Measurements
                            </div>
                            <div class="card-body" id="leftContainer">
                                <p class="text-muted small mb-3">💡 Add foundation elements for the LEFT column (F1, F3, F5, F7...)</p>
                                
                                <!-- Default Row 1 -->
                                <div class="measurement-card left-card">
                                    <div class="card-number">1</div>
                                    <div class="row mt-2">
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label small fw-bold">Element ID <span class="text-danger">*</span></label>
                                            <input type="text" name="left[0][element_id]" class="form-control form-control-sm" placeholder="e.g., F1" required>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label small fw-bold">Qty <span class="text-danger">*</span></label>
                                            <input type="number" name="left[0][quantity_count]" class="form-control form-control-sm" value="1" min="1" required>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label small fw-bold">Length (L) <span class="text-danger">*</span></label>
                                            <input type="number" name="left[0][length]" class="form-control form-control-sm" step="0.001" min="0" placeholder="4.30" required>
                                        </div>
                                        <div class="col-md-2 mb-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.measurement-card').remove()" title="Remove">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label small">Width (W) m</label>
                                            <input type="number" name="left[0][width]" class="form-control form-control-sm" step="0.001" min="0" value="1">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label small">Height (H) m</label>
                                            <input type="number" name="left[0][height_depth]" class="form-control form-control-sm" step="0.001" min="0" value="1">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label small">Description</label>
                                            <input type="text" name="left[0][remarks]" class="form-control form-control-sm" placeholder="Notes...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <button type="button" class="btn-add-row" onclick="addLeftRow()">
                                    <i class="fas fa-plus-circle me-1"></i> Add Another Element to Left Side
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT SIDE -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="section-header" style="background: linear-gradient(135deg, #10b981, #059669);">
                                <i class="fas fa-ruler-combined me-2"></i> Right Side Measurements
                            </div>
                            <div class="card-body" id="rightContainer">
                                <p class="text-muted small mb-3">💡 Add foundation elements for the RIGHT column (F2, F4, F6, F8...)</p>
                                
                                <!-- Default Row 1 -->
                                <div class="measurement-card right-card">
                                    <div class="card-number">1</div>
                                    <div class="row mt-2">
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label small fw-bold">Element ID <span class="text-danger">*</span></label>
                                            <input type="text" name="right[0][element_id]" class="form-control form-control-sm" placeholder="e.g., F2" required>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label small fw-bold">Qty <span class="text-danger">*</span></label>
                                            <input type="number" name="right[0][quantity_count]" class="form-control form-control-sm" value="1" min="1" required>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="form-label small fw-bold">Length (L) <span class="text-danger">*</span></label>
                                            <input type="number" name="right[0][length]" class="form-control form-control-sm" step="0.001" min="0" placeholder="3.70" required>
                                        </div>
                                        <div class="col-md-2 mb-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.measurement-card').remove()" title="Remove">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label small">Width (W) m</label>
                                            <input type="number" name="right[0][width]" class="form-control form-control-sm" step="0.001" min="0" value="1">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label small">Height (H) m</label>
                                            <input type="number" name="right[0][height_depth]" class="form-control form-control-sm" step="0.001" min="0" value="1">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label small">Description</label>
                                            <input type="text" name="right[0][remarks]" class="form-control form-control-sm" placeholder="Notes...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <button type="button" class="btn-add-row" onclick="addRightRow()">
                                    <i class="fas fa-plus-circle me-1"></i> Add Another Element to Right Side
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUBMIT -->
                <div class="d-flex gap-2 mb-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-save me-2"></i> Save All Measurements
                    </button>
                    <a href="{{ route('quantity-takeoffs.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-4">
                <!-- Formula -->
                <div class="formula-box mb-4">
                    <i class="fas fa-calculator fa-2x mb-2" style="color:#92400e;"></i>
                    <h3 class="mb-1">Total = Qty × L × W × H</h3>
                    <p class="text-muted mb-0 small">Auto-calculated when you save</p>
                </div>

                <!-- Example Data -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white"><h6 class="mb-0"><i class="fas fa-table me-2"></i>Example Measurements</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0 small">
                            <thead class="table-light">
                                <tr><th>Element</th><th>Qty</th><th>Size</th><th>Area</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>F1</td><td>9</td><td>4.30×4.30</td><td>166.41</td></tr>
                                <tr><td>F2</td><td>3</td><td>3.70×3.70</td><td>41.07</td></tr>
                                <tr><td>F3</td><td>13</td><td>3.70×3.70</td><td>177.97</td></tr>
                                <tr><td>F4</td><td>48</td><td>3.40×3.40</td><td>554.88</td></tr>
                                <tr><td>F5</td><td>40</td><td>2.80×2.80</td><td>313.60</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Workflow Info -->
                <div class="card">
                    <div class="card-header bg-info text-white"><h6 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Workflow</h6></div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-secondary me-2">1</span>
                            <span>📝 <strong>Draft</strong> - Initial measurement</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-info me-2">2</span>
                            <span>✅ <strong>Verify</strong> - Checked by supervisor</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success me-2">3</span>
                            <span>✔️ <strong>Approve</strong> - Ready for payment</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let leftCount = 1;
let rightCount = 1;

function addLeftRow() {
    const html = `
    <div class="measurement-card left-card">
        <div class="card-number">${leftCount + 1}</div>
        <div class="row mt-2">
            <div class="col-md-4 mb-2">
                <label class="form-label small fw-bold">Element ID <span class="text-danger">*</span></label>
                <input type="text" name="left[${leftCount}][element_id]" class="form-control form-control-sm" placeholder="e.g., F${leftCount+1}" required>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label small fw-bold">Qty <span class="text-danger">*</span></label>
                <input type="number" name="left[${leftCount}][quantity_count]" class="form-control form-control-sm" value="1" min="1" required>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label small fw-bold">Length (L) <span class="text-danger">*</span></label>
                <input type="number" name="left[${leftCount}][length]" class="form-control form-control-sm" step="0.001" min="0" required>
            </div>
            <div class="col-md-2 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.measurement-card').remove()"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-2">
                <label class="form-label small">Width (W) m</label>
                <input type="number" name="left[${leftCount}][width]" class="form-control form-control-sm" step="0.001" min="0" value="1">
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label small">Height (H) m</label>
                <input type="number" name="left[${leftCount}][height_depth]" class="form-control form-control-sm" step="0.001" min="0" value="1">
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label small">Description</label>
                <input type="text" name="left[${leftCount}][remarks]" class="form-control form-control-sm" placeholder="Notes...">
            </div>
        </div>
    </div>`;
    document.getElementById('leftContainer').insertAdjacentHTML('beforeend', html);
    leftCount++;
}

function addRightRow() {
    const html = `
    <div class="measurement-card right-card">
        <div class="card-number">${rightCount + 1}</div>
        <div class="row mt-2">
            <div class="col-md-4 mb-2">
                <label class="form-label small fw-bold">Element ID <span class="text-danger">*</span></label>
                <input type="text" name="right[${rightCount}][element_id]" class="form-control form-control-sm" placeholder="e.g., F${rightCount+1}" required>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label small fw-bold">Qty <span class="text-danger">*</span></label>
                <input type="number" name="right[${rightCount}][quantity_count]" class="form-control form-control-sm" value="1" min="1" required>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label small fw-bold">Length (L) <span class="text-danger">*</span></label>
                <input type="number" name="right[${rightCount}][length]" class="form-control form-control-sm" step="0.001" min="0" required>
            </div>
            <div class="col-md-2 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.measurement-card').remove()"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-2">
                <label class="form-label small">Width (W) m</label>
                <input type="number" name="right[${rightCount}][width]" class="form-control form-control-sm" step="0.001" min="0" value="1">
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label small">Height (H) m</label>
                <input type="number" name="right[${rightCount}][height_depth]" class="form-control form-control-sm" step="0.001" min="0" value="1">
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label small">Description</label>
                <input type="text" name="right[${rightCount}][remarks]" class="form-control form-control-sm" placeholder="Notes...">
            </div>
        </div>
    </div>`;
    document.getElementById('rightContainer').insertAdjacentHTML('beforeend', html);
    rightCount++;
}
</script>
@endpush
