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

<form action="{{ route('boq-items.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-8">
            <!-- Basic Information -->
            <div class="table-card mb-4">
                <h5>Basic Information</h5>
                <hr>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                        <select class="form-select @error('project_id') is-invalid @enderror" id="project_id" name="project_id" required>
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id', $projectId) == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="cost_category_id" class="form-label">Cost Category</label>
                        <select class="form-select" id="cost_category_id" name="cost_category_id">
                            <option value="">Select Category</option>
                            @foreach($costCategories as $category)
                                <option value="{{ $category->id }}" {{ old('cost_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->code }} - {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="item_number" class="form-label">Item Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('item_number') is-invalid @enderror" 
                               id="item_number" name="item_number" value="{{ old('item_number') }}" required>
                        @error('item_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                        <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                            <option value="">Select Unit</option>
                            <option value="m2" {{ old('unit') == 'm2' ? 'selected' : '' }}>m²</option>
                            <option value="m3" {{ old('unit') == 'm3' ? 'selected' : '' }}>m³</option>
                            <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>kg</option>
                            <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>pcs</option>
                            <option value="LS" {{ old('unit') == 'LS' ? 'selected' : '' }}>LS</option>
                            <option value="m" {{ old('unit') == 'm' ? 'selected' : '' }}>m</option>
                        </select>
                        @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="is_parent" name="is_parent" value="1" {{ old('is_parent') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_parent">Is Parent/Group Item</label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                               id="quantity" name="quantity" value="{{ old('quantity') }}" step="0.0001" min="0" required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="unit_rate" class="form-label">Unit Rate (ETB) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('unit_rate') is-invalid @enderror" 
                               id="unit_rate" name="unit_rate" value="{{ old('unit_rate') }}" step="0.01" min="0" required>
                        @error('unit_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="duration_days" class="form-label">Duration (Days)</label>
                        <input type="number" class="form-control" id="duration_days" name="duration_days" 
                               value="{{ old('duration_days') }}" min="0">
                    </div>
                </div>
            </div>
            
            <!-- Resource Breakdowns -->
            <div class="table-card">
                <h5>Resource Breakdown</h5>
                <hr>
                <p class="text-muted small">Add resource details after creating the item.</p>
            </div>
            
            <div class="mt-3">
                <button type="submit" class="btn btn-primary btn-custom">
                    <i class="fas fa-save me-2"></i>Create BOQ Item
                </button>
                <a href="{{ route('boq-items.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="table-card">
                <h5><i class="fas fa-info-circle me-2"></i>Tips</h5>
                <hr>
                <ul class="list-unstyled">
                    <li class="mb-2">📌 Revenue = Quantity × Unit Rate</li>
                    <li class="mb-2">👥 Add labor, material, and equipment after creation</li>
                    <li class="mb-2">📊 Parent items are used for grouping</li>
                </ul>
            </div>
        </div>
    </div>
</form>
@endsection
