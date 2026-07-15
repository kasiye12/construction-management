@extends('layouts.app')

@section('title', 'Create IPC - CMS')

@section('content')
<div class="page-header">
    <h2>➕ Create Payment Certificate (IPC)</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('ipcs.index') }}">IPCs</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<form action="{{ route('ipcs.store') }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-md-12">
            <div class="table-card mb-4">
                <h5>Certificate Details</h5><hr>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                        <select class="form-select @error('project_id') is-invalid @enderror" 
                                id="project_id" name="project_id" required
                                onchange="window.location.href='{{ route('ipcs.create') }}?project_id='+this.value">
                            <option value="">-- Select Project First --</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ ($projectId == $project->id) ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select a project to load its subcontractors and BOQ items</small>
                        @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="subcontractor_id" class="form-label">Subcontractor <span class="text-danger">*</span></label>
                        <select class="form-select @error('subcontractor_id') is-invalid @enderror" 
                                id="subcontractor_id" name="subcontractor_id" required
                                {{ $subcontractors->count() == 0 ? 'disabled' : '' }}>
                            <option value="">-- Select Subcontractor --</option>
                            @foreach($subcontractors as $sub)
                                <option value="{{ $sub->id }}" {{ old('subcontractor_id') == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($subcontractors->count() == 0 && $projectId)
                            <small class="text-warning">No subcontractors assigned to this project. 
                                <a href="{{ route('subcontractors.create') }}">Add subcontractor first</a>
                            </small>
                        @endif
                        @error('subcontractor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="ipc_number" class="form-label">IPC Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ipc_number" name="ipc_number" 
                               value="{{ old('ipc_number', 'IPC-'.str_pad(\App\Models\Ipc::count()+1, 3, '0', STR_PAD_LEFT)) }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="issue_number" class="form-label">Issue Number</label>
                        <input type="number" class="form-control" id="issue_number" name="issue_number" 
                               value="{{ old('issue_number', 1) }}" min="1" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="ipc_date" class="form-label">IPC Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="ipc_date" name="ipc_date" 
                               value="{{ old('ipc_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="retention_percentage" class="form-label">Retention %</label>
                        <input type="number" class="form-control" id="retention_percentage" name="retention_percentage" 
                               value="{{ old('retention_percentage', 5) }}" step="0.01" min="0" max="100">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="period_start_date" class="form-label">Period Start <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="period_start_date" name="period_start_date" 
                               value="{{ old('period_start_date') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="period_end_date" class="form-label">Period End <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="period_end_date" name="period_end_date" 
                               value="{{ old('period_end_date') }}" required>
                    </div>
                </div>
            </div>
            
            @if($boqItems->count() > 0)
            <div class="table-card">
                <h5>BOQ Items <span class="text-danger">*</span></h5><hr>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Unit</th>
                                <th>Contract Qty</th>
                                <th>Rate</th>
                                <th>Previous Qty</th>
                                <th>Previous Amount</th>
                                <th>Current Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($boqItems as $index => $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->unit }}</td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" 
                                           name="items[{{ $index }}][contract_quantity]" 
                                           value="{{ $item->quantity }}" step="0.01" readonly>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" 
                                           name="items[{{ $index }}][contract_amount]" 
                                           value="{{ $item->revenue_amount }}" step="0.01" readonly>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" 
                                           name="items[{{ $index }}][previous_quantity]" value="0" step="0.01">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" 
                                           name="items[{{ $index }}][previous_amount]" value="0" step="0.01">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" 
                                           name="items[{{ $index }}][current_quantity]" value="0" step="0.01" required>
                                    <input type="hidden" name="items[{{ $index }}][boq_item_id]" value="{{ $item->id }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary btn-custom" {{ $boqItems->count() == 0 ? 'disabled' : '' }}>
                    <i class="fas fa-save me-2"></i>Create IPC
                </button>
                <a href="{{ route('ipcs.index') }}" class="btn btn-secondary">Cancel</a>
                
                @if(!$projectId)
                    <small class="text-muted ms-3">Please select a project first to enable IPC creation.</small>
                @endif
            </div>
        </div>
    </div>
</form>
@endsection
