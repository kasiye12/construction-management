@extends('layouts.app')

@section('title', 'BOQ Items - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h2>📋 Bill of Quantities</h2></div>
        @if(\App\Helpers\PermissionHelper::canCreate('boq'))
        <a href="{{ route('boq-items.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New BOQ Item</a>
        @endif
    </div>
</div>

<!-- Professional Filter -->
<div class="card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h6></div>
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">📁 Project</label>
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ Str::limit($p->name, 30) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">📊 Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>In Progress</option>
                    <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">🔍 Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Item number or description..." value="{{ request('search') }}">
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
            @if(request()->anyFilled(['project_id','status','search','date_from','date_to']))
            <div class="col-md-1">
                <a href="{{ route('boq-items.index') }}" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-times"></i></a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Item No.</th><th>Description</th><th>Project</th><th>Unit</th><th class="text-end">Revenue</th><th class="text-end">Budget</th><th class="text-end">P/L</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($boqItems as $item)
                <tr>
                    <td><strong>{{ $item->item_number }}</strong></td>
                    <td><a href="{{ route('boq-items.show', $item) }}" class="text-decoration-none">{{ Str::limit($item->description, 45) }}</a></td>
                    <td>{{ Str::limit($item->project->name ?? 'N/A', 25) }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-end">{{ number_format($item->revenue_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($item->total_budget_cost, 2) }}</td>
                    <td class="text-end fw-bold {{ $item->profit_loss >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($item->profit_loss, 2) }}</td>
                    <td><span class="badge bg-{{ $item->status=='completed'?'success':($item->status=='in_progress'?'warning':'secondary') }}">{{ ucfirst($item->status) }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if(\App\Helpers\PermissionHelper::canView('boq'))<a href="{{ route('boq-items.show', $item) }}" class="btn btn-info"><i class="fas fa-eye"></i></a>@endif
                            @if(\App\Helpers\PermissionHelper::canEdit('boq'))<a href="{{ route('boq-items.edit', $item) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>@endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-4">No BOQ items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $boqItems->links() }}</div>
</div>
@endsection
