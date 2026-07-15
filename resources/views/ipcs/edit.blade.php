@extends('layouts.app')

@section('title', 'Edit IPC - CMS')

@section('content')
<div class="page-header">
    <h2>✏️ Edit IPC: {{ $ipc->ipc_number }}</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('ipcs.index') }}">IPCs</a></li>
            <li class="breadcrumb-item"><a href="{{ route('ipcs.show', $ipc) }}">{{ $ipc->ipc_number }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
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
    
    <div class="col-md-4">
        <div class="table-card">
            <h6>IPC Summary</h6><hr>
            <p>Project: {{ $ipc->project->name ?? 'N/A' }}</p>
            <p>Subcontractor: {{ $ipc->subcontractor->name ?? 'N/A' }}</p>
            <p>Net Payment: {{ number_format($ipc->net_payment_amount, 2) }} ETB</p>
        </div>
    </div>
</div>
@endsection
