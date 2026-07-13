#!/bin/bash

# BOQ Items Index
cat > resources/views/boq-items/index.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', 'BOQ Items - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📋 Bill of Quantities</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">BOQ Items</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('boq-items.create') }}" class="btn btn-primary btn-custom">
            <i class="fas fa-plus me-2"></i>New BOQ Item
        </a>
    </div>
</div>

<div class="table-card">
    @if(request('project_id'))
        <div class="alert alert-info">
            Filtered by Project: {{ \App\Models\Project::find(request('project_id'))->name ?? 'Unknown' }}
            <a href="{{ route('boq-items.index') }}" class="float-end">Clear Filter</a>
        </div>
    @endif
    
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead class="table-light">
                <tr>
                    <th>Item No.</th>
                    <th>Description</th>
                    <th>Project</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Quantity</th>
                    <th>Rate</th>
                    <th>Revenue</th>
                    <th>Budget</th>
                    <th>P/L</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($boqItems as $item)
                <tr>
                    <td>{{ $item->item_number }}</td>
                    <td>
                        <a href="{{ route('boq-items.show', $item) }}">
                            {{ Str::limit($item->description, 50) }}
                        </a>
                        @if($item->is_parent)
                            <span class="badge bg-info ms-1">Group</span>
                        @endif
                    </td>
                    <td>{{ $item->project->name ?? 'N/A' }}</td>
                    <td>{{ $item->costCategory->name ?? 'N/A' }}</td>
                    <td>{{ $item->unit }}</td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ number_format($item->unit_rate, 2) }}</td>
                    <td>{{ number_format($item->revenue_amount, 2) }}</td>
                    <td>{{ number_format($item->total_budget_cost, 2) }}</td>
                    <td>
                        <span class="badge {{ $item->profit_loss >= 0 ? 'profit-badge' : 'loss-badge' }}">
                            {{ number_format($item->profit_loss, 2) }}
                        </span>
                    </td>
                    <td>
                        @if($item->status == 'completed')
                            <span class="badge bg-success">Completed</span>
                        @elseif($item->status == 'in_progress')
                            <span class="badge bg-warning">In Progress</span>
                        @else
                            <span class="badge bg-secondary">Pending</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('boq-items.show', $item) }}" class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('boq-items.edit', $item) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h4>No BOQ Items Found</h4>
                        <a href="{{ route('boq-items.create') }}" class="btn btn-primary">Create First Item</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $boqItems->links() }}
</div>
@endsection
VIEW

# BOQ Items Show
cat > resources/views/boq-items/show.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', 'BOQ Item Details - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📦 BOQ Item Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('boq-items.index') }}">BOQ Items</a></li>
                    <li class="breadcrumb-item active">{{ $boqItem->item_number }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('boq-items.edit', $boqItem) }}" class="btn btn-warning btn-custom">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
        </div>
    </div>
</div>

<!-- Item Overview -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="table-card">
            <h5>Item Information</h5>
            <hr>
            <table class="table table-bordered">
                <tr>
                    <th width="25%">Item Number</th>
                    <td>{{ $boqItem->item_number }}</td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td>{{ $boqItem->description }}</td>
                </tr>
                <tr>
                    <th>Project</th>
                    <td>{{ $boqItem->project->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Category</th>
                    <td>{{ $boqItem->costCategory->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Unit</th>
                    <td>{{ $boqItem->unit }}</td>
                </tr>
                <tr>
                    <th>Quantity</th>
                    <td>{{ number_format($boqItem->quantity, 4) }}</td>
                </tr>
                <tr>
                    <th>Unit Rate</th>
                    <td>{{ number_format($boqItem->unit_rate, 2) }} ETB</td>
                </tr>
                <tr>
                    <th>Revenue Amount</th>
                    <td><strong>{{ number_format($boqItem->revenue_amount, 2) }} ETB</strong></td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="table-card mb-3">
            <h5>Financial Summary</h5>
            <hr>
            <div class="mb-3">
                <label class="text-muted">Total Budget Cost</label>
                <h4>{{ number_format($totalBudgetCost, 2) }} ETB</h4>
            </div>
            <div class="mb-3">
                <label class="text-muted">Profit/Loss</label>
                <h4 class="{{ $profitLoss >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($profitLoss, 2) }} ETB
                </h4>
            </div>
            <div>
                <label class="text-muted">Profit Margin</label>
                <h4>{{ number_format($profitMargin, 2) }}%</h4>
            </div>
        </div>
        
        <div class="table-card">
            <h5>Status</h5>
            <hr>
            <span class="badge {{ $profitLossStatus == 'PROFIT' ? 'profit-badge' : 'loss-badge' }} fs-6">
                {{ $profitLossStatus }}
            </span>
        </div>
    </div>
</div>

<!-- Resource Breakdown -->
<div class="row">
    <div class="col-md-4">
        <div class="table-card">
            <h5>👷 Labor Resources</h5>
            <hr>
            @forelse($boqItem->laborResources as $labor)
            <div class="resource-card">
                <strong>{{ $labor->trade_name }}</strong>
                <div class="row mt-2">
                    <div class="col-6"><small class="text-muted">Workers:</small><br>{{ $labor->number_of_workers }}</div>
                    <div class="col-6"><small class="text-muted">Hours:</small><br>{{ $labor->total_hours }}</div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Amount:</small>
                    <strong class="float-end">{{ number_format($labor->amount, 2) }} ETB</strong>
                </div>
            </div>
            @empty
            <p class="text-muted">No labor resources</p>
            @endforelse
            <div class="mt-3 pt-3 border-top">
                <strong>Total Labor: {{ number_format($boqItem->laborResources->sum('amount'), 2) }} ETB</strong>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="table-card">
            <h5>🧱 Material Resources</h5>
            <hr>
            @forelse($boqItem->materialResources as $material)
            <div class="resource-card">
                <strong>{{ $material->description }}</strong>
                <div class="row mt-2">
                    <div class="col-6"><small class="text-muted">Qty:</small><br>{{ number_format($material->quantity, 2) }} {{ $material->unit }}</div>
                    <div class="col-6"><small class="text-muted">Rate:</small><br>{{ number_format($material->unit_rate, 2) }}</div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Amount:</small>
                    <strong class="float-end">{{ number_format($material->amount, 2) }} ETB</strong>
                </div>
            </div>
            @empty
            <p class="text-muted">No material resources</p>
            @endforelse
            <div class="mt-3 pt-3 border-top">
                <strong>Total Material: {{ number_format($boqItem->materialResources->sum('amount'), 2) }} ETB</strong>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="table-card">
            <h5>🚜 Equipment Resources</h5>
            <hr>
            @forelse($boqItem->equipmentResources as $equipment)
            <div class="resource-card">
                <strong>{{ $equipment->description }}</strong>
                <div class="row mt-2">
                    <div class="col-6"><small class="text-muted">Units:</small><br>{{ $equipment->number_of_units }}</div>
                    <div class="col-6"><small class="text-muted">Hours:</small><br>{{ $equipment->total_hours }}</div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Amount:</small>
                    <strong class="float-end">{{ number_format($equipment->amount, 2) }} ETB</strong>
                </div>
            </div>
            @empty
            <p class="text-muted">No equipment resources</p>
            @endforelse
            <div class="mt-3 pt-3 border-top">
                <strong>Total Equipment: {{ number_format($boqItem->equipmentResources->sum('amount'), 2) }} ETB</strong>
            </div>
        </div>
    </div>
</div>
@endsection
VIEW

# BOQ Items Create
cat > resources/views/boq-items/create.blade.php << 'VIEW'
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
VIEW

echo "✅ BOQ views created!"
