@extends('layouts.app')

@section('title', 'Edit Project - CMS')

@section('content')
<div class="page-header">
    <h2>✏️ Edit Project</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
            <li class="breadcrumb-item"><a href="{{ route('projects.show', $project) }}">{{ Str::limit($project->name, 50) }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="table-card">
            <form action="{{ route('projects.update', $project) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="name" class="form-label">Project Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name', $project->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="client_name" class="form-label">Client Name</label>
                        <input type="text" class="form-control" id="client_name" name="client_name" 
                               value="{{ old('client_name', $project->client_name) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="contractor_name" class="form-label">Contractor Name</label>
                        <input type="text" class="form-control" id="contractor_name" name="contractor_name" 
                               value="{{ old('contractor_name', $project->contractor_name) }}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ old('end_date', $project->end_date ? $project->end_date->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" {{ old('status', $project->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="on_hold" {{ old('status', $project->status) == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ old('status', $project->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $project->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="contract_amount" class="form-label">Contract Amount (ETB)</label>
                    <input type="number" class="form-control" id="contract_amount" name="contract_amount" 
                           value="{{ old('contract_amount', $project->contract_amount) }}" step="0.01" min="0">
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $project->description) }}</textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                    <button type="submit" class="btn btn-primary btn-custom">
                        <i class="fas fa-save me-2"></i>Update Project
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="table-card mb-4">
            <h5><i class="fas fa-info-circle me-2"></i>Project Info</h5>
            <hr>
            <p><strong>Created:</strong> {{ $project->created_at->format('M d, Y') }}</p>
            <p><strong>Last Updated:</strong> {{ $project->updated_at->format('M d, Y') }}</p>
            <p><strong>BOQ Items:</strong> {{ $project->boqItems->count() }}</p>
            <p><strong>IPCs:</strong> {{ $project->ipcs->count() }}</p>
        </div>
        
        <div class="table-card">
            <h5><i class="fas fa-exclamation-triangle text-warning me-2"></i>Warning</h5>
            <hr>
            <p class="text-muted small">Changing project details may affect linked BOQ items and IPCs. Make sure to review all related data after making changes.</p>
        </div>
    </div>
</div>
@endsection
