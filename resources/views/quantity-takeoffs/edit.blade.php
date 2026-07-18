@extends('layouts.app')

@section('title', 'Edit Measurement - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2>✏️ Edit Measurement #{{ $quantityTakeoff->id }}</h2>
        <form action="{{ route('quantity-takeoffs.destroy', $quantityTakeoff) }}" method="POST" onsubmit="return confirm('Delete?')" class="d-inline">
            @csrf @method('DELETE')
            <button class="btn btn-danger"><i class="fas fa-trash me-1"></i> Delete</button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Edit Measurement</h5></div>
            <div class="card-body">
                <form action="{{ route('quantity-takeoffs.update', $quantityTakeoff) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Project</label><select name="project_id" class="form-select" required>@foreach($projects as $p)<option value="{{ $p->id }}" {{ $quantityTakeoff->project_id==$p->id?'selected':'' }}>{{ $p->name }}</option>@endforeach</select></div>
                        <div class="col-md-6 mb-3"><label class="form-label">BOQ Item</label><select name="boq_item_id" class="form-select" required>@foreach($boqItems as $item)<option value="{{ $item->id }}" {{ $quantityTakeoff->boq_item_id==$item->id?'selected':'' }}>{{ $item->item_number }} - {{ Str::limit($item->description,40) }}</option>@endforeach</select></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Structure Type</label><input type="text" name="structure_type" class="form-control" value="{{ $quantityTakeoff->structure_type }}"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Element ID</label><input type="text" name="element_id" class="form-control" value="{{ $quantityTakeoff->element_id }}"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Location/Axis</label><input type="text" name="location_axis" class="form-control" value="{{ $quantityTakeoff->location_axis }}"></div>
                    <div class="row">
                        <div class="col-md-3 mb-3"><label class="form-label">Qty Count</label><input type="number" name="quantity_count" class="form-control" value="{{ $quantityTakeoff->quantity_count }}" min="1" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Length (m)</label><input type="number" name="length" class="form-control" value="{{ $quantityTakeoff->length }}" step="0.001"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Width (m)</label><input type="number" name="width" class="form-control" value="{{ $quantityTakeoff->width }}" step="0.001"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Height/Depth (m)</label><input type="number" name="height_depth" class="form-control" value="{{ $quantityTakeoff->height_depth }}" step="0.001"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Date</label><input type="date" name="measurement_date" class="form-control" value="{{ $quantityTakeoff->measurement_date->format('Y-m-d') }}" required></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Status</label><select name="status" class="form-select" required><option value="draft" {{ $quantityTakeoff->status=='draft'?'selected':'' }}>Draft</option><option value="verified" {{ $quantityTakeoff->status=='verified'?'selected':'' }}>Verified</option><option value="approved" {{ $quantityTakeoff->status=='approved'?'selected':'' }}>Approved</option></select></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Total (Auto)</label><input type="text" class="form-control bg-light" value="{{ number_format($quantityTakeoff->total_area_volume, 3) }}" readonly></div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
                    <a href="{{ route('quantity-takeoffs.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
