@extends('layouts.app')

@section('title', 'Create IPC - CMS')

@push('styles')
<style>
    .boq-item-row { background: #f9fafb; border-radius: 8px; padding: 12px; margin-bottom: 8px; border: 1px solid #e5e7eb; }
    .boq-item-row:hover { border-color: #4f46e5; }
    .item-preview { font-size: 0.75rem; color: #6b7280; }
    .item-preview strong { color: #1a237e; }
    .btn-add-item { border: 2px dashed #cbd5e1; color: #64748b; background: transparent; padding: 10px; border-radius: 8px; width: 100%; font-weight: 600; font-size: 0.85rem; cursor: pointer; }
    .btn-add-item:hover { border-color: #4f46e5; color: #4f46e5; background: #eef2ff; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h2>📄 Create IPC (Payment Certificate)</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('ipcs.index') }}">IPCs</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<form action="{{ route('ipcs.store') }}" method="POST" id="ipcForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <!-- IPC Info -->
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">📋 IPC Information</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Project <span class="text-danger">*</span></label>
                            <select name="project_id" id="projectSelect" class="form-select" required onchange="loadProjectData(this.value)">
                                <option value="">-- Select Project --</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ ($projectId == $p->id) ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Subcontractor <span class="text-danger">*</span></label>
                            <select name="subcontractor_id" id="subcontractorSelect" class="form-select" required>
                                <option value="">-- Select Subcontractor --</option>
                                @foreach($subcontractors as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">IPC Number <span class="text-danger">*</span></label>
                            <input type="text" name="ipc_number" class="form-control" placeholder="e.g., IPC-001" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">IPC Date <span class="text-danger">*</span></label>
                            <input type="date" name="ipc_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Period Start <span class="text-danger">*</span></label>
                            <input type="date" name="period_start_date" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Period End <span class="text-danger">*</span></label>
                            <input type="date" name="period_end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Retention %</label>
                            <input type="number" name="retention_percentage" class="form-control" value="5" min="0" max="100" step="0.01">
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOQ Items -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📦 BOQ Items <span class="text-danger">*</span></h5>
                    <a href="{{ route('boq-items.create', ['project_id' => $projectId]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-plus me-1"></i> Create New BOQ Item
                    </a>
                </div>
                <div class="card-body" id="itemsContainer">
                    @if($boqItems->count() > 0)
                    <div class="boq-item-row" data-index="0">
                        <div class="row align-items-end">
                            <div class="col-md-5 mb-2">
                                <label class="form-label small fw-bold">BOQ Item <span class="text-danger">*</span></label>
                                <select name="items[0][boq_item_id]" class="form-select form-select-sm boq-select" onchange="showItemPreview(this, 0)">
                                    <option value="">-- Select BOQ Item --</option>
                                    @foreach($boqItems as $item)
                                        <option value="{{ $item->id }}" 
                                            data-unit="{{ $item->unit }}" 
                                            data-rate="{{ $item->unit_rate }}"
                                            data-qty="{{ $item->quantity }}">
                                            {{ $item->item_number }} - {{ Str::limit($item->description, 50) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label small">Current Qty <span class="text-danger">*</span></label>
                                <input type="number" name="items[0][current_quantity]" class="form-control form-control-sm qty-input" step="0.01" min="0" placeholder="0.00" required oninput="calculateRow(0)">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label small">Preview</label>
                                <div class="item-preview" id="preview-0">
                                    <span class="text-muted">Select an item</span>
                                </div>
                            </div>
                            <div class="col-md-2 mb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.boq-item-row').remove()">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <p class="text-muted">No BOQ items found for this project.</p>
                        <a href="{{ route('boq-items.create', ['project_id' => $projectId]) }}" class="btn btn-primary" target="_blank">
                            <i class="fas fa-plus me-1"></i> Create BOQ Items First
                        </a>
                    </div>
                    @endif
                </div>
                @if($boqItems->count() > 0)
                <div class="card-footer">
                    <button type="button" class="btn-add-item" onclick="addItemRow()">
                        <i class="fas fa-plus-circle me-2"></i> Add Another BOQ Item
                    </button>
                </div>
                @endif
            </div>

            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-save me-2"></i> Create IPC
            </button>
            <a href="{{ route('ipcs.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">💰 Summary</h6></div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><td>Total Amount:</td><td class="text-end fw-bold" id="totalAmount">0.00 ETB</td></tr>
                        <tr><td>Retention (5%):</td><td class="text-end" id="retentionAmount">0.00 ETB</td></tr>
                        <tr class="table-success"><td><strong>Net Payment:</strong></td><td class="text-end fw-bold" id="netAmount">0.00 ETB</td></tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">💡 Tips</h6></div>
                <div class="card-body small">
                    <ul class="mb-0">
                        <li class="mb-1">📌 Select project to load BOQ items</li>
                        <li class="mb-1">🆕 Create new BOQ items if needed</li>
                        <li class="mb-1">📊 Enter current period quantities</li>
                        <li class="mb-1">💰 Amount = Qty × Unit Rate</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let itemIndex = 1;

function loadProjectData(projectId) {
    if (projectId) {
        window.location.href = '{{ route("ipcs.create") }}?project_id=' + projectId;
    }
}

function showItemPreview(select, index) {
    const option = select.options[select.selectedIndex];
    const unit = option.getAttribute('data-unit') || '';
    const rate = parseFloat(option.getAttribute('data-rate')) || 0;
    const totalQty = parseFloat(option.getAttribute('data-qty')) || 0;
    
    const preview = document.getElementById('preview-' + index);
    preview.innerHTML = `<strong>${rate.toLocaleString()}</strong> ETB/${unit} | Total: ${totalQty} ${unit}`;
    
    calculateRow(index);
    updateSummary();
}

function calculateRow(index) {
    const row = document.querySelector(`[data-index="${index}"]`);
    if (!row) return;
    
    const select = row.querySelector('.boq-select');
    const qtyInput = row.querySelector('.qty-input');
    
    if (select && qtyInput) {
        const option = select.options[select.selectedIndex];
        const rate = parseFloat(option?.getAttribute('data-rate')) || 0;
        const qty = parseFloat(qtyInput.value) || 0;
        const amount = qty * rate;
        
        const preview = document.getElementById('preview-' + index);
        if (preview && select.value) {
            preview.innerHTML = `<strong>${amount.toLocaleString('en-US', {minimumFractionDigits: 2})}</strong> ETB`;
        }
    }
    
    updateSummary();
}

function updateSummary() {
    let total = 0;
    document.querySelectorAll('.qty-input').forEach(input => {
        const row = input.closest('.boq-item-row');
        const select = row.querySelector('.boq-select');
        if (select && select.value) {
            const option = select.options[select.selectedIndex];
            const rate = parseFloat(option?.getAttribute('data-rate')) || 0;
            const qty = parseFloat(input.value) || 0;
            total += qty * rate;
        }
    });
    
    const retentionPct = parseFloat(document.querySelector('[name="retention_percentage"]')?.value) || 5;
    const retention = total * (retentionPct / 100);
    
    document.getElementById('totalAmount').textContent = total.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ETB';
    document.getElementById('retentionAmount').textContent = retention.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ETB';
    document.getElementById('netAmount').textContent = (total - retention).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ETB';
}

function addItemRow() {
    const boqOptions = document.querySelector('.boq-select')?.innerHTML || '<option value="">-- Select BOQ Item --</option>';
    
    const html = `
    <div class="boq-item-row" data-index="${itemIndex}">
        <div class="row align-items-end">
            <div class="col-md-5 mb-2">
                <select name="items[${itemIndex}][boq_item_id]" class="form-select form-select-sm boq-select" onchange="showItemPreview(this, ${itemIndex})">
                    ${boqOptions}
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <input type="number" name="items[${itemIndex}][current_quantity]" class="form-control form-control-sm qty-input" step="0.01" min="0" placeholder="0.00" required oninput="calculateRow(${itemIndex})">
            </div>
            <div class="col-md-3 mb-2">
                <div class="item-preview" id="preview-${itemIndex}">
                    <span class="text-muted">Select an item</span>
                </div>
            </div>
            <div class="col-md-2 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.boq-item-row').remove(); updateSummary();">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>`;
    document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', html);
    itemIndex++;
}

// Update on retention change
document.querySelector('[name="retention_percentage"]')?.addEventListener('input', updateSummary);
</script>
@endpush
