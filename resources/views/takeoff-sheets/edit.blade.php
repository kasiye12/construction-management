@extends('layouts.app')

@section('title', 'Edit Takeoff Sheet - ' . $sheet->sheet_number)

@push('styles')
<style>
    .item-block { background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 16px; }
    .item-block .item-header { background: #1a237e; color: white; padding: 8px 12px; border-radius: 8px; margin-bottom: 12px; font-weight: bold; }
    .desc-block { background: #f9fafb; border-radius: 8px; padding: 12px; margin-bottom: 8px; }
    .desc-block.left { border-left: 3px solid #4f46e5; }
    .desc-block.right { border-left: 3px solid #10b981; }
    .measurement-row { background: white; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px; margin-bottom: 4px; }
    .btn-add-measurement { border: 2px dashed #cbd5e1; color: #64748b; background: transparent; padding: 8px; border-radius: 8px; width: 100%; font-weight: 600; font-size: 0.75rem; cursor: pointer; }
    .btn-add-measurement:hover { border-color: #4f46e5; color: #4f46e5; background: #eef2ff; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h2>✏️ Edit Takeoff Sheet: {{ $sheet->sheet_number }}</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('takeoff-sheets.index') }}">Takeoff Sheets</a></li>
            <li class="breadcrumb-item"><a href="{{ route('takeoff-sheets.show', $sheet) }}">{{ $sheet->sheet_number }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<form action="{{ route('takeoff-sheets.update', $sheet) }}" method="POST" id="takeoffForm">
    @csrf
    @method('PUT')
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">📋 Sheet Information</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Project <span class="text-danger">*</span></label>
                        <select name="project_id" class="form-select" required onchange="loadBoqItems(this.value)">
                            <option value="">-- Select Project --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ $sheet->project_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">Sheet No <span class="text-danger">*</span></label>
                        <input type="text" name="sheet_number" class="form-control" value="{{ $sheet->sheet_number }}" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Page No</label>
                        <input type="number" name="page_no" class="form-control" value="{{ $sheet->page_no ?? 1 }}" min="1">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Division</label>
                        <input type="text" name="division" class="form-control" value="{{ $sheet->division }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="measurement_date" class="form-control" value="{{ $sheet->measurement_date->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Measured By</label>
                        <input type="text" name="measured_by" class="form-control" value="{{ $sheet->measured_by }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" {{ $sheet->status == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="verified" {{ $sheet->status == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="approved" {{ $sheet->status == 'approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="1">{{ $sheet->remarks }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div id="itemsContainer">
            @php $itemIndex = 0; @endphp
            @foreach($sheet->items as $item)
            @php
                $leftDesc = collect();
                $rightDesc = collect();
                foreach($item->descriptions as $desc) {
                    if($desc->side == 'left') $leftDesc->push($desc);
                    else $rightDesc->push($desc);
                }
                $ld = $leftDesc->first();
                $rd = $rightDesc->first();
            @endphp
            <div class="item-block" data-item="{{ $itemIndex }}">
                <div class="item-header d-flex justify-content-between align-items-center">
                    <span>📋 Item #{{ $item->item_number }}</span>
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="this.closest('.item-block').remove()">✕</button>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label small fw-bold">Item No <span class="text-danger">*</span></label>
                        <input type="text" name="items[{{ $itemIndex }}][item_number]" class="form-control form-control-sm" value="{{ $item->item_number }}" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label small">BOQ Item</label>
                        <select name="items[{{ $itemIndex }}][boq_item_id]" class="form-select form-select-sm boq-select">
                            <option value="">-- Select BOQ --</option>
                            @foreach($boqItems as $b)
                                <option value="{{ $b->id }}" {{ $item->boq_item_id == $b->id ? 'selected' : '' }}>{{ $b->item_number }} - {{ Str::limit($b->description, 40) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label small">Item Description</label>
                        <input type="text" name="items[{{ $itemIndex }}][description]" class="form-control form-control-sm" value="{{ $item->description }}">
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-6">
                        <div class="desc-block left">
                            <h6 class="mb-2">📝 Left Side Description</h6>
                            <input type="text" name="items[{{ $itemIndex }}][left_desc]" class="form-control form-control-sm mb-2" value="{{ $ld->description ?? '' }}" placeholder="Side description">
                            <div class="measurements-container" data-item="{{ $itemIndex }}" data-side="left">
                                @if($ld && $ld->measurements->count() > 0)
                                    @foreach($ld->measurements as $mIndex => $m)
                                    <div class="measurement-row">
                                        <div class="row">
                                            <div class="col-2"><label class="form-label small">Qty</label><input type="number" name="items[{{ $itemIndex }}][left_measurements][{{ $mIndex }}][qty]" class="form-control form-control-sm" value="{{ $m->quantity_count }}" min="1"></div>
                                            <div class="col-2"><label class="form-label small">Length</label><input type="number" name="items[{{ $itemIndex }}][left_measurements][{{ $mIndex }}][length]" class="form-control form-control-sm" step="0.001" min="0" value="{{ $m->length }}"></div>
                                            <div class="col-2"><label class="form-label small">Width</label><input type="number" name="items[{{ $itemIndex }}][left_measurements][{{ $mIndex }}][width]" class="form-control form-control-sm" step="0.001" min="0" value="{{ $m->width }}"></div>
                                            <div class="col-2"><label class="form-label small">Height</label><input type="number" name="items[{{ $itemIndex }}][left_measurements][{{ $mIndex }}][height]" class="form-control form-control-sm" step="0.001" min="0" value="{{ $m->height_depth }}"></div>
                                            <div class="col-3"><label class="form-label small">Desc/Remark</label><input type="text" name="items[{{ $itemIndex }}][left_measurements][{{ $mIndex }}][description]" class="form-control form-control-sm" value="{{ $m->description }}" placeholder="Per measurement desc"></div>
                                            <div class="col-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.measurement-row').remove()">✕</button></div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="measurement-row">
                                        <div class="row">
                                            <div class="col-2"><input type="number" name="items[{{ $itemIndex }}][left_measurements][0][qty]" class="form-control form-control-sm" value="1" min="1"></div>
                                            <div class="col-2"><input type="number" name="items[{{ $itemIndex }}][left_measurements][0][length]" class="form-control form-control-sm" step="0.001" min="0"></div>
                                            <div class="col-2"><input type="number" name="items[{{ $itemIndex }}][left_measurements][0][width]" class="form-control form-control-sm" step="0.001" min="0" value="1"></div>
                                            <div class="col-2"><input type="number" name="items[{{ $itemIndex }}][left_measurements][0][height]" class="form-control form-control-sm" step="0.001" min="0" value="1"></div>
                                            <div class="col-3"><input type="text" name="items[{{ $itemIndex }}][left_measurements][0][description]" class="form-control form-control-sm" placeholder="Per measurement desc"></div>
                                            <div class="col-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.measurement-row').remove()">✕</button></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn-add-measurement mt-2" onclick="addMeasurement(this, {{ $itemIndex }}, 'left')">+ Add Measurement</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="desc-block right">
                            <h6 class="mb-2">📝 Right Side Description</h6>
                            <input type="text" name="items[{{ $itemIndex }}][right_desc]" class="form-control form-control-sm mb-2" value="{{ $rd->description ?? '' }}" placeholder="Side description">
                            <div class="measurements-container" data-item="{{ $itemIndex }}" data-side="right">
                                @if($rd && $rd->measurements->count() > 0)
                                    @foreach($rd->measurements as $mIndex => $m)
                                    <div class="measurement-row">
                                        <div class="row">
                                            <div class="col-2"><label class="form-label small">Qty</label><input type="number" name="items[{{ $itemIndex }}][right_measurements][{{ $mIndex }}][qty]" class="form-control form-control-sm" value="{{ $m->quantity_count }}" min="1"></div>
                                            <div class="col-2"><label class="form-label small">Length</label><input type="number" name="items[{{ $itemIndex }}][right_measurements][{{ $mIndex }}][length]" class="form-control form-control-sm" step="0.001" min="0" value="{{ $m->length }}"></div>
                                            <div class="col-2"><label class="form-label small">Width</label><input type="number" name="items[{{ $itemIndex }}][right_measurements][{{ $mIndex }}][width]" class="form-control form-control-sm" step="0.001" min="0" value="{{ $m->width }}"></div>
                                            <div class="col-2"><label class="form-label small">Height</label><input type="number" name="items[{{ $itemIndex }}][right_measurements][{{ $mIndex }}][height]" class="form-control form-control-sm" step="0.001" min="0" value="{{ $m->height_depth }}"></div>
                                            <div class="col-3"><label class="form-label small">Desc/Remark</label><input type="text" name="items[{{ $itemIndex }}][right_measurements][{{ $mIndex }}][description]" class="form-control form-control-sm" value="{{ $m->description }}" placeholder="Per measurement desc"></div>
                                            <div class="col-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.measurement-row').remove()">✕</button></div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="measurement-row">
                                        <div class="row">
                                            <div class="col-2"><input type="number" name="items[{{ $itemIndex }}][right_measurements][0][qty]" class="form-control form-control-sm" value="1" min="1"></div>
                                            <div class="col-2"><input type="number" name="items[{{ $itemIndex }}][right_measurements][0][length]" class="form-control form-control-sm" step="0.001" min="0"></div>
                                            <div class="col-2"><input type="number" name="items[{{ $itemIndex }}][right_measurements][0][width]" class="form-control form-control-sm" step="0.001" min="0" value="1"></div>
                                            <div class="col-2"><input type="number" name="items[{{ $itemIndex }}][right_measurements][0][height]" class="form-control form-control-sm" step="0.001" min="0" value="1"></div>
                                            <div class="col-3"><input type="text" name="items[{{ $itemIndex }}][right_measurements][0][description]" class="form-control form-control-sm" placeholder="Per measurement desc"></div>
                                            <div class="col-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.measurement-row').remove()">✕</button></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn-add-measurement mt-2" onclick="addMeasurement(this, {{ $itemIndex }}, 'right')">+ Add Measurement</button>
                        </div>
                    </div>
                </div>
            </div>
            @php $itemIndex++; @endphp
            @endforeach
        </div>

        <div class="text-center mb-4">
            <button type="button" class="btn btn-outline-primary btn-lg" onclick="addItem()">
                <i class="fas fa-plus-circle me-2"></i> Add Another Item
            </button>
        </div>

        <button type="submit" class="btn btn-primary btn-lg px-5">
            <i class="fas fa-save me-2"></i> Update Takeoff Sheet
        </button>
        <a href="{{ route('takeoff-sheets.show', $sheet) }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
let itemCount = {{ $sheet->items->count() }};

function loadBoqItems(projectId) {
    if (projectId) {
        window.location.href = '{{ route("takeoff-sheets.edit", $sheet) }}?project_id=' + projectId;
    }
}

function addItem() {
    const boqOptions = document.querySelector('.boq-select') ? document.querySelector('.boq-select').innerHTML : '<option value="">-- Select BOQ --</option>';
    
    const html = `
    <div class="item-block" data-item="${itemCount}">
        <div class="item-header d-flex justify-content-between align-items-center">
            <span>📋 Item #${itemCount + 1}</span>
            <button type="button" class="btn btn-sm btn-outline-light" onclick="this.closest('.item-block').remove()">✕</button>
        </div>
        <div class="row">
            <div class="col-md-3 mb-2"><label class="form-label small">Item No <span class="text-danger">*</span></label><input type="text" name="items[${itemCount}][item_number]" class="form-control form-control-sm" required></div>
            <div class="col-md-3 mb-2"><label class="form-label small">BOQ Item</label><select name="items[${itemCount}][boq_item_id]" class="form-select form-select-sm">${boqOptions}</select></div>
            <div class="col-md-6 mb-2"><label class="form-label small">Item Description</label><input type="text" name="items[${itemCount}][description]" class="form-control form-control-sm"></div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <div class="desc-block left">
                    <h6 class="mb-2">📝 Left Side Description</h6>
                    <input type="text" name="items[${itemCount}][left_desc]" class="form-control form-control-sm mb-2" placeholder="Side description">
                    <div class="measurements-container" data-item="${itemCount}" data-side="left">
                        <div class="measurement-row"><div class="row">
                            <div class="col-2"><input type="number" name="items[${itemCount}][left_measurements][0][qty]" class="form-control form-control-sm" value="1" min="1"></div>
                            <div class="col-2"><input type="number" name="items[${itemCount}][left_measurements][0][length]" class="form-control form-control-sm" step="0.001" min="0"></div>
                            <div class="col-2"><input type="number" name="items[${itemCount}][left_measurements][0][width]" class="form-control form-control-sm" step="0.001" min="0" value="1"></div>
                            <div class="col-2"><input type="number" name="items[${itemCount}][left_measurements][0][height]" class="form-control form-control-sm" step="0.001" min="0" value="1"></div>
                            <div class="col-3"><input type="text" name="items[${itemCount}][left_measurements][0][description]" class="form-control form-control-sm" placeholder="Per measurement desc"></div>
                            <div class="col-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.measurement-row').remove()">✕</button></div>
                        </div></div>
                    </div>
                    <button type="button" class="btn-add-measurement mt-2" onclick="addMeasurement(this, ${itemCount}, 'left')">+ Add Measurement</button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="desc-block right">
                    <h6 class="mb-2">📝 Right Side Description</h6>
                    <input type="text" name="items[${itemCount}][right_desc]" class="form-control form-control-sm mb-2" placeholder="Side description">
                    <div class="measurements-container" data-item="${itemCount}" data-side="right">
                        <div class="measurement-row"><div class="row">
                            <div class="col-2"><input type="number" name="items[${itemCount}][right_measurements][0][qty]" class="form-control form-control-sm" value="1" min="1"></div>
                            <div class="col-2"><input type="number" name="items[${itemCount}][right_measurements][0][length]" class="form-control form-control-sm" step="0.001" min="0"></div>
                            <div class="col-2"><input type="number" name="items[${itemCount}][right_measurements][0][width]" class="form-control form-control-sm" step="0.001" min="0" value="1"></div>
                            <div class="col-2"><input type="number" name="items[${itemCount}][right_measurements][0][height]" class="form-control form-control-sm" step="0.001" min="0" value="1"></div>
                            <div class="col-3"><input type="text" name="items[${itemCount}][right_measurements][0][description]" class="form-control form-control-sm" placeholder="Per measurement desc"></div>
                            <div class="col-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.measurement-row').remove()">✕</button></div>
                        </div></div>
                    </div>
                    <button type="button" class="btn-add-measurement mt-2" onclick="addMeasurement(this, ${itemCount}, 'right')">+ Add Measurement</button>
                </div>
            </div>
        </div>
    </div>`;
    document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', html);
    itemCount++;
}

function addMeasurement(btn, itemIndex, side) {
    const container = btn.previousElementSibling;
    const count = container.querySelectorAll('.measurement-row').length;
    const html = `<div class="measurement-row"><div class="row">
        <div class="col-2"><input type="number" name="items[${itemIndex}][${side}_measurements][${count}][qty]" class="form-control form-control-sm" value="1" min="1"></div>
        <div class="col-2"><input type="number" name="items[${itemIndex}][${side}_measurements][${count}][length]" class="form-control form-control-sm" step="0.001" min="0"></div>
        <div class="col-2"><input type="number" name="items[${itemIndex}][${side}_measurements][${count}][width]" class="form-control form-control-sm" step="0.001" min="0" value="1"></div>
        <div class="col-2"><input type="number" name="items[${itemIndex}][${side}_measurements][${count}][height]" class="form-control form-control-sm" step="0.001" min="0" value="1"></div>
        <div class="col-3"><input type="text" name="items[${itemIndex}][${side}_measurements][${count}][description]" class="form-control form-control-sm" placeholder="Per measurement desc"></div>
        <div class="col-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.measurement-row').remove()">✕</button></div>
    </div></div>`;
    container.insertAdjacentHTML('beforeend', html);
}
</script>
@endpush
