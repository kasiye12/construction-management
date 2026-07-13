@extends('layouts.app')

@section('title', 'Create Project - CMS')

@section('content')
<div class="page-header">
    <h2>➕ Create New Project</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card">
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="name" class="form-label">Project Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="client_name" class="form-label">Client Name</label>
                        <input type="text" class="form-control" id="client_name" name="client_name" 
                               value="{{ old('client_name') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="contractor_name" class="form-label">Contractor Name</label>
                        <input type="text" class="form-control" id="contractor_name" name="contractor_name" 
                               value="{{ old('contractor_name') }}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ old('start_date') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ old('end_date') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="on_hold" {{ old('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="contract_amount" class="form-label">Contract Amount (ETB)</label>
                    <input type="number" class="form-control" id="contract_amount" name="contract_amount" 
                           value="{{ old('contract_amount', 0) }}" step="0.01" min="0">
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-custom">
                        <i class="fas fa-save me-2"></i>Create Project
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="table-card">
            <h5><i class="fas fa-info-circle me-2"></i>Tips</h5>
            <hr>
            <ul class="list-unstyled">
                <li class="mb-2">✅ Fill in all required fields marked with *</li>
                <li class="mb-2">💰 Contract amount helps track project value</li>
                <li class="mb-2">📅 Set realistic start and end dates</li>
                <li class="mb-2">📝 Add detailed description for clarity</li>
            </ul>
        </div>
    </div>
</div>
@endsection
