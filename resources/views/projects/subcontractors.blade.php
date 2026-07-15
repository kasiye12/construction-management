@extends('layouts.app')

@section('title', 'Manage Subcontractors - ' . $project->name)

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>👥 Manage Subcontractors</h2>
            <p class="text-muted">Project: <strong>{{ $project->name }}</strong></p>
        </div>
        <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Project
        </a>
    </div>
</div>

<div class="row">
    <!-- Assigned Subcontractors -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">✅ Assigned Subcontractors ({{ $assignedSubs->count() }})</h5></div>
            <div class="card-body">
                @if($assignedSubs->count() > 0)
                    @foreach($assignedSubs as $sub)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $sub->name }}</h6>
                                <small class="text-muted">{{ $sub->contact_person ?? 'No contact' }} | {{ $sub->phone ?? 'No phone' }}</small>
                                <br>
                                <span class="badge bg-primary">Contract: {{ number_format($sub->pivot->contract_amount, 2) }} ETB</span>
                                @if($sub->pivot->scope_of_work)
                                    <br><small><strong>Scope:</strong> {{ $sub->pivot->scope_of_work }}</small>
                                @endif
                                @if($sub->pivot->contract_start_date)
                                    <br><small><strong>Period:</strong> {{ date('M d, Y', strtotime($sub->pivot->contract_start_date)) }} - {{ $sub->pivot->contract_end_date ? date('M d, Y', strtotime($sub->pivot->contract_end_date)) : 'Ongoing' }}</small>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $sub->id }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form action="{{ route('projects.subcontractors.remove', [$project, $sub]) }}" method="POST" onsubmit="return confirm('Remove this subcontractor?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal{{ $sub->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('projects.subcontractors.update', [$project, $sub]) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Contract - {{ $sub->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Contract Amount (ETB)</label>
                                            <input type="number" name="contract_amount" class="form-control" value="{{ $sub->pivot->contract_amount }}" step="0.01">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Start Date</label>
                                                <input type="date" name="contract_start_date" class="form-control" value="{{ $sub->pivot->contract_start_date }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">End Date</label>
                                                <input type="date" name="contract_end_date" class="form-control" value="{{ $sub->pivot->contract_end_date }}">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Scope of Work</label>
                                            <textarea name="scope_of_work" class="form-control" rows="3">{{ $sub->pivot->scope_of_work }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update Contract</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-3">No subcontractors assigned yet.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Add Subcontractor -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">➕ Assign Subcontractor</h5></div>
            <div class="card-body">
                @if($availableSubs->count() > 0)
                <form action="{{ route('projects.subcontractors.assign', $project) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Subcontractor <span class="text-danger">*</span></label>
                        <select name="subcontractor_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            @foreach($availableSubs as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contract Amount (ETB)</label>
                        <input type="number" name="contract_amount" class="form-control" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Scope of Work</label>
                        <textarea name="scope_of_work" class="form-control" rows="2" placeholder="e.g., Water proofing works for foundation"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="contract_start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="contract_end_date" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-link me-1"></i> Assign to Project
                    </button>
                </form>
                @else
                    <p class="text-muted text-center">All active subcontractors are already assigned.</p>
                    <a href="{{ route('subcontractors.create') }}" class="btn btn-primary w-100">Create New Subcontractor</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
