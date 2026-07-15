@extends('layouts.app')

@section('title', 'Add Actual Cost - CMS')

@section('content')
<div class="page-header">
    <h2>➕ Record Actual Cost</h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card">
            <form action="{{ route('actual-costs.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Project <span class="text-danger">*</span></label>
                        <select name="project_id" class="form-select" required onchange="window.location.href='?project_id='+this.value">
                            <option value="">Select Project</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ $projectId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">BOQ Item</label>
                        <select name="boq_item_id" class="form-select">
                            <option value="">Select Item (optional)</option>
                            @foreach($boqItems as $item)
                                <option value="{{ $item->id }}">{{ $item->item_number }} - {{ Str::limit($item->description, 40) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Cost Type <span class="text-danger">*</span></label>
                        <select name="cost_type" class="form-select" required>
                            <option value="labor">Labor</option>
                            <option value="material">Material</option>
                            <option value="equipment">Equipment</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="cost_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="2" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Vendor/Supplier</label>
                        <input type="text" name="vendor" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" name="invoice_number" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save Cost</button>
                <a href="{{ route('actual-costs.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
