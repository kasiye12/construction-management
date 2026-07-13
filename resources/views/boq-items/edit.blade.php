@extends('layouts.app')

@section('title', 'Edit BOQ Item - CMS')

@section('content')
<div class="page-header">
    <h2>✏️ Edit BOQ Item</h2>
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
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-8">
            <div class="table-card mb-4">
                <h5>Basic Information</h5>
                <hr>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="project_id" class="form-label">Project</label>
                        <select class="form-select" id="project_id" name="project_id" required>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ $boqItem->project_id == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="cost_category_id" class="form-label">Cost Category</label>
                        <select class="form-select" id="cost_category_id" name="cost_category_id">
                            <option value="">None</option>
                            @foreach($costCategories as $category)
                                <option value="{{ $category->id }}" {{ $boqItem->cost_category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->code }} - {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="item_number" class="form-label">Item Number</label>
                        <input type="text" class="form-control" id="item_number" name="item_number" 
                               value="{{ old('item_number', $boqItem->item_number) }}" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="unit" class="form-label">Unit</label>
                        <select class="form-select" id="unit" name="unit" required>
                            <option value="m2" {{ $boqItem->unit == 'm2' ? 'selected' : '' }}>m²</option>
                            <option value="m3" {{ $boqItem->unit == 'm3' ? 'selected' : '' }}>m³</option>
                            <option value="kg" {{ $boqItem->unit == 'kg' ? 'selected' : '' }}>kg</option>
                            <option value="pcs" {{ $boqItem->unit == 'pcs' ? 'selected' : '' }}>pcs</option>
                            <option value="LS" {{ $boqItem->unit == 'LS' ? 'selected' : '' }}>LS</option>
                            <option value="m" {{ $boqItem->unit == 'm' ? 'selected' : '' }}>m</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending" {{ $boqItem->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ $boqItem->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ $boqItem->status == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required>{{ old('description', $boqItem->description) }}</textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               value="{{ old('quantity', $boqItem->quantity) }}" step="0.0001" min="0" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="unit_rate" class="form-label">Unit Rate (ETB)</label>
                        <input type="number" class="form-control" id="unit_rate" name="unit_rate" 
                               value="{{ old('unit_rate', $boqItem->unit_rate) }}" step="0.01" min="0" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="duration_days" class="form-label">Duration (Days)</label>
                        <input type="number" class="form-control" id="duration_days" name="duration_days" 
                               value="{{ old('duration_days', $boqItem->duration_days) }}" min="0">
                    </div>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="is_parent" name="is_parent" value="1" 
                           {{ $boqItem->is_parent ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_parent">Is Parent/Group Item</label>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('boq-items.show', $boqItem) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary btn-custom">
                    <i class="fas fa-save me-2"></i>Update BOQ Item
                </button>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="table-card">
                <h5>Financial Info</h5>
                <hr>
                <p><strong>Revenue:</strong> {{ number_format($boqItem->revenue_amount, 2) }} ETB</p>
                <p><strong>Budget Cost:</strong> {{ number_format($boqItem->total_budget_cost, 2) }} ETB</p>
                <p><strong>Profit/Loss:</strong> 
                    <span class="{{ $boqItem->profit_loss >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($boqItem->profit_loss, 2) }} ETB
                    </span>
                </p>
            </div>
        </div>
    </div>
</form>
@endsection
