@extends('layouts.app')

@section('title', 'Actual Costs - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h2>💰 Actual Cost Tracking</h2></div>
        @if(\App\Helpers\PermissionHelper::canCreate('actual-costs'))
        <a href="{{ route('actual-costs.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Cost</a>
        @endif
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body text-center py-2"><h6>BUDGET</h6><h3>{{ number_format($totalBudget,0) }}</h3></div></div></div>
    <div class="col-md-3"><div class="card bg-warning text-white"><div class="card-body text-center py-2"><h6>ACTUAL</h6><h3>{{ number_format($totalActual,0) }}</h3></div></div></div>
    <div class="col-md-3"><div class="card bg-{{ $variance>=0?'success':'danger' }} text-white"><div class="card-body text-center py-2"><h6>VARIANCE</h6><h3>{{ number_format($variance,0) }}</h3></div></div></div>
    <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body text-center py-2"><h6>USAGE</h6><h3>{{ $percentUsed }}%</h3></div></div></div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Date</th><th>Project</th><th>Type</th><th>Description</th><th>Amount</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($costs as $cost)
                <tr>
                    <td>{{ $cost->cost_date->format('M d, Y') }}</td>
                    <td>{{ Str::limit($cost->project->name ?? 'N/A', 25) }}</td>
                    <td><span class="badge bg-info">{{ ucfirst($cost->cost_type) }}</span></td>
                    <td>{{ Str::limit($cost->description, 40) }}</td>
                    <td class="text-end fw-bold">{{ number_format($cost->amount, 2) }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if(\App\Helpers\PermissionHelper::canEdit('actual-costs'))
                            <a href="{{ route('actual-costs.edit', $cost) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canDelete('actual-costs'))
                            <form action="{{ route('actual-costs.destroy', $cost) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4">No costs recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $costs->links() }}</div>
</div>
@endsection
