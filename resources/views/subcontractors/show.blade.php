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
