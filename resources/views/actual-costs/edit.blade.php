@extends('layouts.app')

@section('title', 'Edit Actual Cost - CMS')

@section('content')
<div class="page-header">
    <h2>✏️ Edit Actual Cost</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('actual-costs.index') }}">Actual Costs</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Cost Details</h5></div>
            <div class="card-body">
                <form action="{{ route('actual-costs.update', $actualCost) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Project <span class="text-danger">*</span></label>
                            <select name="project_id" class="form-select" required>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" {{ $actualCost->project_id == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">BOQ Item</label>
                            <select name="boq_item_id" class="form-select">
                                <option value="">Select Item (optional)</option>
                                @foreach($boqItems as $item)
                                    <option value="{{ $item->id }}" {{ $actualCost->boq_item_id == $item->id ? 'selected' : '' }}>
                                        {{ $item->item_number }} - {{ Str::limit($item->description, 40) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cost Type <span class="text-danger">*</span></label>
                            <select name="cost_type" class="form-select" required>
                                <option value="labor" {{ $actualCost->cost_type == 'labor' ? 'selected' : '' }}>👷 Labor</option>
                                <option value="material" {{ $actualCost->cost_type == 'material' ? 'selected' : '' }}>🧱 Material</option>
                                <option value="equipment" {{ $actualCost->cost_type == 'equipment' ? 'selected' : '' }}>🚜 Equipment</option>
                                <option value="other" {{ $actualCost->cost_type == 'other' ? 'selected' : '' }}>📋 Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Amount (ETB) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" value="{{ $actualCost->amount }}" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="cost_date" class="form-control" value="{{ $actualCost->cost_date->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="2" required>{{ $actualCost->description }}</textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vendor/Supplier</label>
                            <input type="text" name="vendor" class="form-control" value="{{ $actualCost->vendor }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control" value="{{ $actualCost->invoice_number }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2">{{ $actualCost->remarks }}</textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Cost
                    </button>
                    <a href="{{ route('actual-costs.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
