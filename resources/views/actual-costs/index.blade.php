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

<!-- Summary -->
<div class="row mb-3">
    <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body text-center py-2"><h6>BUDGET</h6><h3>{{ number_format($totalBudget,0) }}</h3></div></div></div>
    <div class="col-md-3"><div class="card bg-warning text-white"><div class="card-body text-center py-2"><h6>ACTUAL</h6><h3>{{ number_format($totalActual,0) }}</h3></div></div></div>
    <div class="col-md-3"><div class="card bg-{{ $variance>=0?'success':'danger' }} text-white"><div class="card-body text-center py-2"><h6>VARIANCE</h6><h3>{{ number_format($variance,0) }}</h3></div></div></div>
    <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body text-center py-2"><h6>USAGE</h6><h3>{{ $percentUsed }}%</h3></div></div></div>
</div>

<!-- Professional Filter -->
<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h6></div>
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-bold">📁 Project</label>
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ Str::limit($p->name, 25) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">📂 Type</label>
                <select name="cost_type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="labor" {{ request('cost_type')=='labor'?'selected':'' }}>Labor</option>
                    <option value="material" {{ request('cost_type')=='material'?'selected':'' }}>Material</option>
                    <option value="equipment" {{ request('cost_type')=='equipment'?'selected':'' }}>Equipment</option>
                    <option value="other" {{ request('cost_type')=='other'?'selected':'' }}>Other</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">🔍 Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Description..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">📅 Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">📅 Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i></button>
            </div>
            @if(request()->anyFilled(['project_id','cost_type','search','date_from','date_to']))
            <div class="col-md-1"><a href="{{ route('actual-costs.index') }}" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-times"></i></a></div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Date</th><th>Project</th><th>Type</th><th>Description</th><th class="text-end">Amount</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($costs as $cost)
                <tr>
                    <td>{{ $cost->cost_date->format('M d, Y') }}</td>
                    <td>{{ Str::limit($cost->project->name ?? 'N/A', 25) }}</td>
                    <td><span class="badge bg-{{ $cost->cost_type=='labor'?'primary':($cost->cost_type=='material'?'warning':'info') }}">{{ ucfirst($cost->cost_type) }}</span></td>
                    <td>{{ Str::limit($cost->description, 40) }}</td>
                    <td class="text-end fw-bold">{{ number_format($cost->amount, 2) }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if(\App\Helpers\PermissionHelper::canEdit('actual-costs'))<a href="{{ route('actual-costs.edit', $cost) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>@endif
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
