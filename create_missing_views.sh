#!/bin/bash

echo "📝 Creating missing views..."

# BOQ Items Edit View
cat > resources/views/boq-items/edit.blade.php << 'VIEW'
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
VIEW

# Subcontractors Index View
cat > resources/views/subcontractors/index.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', 'Subcontractors - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>👥 Subcontractors</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Subcontractors</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('subcontractors.create') }}" class="btn btn-primary btn-custom">
            <i class="fas fa-plus me-2"></i>New Subcontractor
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Projects</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subcontractors as $subcontractor)
                <tr>
                    <td>
                        <a href="{{ route('subcontractors.show', $subcontractor) }}" class="fw-bold">
                            {{ $subcontractor->name }}
                        </a>
                    </td>
                    <td>{{ $subcontractor->contact_person ?? 'N/A' }}</td>
                    <td>{{ $subcontractor->email ?? 'N/A' }}</td>
                    <td>{{ $subcontractor->phone ?? 'N/A' }}</td>
                    <td>{{ $subcontractor->projects->count() }}</td>
                    <td>
                        <span class="badge {{ $subcontractor->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $subcontractor->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('subcontractors.show', $subcontractor) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('subcontractors.edit', $subcontractor) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h4>No Subcontractors Found</h4>
                        <a href="{{ route('subcontractors.create') }}" class="btn btn-primary">Add Subcontractor</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $subcontractors->links() }}
</div>
@endsection
VIEW

# Subcontractors Create View
cat > resources/views/subcontractors/create.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', 'Create Subcontractor - CMS')

@section('content')
<div class="page-header">
    <h2>➕ Create Subcontractor</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('subcontractors.index') }}">Subcontractors</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card">
            <form action="{{ route('subcontractors.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="name" class="form-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person" 
                               value="{{ old('contact_person') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="{{ old('email') }}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" 
                               value="{{ old('phone') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tax_id" class="form-label">Tax ID</label>
                        <input type="text" class="form-control" id="tax_id" name="tax_id" 
                               value="{{ old('tax_id') }}">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3">{{ old('address') }}</textarea>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('subcontractors.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-custom">
                        <i class="fas fa-save me-2"></i>Create Subcontractor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
VIEW

# Subcontractors Show View
cat > resources/views/subcontractors/show.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', $subcontractor->name . ' - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>👤 {{ $subcontractor->name }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('subcontractors.index') }}">Subcontractors</a></li>
                    <li class="breadcrumb-item active">{{ $subcontractor->name }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('subcontractors.edit', $subcontractor) }}" class="btn btn-warning btn-custom">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card mb-4">
            <h5>Company Information</h5>
            <hr>
            <table class="table table-bordered">
                <tr><th width="30%">Contact Person</th><td>{{ $subcontractor->contact_person ?? 'N/A' }}</td></tr>
                <tr><th>Email</th><td>{{ $subcontractor->email ?? 'N/A' }}</td></tr>
                <tr><th>Phone</th><td>{{ $subcontractor->phone ?? 'N/A' }}</td></tr>
                <tr><th>Tax ID</th><td>{{ $subcontractor->tax_id ?? 'N/A' }}</td></tr>
                <tr><th>Address</th><td>{{ $subcontractor->address ?? 'N/A' }}</td></tr>
                <tr><th>Status</th>
                    <td>
                        <span class="badge {{ $subcontractor->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $subcontractor->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="table-card">
            <h5>Projects</h5>
            <hr>
            @forelse($subcontractor->projects as $project)
            <div class="border rounded p-3 mb-2">
                <strong>{{ $project->name }}</strong>
                <p class="mb-1">Scope: {{ $project->pivot->scope_of_work ?? 'N/A' }}</p>
                <small>Contract: {{ number_format($project->pivot->contract_amount, 2) }} ETB</small>
            </div>
            @empty
            <p class="text-muted">No projects assigned</p>
            @endforelse
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="table-card">
            <h5>Recent IPCs</h5>
            <hr>
            @forelse($subcontractor->ipcs->take(5) as $ipc)
            <div class="border rounded p-2 mb-2">
                <a href="{{ route('ipcs.show', $ipc) }}">{{ $ipc->ipc_number }}</a>
                <br>
                <small>{{ optional($ipc->ipc_date)->format('M d, Y') }} - {{ number_format($ipc->net_payment_amount, 2) }} ETB</small>
            </div>
            @empty
            <p class="text-muted">No IPCs</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
VIEW

# Subcontractors Edit View
cp resources/views/subcontractors/create.blade.php resources/views/subcontractors/edit.blade.php
sed -i 's/Create Subcontractor/Edit Subcontractor/g' resources/views/subcontractors/edit.blade.php
sed -i 's/subcontractors.store/subcontractors.update, $subcontractor/g' resources/views/subcontractors/edit.blade.php
sed -i 's/@csrf/@csrf\n                @method("PUT")/g' resources/views/subcontractors/edit.blade.php
sed -i "s/value=\"{{ old('name') }}\"/value=\"{{ old('name', \$subcontractor->name) }}\"/g" resources/views/subcontractors/edit.blade.php
sed -i "s/value=\"{{ old('contact_person') }}\"/value=\"{{ old('contact_person', \$subcontractor->contact_person) }}\"/g" resources/views/subcontractors/edit.blade.php
sed -i "s/value=\"{{ old('email') }}\"/value=\"{{ old('email', \$subcontractor->email) }}\"/g" resources/views/subcontractors/edit.blade.php
sed -i "s/value=\"{{ old('phone') }}\"/value=\"{{ old('phone', \$subcontractor->phone) }}\"/g" resources/views/subcontractors/edit.blade.php
sed -i "s/value=\"{{ old('tax_id') }}\"/value=\"{{ old('tax_id', \$subcontractor->tax_id) }}\"/g" resources/views/subcontractors/edit.blade.php
sed -i "s/{{ old('address') }}/{{ old('address', \$subcontractor->address) }}/g" resources/views/subcontractors/edit.blade.php

# Cost Categories Index View
cat > resources/views/cost-categories/index.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', 'Cost Categories - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📂 Cost Categories</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Cost Categories</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('cost-categories.create') }}" class="btn btn-primary btn-custom">
            <i class="fas fa-plus me-2"></i>New Category
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Project</th>
                    <th>Order</th>
                    <th>Items</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($costCategories as $category)
                <tr>
                    <td>{{ $category->code ?? 'N/A' }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->project->name ?? 'N/A' }}</td>
                    <td>{{ $category->display_order }}</td>
                    <td>{{ $category->boqItems->count() ?? 0 }}</td>
                    <td>
                        <a href="{{ route('cost-categories.show', $category) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('cost-categories.edit', $category) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">No categories found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
VIEW

# Cost Categories Create View
cat > resources/views/cost-categories/create.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', 'Create Cost Category - CMS')

@section('content')
<div class="page-header">
    <h2>➕ Create Cost Category</h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card">
            <form action="{{ route('cost-categories.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="project_id" class="form-label">Project</label>
                    <select class="form-select" id="project_id" name="project_id" required>
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="code" class="form-label">Code</label>
                        <input type="text" class="form-control" id="code" name="code" placeholder="A, B, C...">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="{{ route('cost-categories.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
VIEW

# Cost Categories Show View
cat > resources/views/cost-categories/show.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', $costCategory->name . ' - CMS')

@section('content')
<div class="page-header">
    <h2>{{ $costCategory->code }} - {{ $costCategory->name }}</h2>
</div>

<div class="table-card">
    <h5>BOQ Items in this Category</h5>
    <hr>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Item No.</th>
                    <th>Description</th>
                    <th>Unit</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($costCategory->boqItems as $item)
                <tr>
                    <td>{{ $item->item_number }}</td>
                    <td><a href="{{ route('boq-items.show', $item) }}">{{ $item->description }}</a></td>
                    <td>{{ $item->unit }}</td>
                    <td>{{ number_format($item->revenue_amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">No items</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
VIEW

# Cost Categories Edit View
cp resources/views/cost-categories/create.blade.php resources/views/cost-categories/edit.blade.php
sed -i 's/Create Cost Category/Edit Cost Category/g' resources/views/cost-categories/edit.blade.php
sed -i 's/cost-categories.store/cost-categories.update, $costCategory/g' resources/views/cost-categories/edit.blade.php
sed -i 's/@csrf/@csrf\n                @method("PUT")/g' resources/views/cost-categories/edit.blade.php

# IPC Show View
cat > resources/views/ipcs/show.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', 'IPC Details - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📄 {{ $ipc->ipc_number }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('ipcs.index') }}">IPCs</a></li>
                    <li class="breadcrumb-item active">{{ $ipc->ipc_number }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="table-card">
            <h5>IPC Information</h5>
            <hr>
            <table class="table table-bordered">
                <tr><th width="30%">Project</th><td>{{ $ipc->project->name ?? 'N/A' }}</td></tr>
                <tr><th>Subcontractor</th><td>{{ $ipc->subcontractor->name ?? 'N/A' }}</td></tr>
                <tr><th>IPC Date</th><td>{{ optional($ipc->ipc_date)->format('M d, Y') }}</td></tr>
                <tr><th>Period</th><td>{{ optional($ipc->period_start_date)->format('M d, Y') }} to {{ optional($ipc->period_end_date)->format('M d, Y') }}</td></tr>
                <tr><th>Status</th><td><span class="badge bg-{{ $ipc->status == 'approved' ? 'success' : 'warning' }}">{{ ucfirst($ipc->status) }}</span></td></tr>
            </table>
        </div>
        
        <div class="table-card mt-4">
            <h5>IPC Items</h5>
            <hr>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Contract Qty</th>
                            <th>Previous</th>
                            <th>Current</th>
                            <th>To Date</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ipc->ipcItems as $item)
                        <tr>
                            <td>{{ $item->boqItem->description ?? 'N/A' }}</td>
                            <td>{{ number_format($item->contract_quantity, 2) }}</td>
                            <td>{{ number_format($item->previous_amount, 2) }}</td>
                            <td>{{ number_format($item->current_amount, 2) }}</td>
                            <td>{{ number_format($item->to_date_amount, 2) }}</td>
                            <td>{{ number_format($item->percentage_complete, 1) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="table-card">
            <h5>Financial Summary</h5>
            <hr>
            <p>Previous: {{ number_format($ipc->total_previous_amount, 2) }} ETB</p>
            <p>Current: {{ number_format($ipc->total_current_amount, 2) }} ETB</p>
            <p>To Date: <strong>{{ number_format($ipc->total_to_date_amount, 2) }} ETB</strong></p>
            <p>Retention ({{ $ipc->retention_percentage }}%): {{ number_format($ipc->retention_amount, 2) }} ETB</p>
            <hr>
            <h4>Net Payment: {{ number_format($ipc->net_payment_amount, 2) }} ETB</h4>
        </div>
    </div>
</div>
@endsection
VIEW

# IPC Create View
cat > resources/views/ipcs/create.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', 'Create IPC - CMS')

@section('content')
<div class="page-header">
    <h2>➕ Create IPC</h2>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="table-card">
            <form action="{{ route('ipcs.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="project_id" class="form-label">Project</label>
                        <select class="form-select" id="project_id" name="project_id" required>
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id', $projectId) == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="subcontractor_id" class="form-label">Subcontractor</label>
                        <select class="form-select" id="subcontractor_id" name="subcontractor_id" required>
                            <option value="">Select Subcontractor</option>
                            @foreach($subcontractors as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="ipc_number" class="form-label">IPC Number</label>
                        <input type="text" class="form-control" id="ipc_number" name="ipc_number" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="ipc_date" class="form-label">IPC Date</label>
                        <input type="date" class="form-control" id="ipc_date" name="ipc_date" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="retention_percentage" class="form-label">Retention %</label>
                        <input type="number" class="form-control" id="retention_percentage" name="retention_percentage" value="5" step="0.01">
                    </div>
                </div>
                
                <h5 class="mt-4">BOQ Items</h5>
                <div class="table-responsive">
                    <table class="table" id="ipc-items-table">
                        <thead>
                            <tr>
                                <th>BOQ Item</th>
                                <th>Contract Qty</th>
                                <th>Contract Amount</th>
                                <th>Previous Qty</th>
                                <th>Previous Amount</th>
                                <th>Current Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($boqItems as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td>
                                    <input type="number" class="form-control" 
                                           name="items[{{ $loop->index }}][contract_quantity]" 
                                           value="{{ $item->quantity }}">
                                </td>
                                <td>
                                    <input type="number" class="form-control" 
                                           name="items[{{ $loop->index }}][contract_amount]" 
                                           value="{{ $item->revenue_amount }}">
                                </td>
                                <td>
                                    <input type="number" class="form-control" 
                                           name="items[{{ $loop->index }}][previous_quantity]" value="0">
                                </td>
                                <td>
                                    <input type="number" class="form-control" 
                                           name="items[{{ $loop->index }}][previous_amount]" value="0">
                                </td>
                                <td>
                                    <input type="number" class="form-control" 
                                           name="items[{{ $loop->index }}][current_quantity]" value="0" step="0.01">
                                    <input type="hidden" name="items[{{ $loop->index }}][boq_item_id]" value="{{ $item->id }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <button type="submit" class="btn btn-primary btn-custom mt-3">
                    <i class="fas fa-save me-2"></i>Create IPC
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
VIEW

# IPC Edit View
cat > resources/views/ipcs/edit.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', 'Edit IPC - CMS')

@section('content')
<div class="page-header">
    <h2>✏️ Edit IPC</h2>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card">
            <form action="{{ route('ipcs.update', $ipc) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="draft" {{ $ipc->status == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ $ipc->status == 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="approved" {{ $ipc->status == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ $ipc->status == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="3">{{ $ipc->remarks }}</textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Update IPC</button>
                <a href="{{ route('ipcs.show', $ipc) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
VIEW

# IPC Print View
cat > resources/views/ipcs/print.blade.php << 'VIEW'
@extends('layouts.app')

@section('title', 'Print IPC - CMS')

@section('content')
<div class="page-header">
    <h2>🖨️ Print IPC: {{ $ipc->ipc_number }}</h2>
    <button onclick="window.print()" class="btn btn-primary">Print</button>
</div>

<div class="table-card">
    <h5>{{ $ipc->project->name ?? 'N/A' }}</h5>
    <p>Subcontractor: {{ $ipc->subcontractor->name ?? 'N/A' }}</p>
    <p>Date: {{ optional($ipc->ipc_date)->format('M d, Y') }}</p>
    
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Item</th>
                <th>Contract Amount</th>
                <th>Previous</th>
                <th>Current</th>
                <th>To Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ipc->ipcItems as $item)
            <tr>
                <td>{{ $item->boqItem->description ?? 'N/A' }}</td>
                <td>{{ number_format($item->contract_amount, 2) }}</td>
                <td>{{ number_format($item->previous_amount, 2) }}</td>
                <td>{{ number_format($item->current_amount, 2) }}</td>
                <td>{{ number_format($item->to_date_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Net Payment</th>
                <th>{{ number_format($ipc->net_payment_amount, 2) }} ETB</th>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
VIEW

echo "✅ All missing views created!"
