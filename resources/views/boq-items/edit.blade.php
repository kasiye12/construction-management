@extends('layouts.app')

@section('title', 'Edit BOQ Item - ' . $boqItem->item_number)

@section('content')
<div class="page-header">
    <h2>✏️ Edit BOQ Item: {{ $boqItem->item_number }}</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('boq-items.index') }}">BOQ Items</a></li>
            <li class="breadcrumb-item"><a href="{{ route('boq-items.show', $boqItem) }}">{{ $boqItem->item_number }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('boq-items.update', $boqItem) }}" method="POST">
    @csrf @method('PUT')
    
    <div class="row">
        <div class="col-md-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">📋 Basic Information</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Project</label>
                            <select name="project_id" class="form-select" required>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ $boqItem->project_id == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cost Category</label>
                            <select name="cost_category_id" class="form-select">
                                <option value="">-- Select --</option>
                                @foreach($costCategories as $cat)
                                    <option value="{{ $cat->id }}" {{ $boqItem->cost_category_id == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->code }} - {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="pending" {{ $boqItem->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $boqItem->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $boqItem->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Item Number <span class="text-danger">*</span></label>
                            <input type="text" name="item_number" class="form-control" value="{{ $boqItem->item_number }}" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control" value="{{ $boqItem->description }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select" required>
                                <option value="m2" {{ $boqItem->unit=='m2'?'selected':'' }}>m²</option>
                                <option value="m3" {{ $boqItem->unit=='m3'?'selected':'' }}>m³</option>
                                <option value="kg" {{ $boqItem->unit=='kg'?'selected':'' }}>kg</option>
                                <option value="ton" {{ $boqItem->unit=='ton'?'selected':'' }}>ton</option>
                                <option value="pcs" {{ $boqItem->unit=='pcs'?'selected':'' }}>pcs</option>
                                <option value="LS" {{ $boqItem->unit=='LS'?'selected':'' }}>LS</option>
                                <option value="m" {{ $boqItem->unit=='m'?'selected':'' }}>m</option>
                                <option value="liter" {{ $boqItem->unit=='liter'?'selected':'' }}>liter</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" value="{{ $boqItem->quantity }}" step="0.0001" min="0" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Unit Rate (ETB) <span class="text-danger">*</span></label>
                            <input type="number" name="unit_rate" class="form-control" value="{{ $boqItem->unit_rate }}" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Duration (Days)</label>
                            <input type="number" name="duration_days" class="form-control" value="{{ $boqItem->duration_days }}" min="0">
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_parent" class="form-check-input" value="1" {{ $boqItem->is_parent?'checked':'' }}>
                                <label class="form-check-label">Parent Item</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 👷 LABOR RESOURCES -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">👷 Labor Resources ({{ number_format($boqItem->laborResources->sum('amount'), 2) }} ETB)</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLaborRow()">
                        <i class="fas fa-plus"></i> Add Labor
                    </button>
                </div>
                <div class="card-body" id="laborContainer">
                    @php $lIndex = 0; @endphp
                    @forelse($boqItem->laborResources as $labor)
                    <div class="border rounded p-3 mb-2">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label small">Trade Name</label>
                                <input type="text" name="labor[{{ $lIndex }}][trade_name]" class="form-control form-control-sm" value="{{ $labor->trade_name }}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Workers</label>
                                <input type="number" name="labor[{{ $lIndex }}][number_of_workers]" class="form-control form-control-sm" value="{{ $labor->number_of_workers }}" min="1">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Total Hours</label>
                                <input type="number" name="labor[{{ $lIndex }}][total_hours]" class="form-control form-control-sm" value="{{ $labor->total_hours }}" min="0">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Wage/Day</label>
                                <input type="number" name="labor[{{ $lIndex }}][wage_per_day]" class="form-control form-control-sm" value="{{ $labor->wage_per_day }}" min="0">
                            </div>
                            <div class="col-md-2 mb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.border').remove()"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    @php $lIndex++; @endphp
                    @empty
                    <p class="text-muted" id="noLabor">No labor resources. Click "Add Labor" to add.</p>
                    @endforelse
                </div>
            </div>

            <!-- 🧱 MATERIAL RESOURCES -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">🧱 Material Resources ({{ number_format($boqItem->materialResources->sum('amount'), 2) }} ETB)</h5>
                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="addMaterialRow()">
                        <i class="fas fa-plus"></i> Add Material
                    </button>
                </div>
                <div class="card-body" id="materialContainer">
                    @php $mIndex = 0; @endphp
                    @forelse($boqItem->materialResources as $material)
                    <div class="border rounded p-3 mb-2">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label small">Material Description</label>
                                <input type="text" name="materials[{{ $mIndex }}][description]" class="form-control form-control-sm" value="{{ $material->description }}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Unit</label>
                                <select name="materials[{{ $mIndex }}][unit]" class="form-select form-select-sm">
                                    @php $units = ['kg','ton','m3','m2','m','liter','pcs','bag','roll','cylinder','box','sheet','pack','set','pair','lot']; @endphp
                                    @foreach($units as $u)
                                        <option value="{{ $u }}" {{ $material->unit == $u ? 'selected' : '' }}>{{ $u }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Quantity</label>
                                <input type="number" name="materials[{{ $mIndex }}][quantity]" class="form-control form-control-sm" value="{{ $material->quantity }}" min="0" step="0.01">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Unit Rate (ETB)</label>
                                <input type="number" name="materials[{{ $mIndex }}][unit_rate]" class="form-control form-control-sm" value="{{ $material->unit_rate }}" min="0" step="0.01">
                            </div>
                            <div class="col-md-2 mb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.border').remove()"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    @php $mIndex++; @endphp
                    @empty
                    <p class="text-muted" id="noMaterial">No material resources. Click "Add Material" to add.</p>
                    @endforelse
                </div>
            </div>

            <!-- 🚜 EQUIPMENT RESOURCES -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">🚜 Equipment Resources ({{ number_format($boqItem->equipmentResources->sum('amount'), 2) }} ETB)</h5>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="addEquipmentRow()">
                        <i class="fas fa-plus"></i> Add Equipment
                    </button>
                </div>
                <div class="card-body" id="equipmentContainer">
                    @php $eIndex = 0; @endphp
                    @forelse($boqItem->equipmentResources as $equipment)
                    <div class="border rounded p-3 mb-2">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label class="form-label small">Equipment Description</label>
                                <input type="text" name="equipment[{{ $eIndex }}][description]" class="form-control form-control-sm" value="{{ $equipment->description }}">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Duration (Days)</label>
                                <input type="number" name="equipment[{{ $eIndex }}][duration_days]" class="form-control form-control-sm" value="{{ $equipment->duration_days }}" min="0">
                            </div>
                            <div class="col-md-1 mb-2">
                                <label class="form-label small">Units</label>
                                <input type="number" name="equipment[{{ $eIndex }}][number_of_units]" class="form-control form-control-sm" value="{{ $equipment->number_of_units }}" min="1">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Total Hours</label>
                                <input type="number" name="equipment[{{ $eIndex }}][total_hours]" class="form-control form-control-sm" value="{{ $equipment->total_hours }}" min="0">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Rate/Hour (ETB)</label>
                                <input type="number" name="equipment[{{ $eIndex }}][rate_per_hour]" class="form-control form-control-sm" value="{{ $equipment->rate_per_hour }}" min="0">
                            </div>
                            <div class="col-md-2 mb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.border').remove()"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    @php $eIndex++; @endphp
                    @empty
                    <p class="text-muted" id="noEquipment">No equipment resources. Click "Add Equipment" to add.</p>
                    @endforelse
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-2"></i>Update BOQ Item
            </button>
            <a href="{{ route('boq-items.show', $boqItem) }}" class="btn btn-secondary btn-lg">Cancel</a>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">💰 Financial Summary</h5></div>
                <div class="card-body">
                    <p><strong>Revenue:</strong> {{ number_format($boqItem->revenue_amount, 2) }} ETB</p>
                    <p><strong>Budget Cost:</strong> {{ number_format($boqItem->total_budget_cost, 2) }} ETB</p>
                    <p><strong>P/L:</strong> 
                        <span class="{{ $boqItem->profit_loss >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($boqItem->profit_loss, 2) }} ETB
                        </span>
                    </p>
                    <hr>
                    <p><strong>Labor:</strong> {{ number_format($boqItem->laborResources->sum('amount'), 2) }} ETB</p>
                    <p><strong>Material:</strong> {{ number_format($boqItem->materialResources->sum('amount'), 2) }} ETB</p>
                    <p><strong>Equipment:</strong> {{ number_format($boqItem->equipmentResources->sum('amount'), 2) }} ETB</p>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let laborCount = {{ $boqItem->laborResources->count() }};
let materialCount = {{ $boqItem->materialResources->count() }};
let equipmentCount = {{ $boqItem->equipmentResources->count() }};

document.getElementById('noLabor')?.remove();
document.getElementById('noMaterial')?.remove();
document.getElementById('noEquipment')?.remove();

function addLaborRow() {
    const html = `<div class="border rounded p-3 mb-2"><div class="row">
        <div class="col-md-4 mb-2"><input type="text" name="labor[${laborCount}][trade_name]" class="form-control form-control-sm" placeholder="Trade name"></div>
        <div class="col-md-2 mb-2"><input type="number" name="labor[${laborCount}][number_of_workers]" class="form-control form-control-sm" value="1" min="1"></div>
        <div class="col-md-2 mb-2"><input type="number" name="labor[${laborCount}][total_hours]" class="form-control form-control-sm" value="8" min="0"></div>
        <div class="col-md-2 mb-2"><input type="number" name="labor[${laborCount}][wage_per_day]" class="form-control form-control-sm" value="500" min="0"></div>
        <div class="col-md-2 mb-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.border').remove()"><i class="fas fa-times"></i></button></div>
    </div></div>`;
    document.getElementById('laborContainer').insertAdjacentHTML('beforeend', html);
    laborCount++;
}

function addMaterialRow() {
    const html = `<div class="border rounded p-3 mb-2"><div class="row">
        <div class="col-md-4 mb-2"><input type="text" name="materials[${materialCount}][description]" class="form-control form-control-sm" placeholder="Material name"></div>
        <div class="col-md-2 mb-2">
            <select name="materials[${materialCount}][unit]" class="form-select form-select-sm">
                <option value="">Select</option><option value="kg">kg</option><option value="ton">ton</option>
                <option value="m3">m³</option><option value="m2">m²</option><option value="m">m</option>
                <option value="liter">liter</option><option value="pcs">pcs</option><option value="bag">bag</option>
                <option value="roll">roll</option><option value="cylinder">cylinder</option><option value="box">box</option>
                <option value="sheet">sheet</option><option value="pack">pack</option><option value="set">set</option>
            </select>
        </div>
        <div class="col-md-2 mb-2"><input type="number" name="materials[${materialCount}][quantity]" class="form-control form-control-sm" value="1" min="0" step="0.01"></div>
        <div class="col-md-2 mb-2"><input type="number" name="materials[${materialCount}][unit_rate]" class="form-control form-control-sm" value="0" min="0" step="0.01"></div>
        <div class="col-md-2 mb-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.border').remove()"><i class="fas fa-times"></i></button></div>
    </div></div>`;
    document.getElementById('materialContainer').insertAdjacentHTML('beforeend', html);
    materialCount++;
}

function addEquipmentRow() {
    const html = `<div class="border rounded p-3 mb-2"><div class="row">
        <div class="col-md-3 mb-2"><input type="text" name="equipment[${equipmentCount}][description]" class="form-control form-control-sm" placeholder="Equipment name"></div>
        <div class="col-md-2 mb-2"><input type="number" name="equipment[${equipmentCount}][duration_days]" class="form-control form-control-sm" value="1" min="0"></div>
        <div class="col-md-1 mb-2"><input type="number" name="equipment[${equipmentCount}][number_of_units]" class="form-control form-control-sm" value="1" min="1"></div>
        <div class="col-md-2 mb-2"><input type="number" name="equipment[${equipmentCount}][total_hours]" class="form-control form-control-sm" value="8" min="0"></div>
        <div class="col-md-2 mb-2"><input type="number" name="equipment[${equipmentCount}][rate_per_hour]" class="form-control form-control-sm" value="0" min="0"></div>
        <div class="col-md-2 mb-2 d-flex align-items-end"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.border').remove()"><i class="fas fa-times"></i></button></div>
    </div></div>`;
    document.getElementById('equipmentContainer').insertAdjacentHTML('beforeend', html);
    equipmentCount++;
}
</script>
@endpush
