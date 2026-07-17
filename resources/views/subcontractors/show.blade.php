@extends('layouts.app')

@section('title', $subcontractor->name . ' - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h2>👤 {{ $subcontractor->name }}</h2></div>
        <a href="{{ route('subcontractors.edit', $subcontractor) }}" class="btn btn-warning"><i class="fas fa-edit me-1"></i> Edit</a>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Company Details</h5></div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr><th width="150">Contact Person</th><td>{{ $subcontractor->contact_person ?? 'N/A' }}</td></tr>
                    <tr><th>Email</th><td>{{ $subcontractor->email ?? 'N/A' }}</td></tr>
                    <tr><th>Phone</th><td>{{ $subcontractor->phone ?? 'N/A' }}</td></tr>
                    <tr><th>Tax ID</th><td>{{ $subcontractor->tax_id ?? 'N/A' }}</td></tr>
                    <tr><th>Address</th><td>{{ $subcontractor->address ?? 'N/A' }}</td></tr>
                    <tr><th>Status</th><td><span class="badge {{ $subcontractor->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $subcontractor->is_active ? 'Active' : 'Inactive' }}</span></td></tr>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Projects ({{ $subcontractor->projects->count() }})</h5></div>
            <div class="card-body">
                @forelse($subcontractor->projects as $project)
                <div class="border rounded p-3 mb-2">
                    <strong>{{ $project->name }}</strong>
                    <br><small>Contract: {{ number_format($project->pivot->contract_amount, 2) }} ETB</small>
                    @if($project->pivot->scope_of_work)<br><small>Scope: {{ $project->pivot->scope_of_work }}</small>@endif
                </div>
                @empty
                <p class="text-muted">No projects assigned.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Recent IPCs</h5></div>
            <div class="card-body">
                @forelse($subcontractor->ipcs as $ipc)
                <div class="border-bottom pb-2 mb-2">
                    <a href="{{ route('ipcs.show', $ipc) }}">{{ $ipc->ipc_number }}</a>
                    <br><small>{{ number_format($ipc->net_payment_amount, 2) }} ETB - {{ ucfirst($ipc->status) }}</small>
                </div>
                @empty
                <p class="text-muted">No IPCs yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
