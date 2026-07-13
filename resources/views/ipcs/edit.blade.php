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
