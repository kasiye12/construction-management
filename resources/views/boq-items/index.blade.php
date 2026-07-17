@extends('layouts.app')

@section('title', 'BOQ Items - Bill of Quantities')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📋 Bill of Quantities</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">BOQ Items</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('boq-items.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New BOQ Item
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row align-items-end">
            <div class="col-md-4">
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(request('project_id'))
            <div class="col-md-2">
                <a href="{{ route('boq-items.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
            @endif
        </form>
    </div>
</div>

<!-- BOQ Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Item No.</th>
                    <th>Description</th>
                    <th>Project</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th class="text-end">Revenue</th>
                    <th class="text-end">Budget</th>
                    <th class="text-end">P/L</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($boqItems as $item)
                <tr>
                    <td><strong>{{ $item->item_number }}</strong></td>
                    <td>
                        <a href="{{ route('boq-items.show', $item) }}" class="text-decoration-none">
                            {{ Str::limit($item->description, 50) }}
                        </a>
                        @if($item->is_parent)
                            <span class="badge bg-info ms-1">Group</span>
                        @endif
                    </td>
                    <td>{{ $item->project->name ?? 'N/A' }}</td>
                    <td>{{ $item->costCategory->code ?? '' }} {{ $item->costCategory->name ?? 'N/A' }}</td>
                    <td>{{ $item->unit }}</td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ number_format($item->unit_rate, 2) }}</td>
                    <td class="text-end">{{ number_format($item->revenue_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($item->total_budget_cost, 2) }}</td>
                    <td class="text-end fw-bold {{ $item->profit_loss >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($item->profit_loss, 2) }}
                    </td>
                    <td>
                        @if($item->status == 'completed')
                            <span class="badge bg-success">Completed</span>
                        @elseif($item->status == 'in_progress')
                            <span class="badge bg-warning text-dark">In Progress</span>
                        @else
                            <span class="badge bg-secondary">Pending</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('boq-items.show', $item) }}" class="btn btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('boq-items.edit', $item) }}" class="btn btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h5>No BOQ Items Found</h5>
                        <p class="text-muted">Select a project and create your first BOQ item.</p>
                        <a href="{{ route('boq-items.create') }}" class="btn btn-primary">Create BOQ Item</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $boqItems->links() }}
    </div>
</div>
@endsection
