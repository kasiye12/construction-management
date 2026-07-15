@extends('layouts.app')

@section('title', 'Create BOQ Item - CMS')

@section('content')
<div class="page-header">
    <h2>➕ Create BOQ Item</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('boq-items.index') }}">BOQ Items</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Project Selector -->
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">📁 Select Project First</h5></div>
            <div class="card-body">
                <form method="GET" action="{{ route('boq-items.create') }}" id="projectForm">
                    <div class="row align-items-end">
                        <div class="col-md-8">
                            <select name="project_id" class="form-select" onchange="document.getElementById('projectForm').submit();">
                                <option value="">-- Select Project --</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ ($projectId == $project->id) ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-outline-primary w-100">Load</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($projectId)
        <form action="{{ route('boq-items.store') }}" method="POST">
            @csrf
            <input type="hidden" name="project_id" value="{{ $projectId }}">

            <!-- Basic Info -->
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">📋 Basic Information</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cost Category</label>
                            <select name="cost_category_id" class="form-select">
                                <option value="">-- Select --</option>
                                @foreach($costCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->code }} - {{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Item Number <span class="text-danger">*</span></label>
                            <input type="text" name="item_number" class="form-control" placeholder="e.g., 1.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select" required>
                                <option value="m2">m² (Square Meter)</option>
                                <option value="m3">m³ (Cubic Meter)</option>
                                <option value="kg">kg (Kilogram)</option>
                                <option value="ton">ton (Ton)</option>
                                <option value="pcs">pcs (Pieces)</option>
                                <option value="LS">LS (Lump Sum)</option>
                                <option value="m">m (Meter)</option>
                                <option value="liter">liter (Liter)</option>
                                <option value="roll">roll</option>
                                <option value="cylinder">cylinder</option>
                                <option value="bag">bag</option>
                                <option value="box">box</option>
                                <option value="sheet">sheet</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" step="0.0001" min="0" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Unit Rate (ETB) <span class="text-danger">*</span></label>
                            <input type="number" name="unit_rate" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Duration (Days)</label>
                            <input type="number" name="duration_days" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="planned_start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="planned_end_date" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 👷 LABOR RESOURCES -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">👷 Labor Resources</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLaborRow()">
                        <i class="fas fa-plus"></i> Add Labor
                    </button>
                </div>
                <div class="card-body" id="laborContainer">
                    <div class="resource-row border rounded p-3 mb-2">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label small">Trade Name</label>
                                <input type="text" name="labor[0][trade_name]" class="form-control form-control-sm" placeholder="e.g., Mason, Operator">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Workers</label>
                                <input type="number" name="labor[0][number_of_workers]" class="form-control form-control-sm" value="1" min="1">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Total Hours</label>
                                <input type="number" name="labor[0][total_hours]" class="form-control form-control-sm" value="8" min="0">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Wage/Day</label>
                                <input type="number" name="labor[0][wage_per_day]" class="form-control form-control-sm" value="500" min="0">
                            </div>
                            <div class="col-md-2 mb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.resource-row').remove()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🧱 MATERIAL RESOURCES -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">🧱 Material Resources</h5>
                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="addMaterialRow()">
                        <i class="fas fa-plus"></i> Add Material
                    </button>
                </div>
                <div class="card-body" id="materialContainer">
                    <div class="resource-row border rounded p-3 mb-2">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label small">Material Description</label>
                                <input type="text" name="materials[0][description]" class="form-control form-control-sm" placeholder="e.g., Cement, Steel">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Unit</label>
                                <select name="materials[0][unit]" class="form-select form-select-sm">
                                    <option value="">Select Unit</option>
                                    <option value="kg">kg</option>
                                    <option value="ton">ton</option>
                                    <option value="m3">m³</option>
                                    <option value="m2">m²</option>
                                    <option value="m">m</option>
                                    <option value="liter">liter</option>
                                    <option value="pcs">pcs</option>
                                    <option value="bag">bag</option>
                                    <option value="roll">roll</option>
                                    <option value="cylinder">cylinder</option>
                                    <option value="box">box</option>
                                    <option value="sheet">sheet</option>
                                    <option value="pack">pack</option>
                                    <option value="set">set</option>
                                    <option value="pair">pair</option>
                                    <option value="lot">lot</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Quantity</label>
                                <input type="number" name="materials[0][quantity]" class="form-control form-control-sm" value="1" min="0" step="0.01">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Unit Rate (ETB)</label>
                                <input type="number" name="materials[0][unit_rate]" class="form-control form-control-sm" value="0" min="0" step="0.01">
                            </div>
                            <div class="col-md-2 mb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.resource-row').remove()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🚜 EQUIPMENT RESOURCES -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">🚜 Equipment Resources</h5>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="addEquipmentRow()">
                        <i class="fas fa-plus"></i> Add Equipment
                    </button>
                </div>
                <div class="card-body" id="equipmentContainer">
                    <div class="resource-row border rounded p-3 mb-2">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label class="form-label small">Equipment Description</label>
                                <input type="text" name="equipment[0][description]" class="form-control form-control-sm" placeholder="e.g., Excavator">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Duration (Days)</label>
                                <input type="number" name="equipment[0][duration_days]" class="form-control form-control-sm" value="1" min="0">
                            </div>
                            <div class="col-md-1 mb-2">
                                <label class="form-label small">Units</label>
                                <input type="number" name="equipment[0][number_of_units]" class="form-control form-control-sm" value="1" min="1">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Total Hours</label>
                                <input type="number" name="equipment[0][total_hours]" class="form-control form-control-sm" value="8" min="0">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Rate/Hour (ETB)</label>
                                <input type="number" name="equipment[0][rate_per_hour]" class="form-control form-control-sm" value="0" min="0">
                            </div>
                            <div class="col-md-2 mb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.resource-row').remove()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-2"></i>Create BOQ Item with Resources
            </button>
            <a href="{{ route('boq-items.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
        </form>
        @else
            <div class="card"><div class="card-body text-center py-5">
                <i class="fas fa-arrow-up fa-3x text-muted mb-3"></i>
                <h5>Select a Project First</h5>
                <p>Please select a project to create a BOQ item.</p>
            </div></div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card"><div class="card-header"><h5 class="mb-0">💡 Quick Guide</h5></div><div class="card-body">
            <p><strong>Labor:</strong> Add workers with trade names, hours, and daily wage.</p>
            <p><strong>Material:</strong> Add materials with quantities and unit rates.</p>
            <p><strong>Equipment:</strong> Add machinery with hours and hourly rates.</p>
            <p class="mb-0 text-muted">All amounts are auto-calculated on save.</p>
        </div></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let laborCount = 1, materialCount = 1, equipmentCount = 1;

function addLaborRow() {
    const html = `<div class="resource-row border rounded p-3 mb-2">
        <div class="row">
            <div class="col-md-4 mb-2"><input type="text" name="labor[${laborCount}][trade_name]" class="form-control form-control-sm" placeholder="Trade name"></div>
            <div class="col-md-2 mb-2"><input type="number" name="labor[${laborCount}][number_of_workers]" class="form-control form-control-sm" value="1" min="1"></div>
            <div class="col-md-2 mb-2"><input type="number" name="labor[${laborCount}][total_hours]" class="form-control form-control-sm" value="8" min="0"></div>
            <div class="col-md-2 mb-2"><input type="number" name="labor[${laborCount}][wage_per_day]" class="form-control form-control-sm" value="500" min="0"></div>
            <div class="col-md-2 mb-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.resource-row').remove()"><i class="fas fa-times"></i></button></div>
        </div></div>`;
    document.getElementById('laborContainer').insertAdjacentHTML('beforeend', html);
    laborCount++;
}

function addMaterialRow() {
    const html = `<div class="resource-row border rounded p-3 mb-2">
        <div class="row">
            <div class="col-md-4 mb-2"><input type="text" name="materials[${materialCount}][description]" class="form-control form-control-sm" placeholder="Material name"></div>
            <div class="col-md-2 mb-2">
                <select name="materials[${materialCount}][unit]" class="form-select form-select-sm">
                    <option value="">Select Unit</option>
                    <option value="kg">kg</option><option value="ton">ton</option><option value="m3">m³</option>
                    <option value="m2">m²</option><option value="m">m</option><option value="liter">liter</option>
                    <option value="pcs">pcs</option><option value="bag">bag</option><option value="roll">roll</option>
                    <option value="cylinder">cylinder</option><option value="box">box</option><option value="sheet">sheet</option>
                    <option value="pack">pack</option><option value="set">set</option><option value="pair">pair</option>
                    <option value="lot">lot</option>
                </select>
            </div>
            <div class="col-md-2 mb-2"><input type="number" name="materials[${materialCount}][quantity]" class="form-control form-control-sm" value="1" min="0" step="0.01"></div>
            <div class="col-md-2 mb-2"><input type="number" name="materials[${materialCount}][unit_rate]" class="form-control form-control-sm" value="0" min="0" step="0.01"></div>
            <div class="col-md-2 mb-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.resource-row').remove()"><i class="fas fa-times"></i></button></div>
        </div></div>`;
    document.getElementById('materialContainer').insertAdjacentHTML('beforeend', html);
    materialCount++;
}

function addEquipmentRow() {
    const html = `<div class="resource-row border rounded p-3 mb-2">
        <div class="row">
            <div class="col-md-3 mb-2"><input type="text" name="equipment[${equipmentCount}][description]" class="form-control form-control-sm" placeholder="Equipment name"></div>
            <div class="col-md-2 mb-2"><input type="number" name="equipment[${equipmentCount}][duration_days]" class="form-control form-control-sm" value="1" min="0"></div>
            <div class="col-md-1 mb-2"><input type="number" name="equipment[${equipmentCount}][number_of_units]" class="form-control form-control-sm" value="1" min="1"></div>
            <div class="col-md-2 mb-2"><input type="number" name="equipment[${equipmentCount}][total_hours]" class="form-control form-control-sm" value="8" min="0"></div>
            <div class="col-md-2 mb-2"><input type="number" name="equipment[${equipmentCount}][rate_per_hour]" class="form-control form-control-sm" value="0" min="0"></div>
            <div class="col-md-2 mb-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.resource-row').remove()"><i class="fas fa-times"></i></button></div>
        </div></div>`;
    document.getElementById('equipmentContainer').insertAdjacentHTML('beforeend', html);
    equipmentCount++;
}
</script>
@endpush
