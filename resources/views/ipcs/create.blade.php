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
