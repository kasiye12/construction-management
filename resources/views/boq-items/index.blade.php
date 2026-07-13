@extends('layouts.app')

@section('title', 'BOQ Items - CMS')

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
        <a href="{{ route('boq-items.create') }}" class="btn btn-primary btn-custom">
            <i class="fas fa-plus me-2"></i>New BOQ Item
        </a>
    </div>
</div>

<div class="table-card">
    @if(request('project_id'))
        <div class="alert alert-info">
            Filtered by Project: {{ \App\Models\Project::find(request('project_id'))->name ?? 'Unknown' }}
            <a href="{{ route('boq-items.index') }}" class="float-end">Clear Filter</a>
        </div>
    @endif
    
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead class="table-light">
                <tr>
                    <th>Item No.</th>
                    <th>Description</th>
                    <th>Project</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th>Quantity</th>
                    <th>Rate</th>
                    <th>Revenue</th>
                    <th>Budget</th>
                    <th>P/L</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($boqItems as $item)
                <tr>
                    <td>{{ $item->item_number }}</td>
                    <td>
                        <a href="{{ route('boq-items.show', $item) }}">
                            {{ Str::limit($item->description, 50) }}
                        </a>
                        @if($item->is_parent)
                            <span class="badge bg-info ms-1">Group</span>
                        @endif
                    </td>
                    <td>{{ $item->project->name ?? 'N/A' }}</td>
                    <td>{{ $item->costCategory->name ?? 'N/A' }}</td>
                    <td>{{ $item->unit }}</td>
                    <td>{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ number_format($item->unit_rate, 2) }}</td>
                    <td>{{ number_format($item->revenue_amount, 2) }}</td>
                    <td>{{ number_format($item->total_budget_cost, 2) }}</td>
                    <td>
                        <span class="badge {{ $item->profit_loss >= 0 ? 'profit-badge' : 'loss-badge' }}">
                            {{ number_format($item->profit_loss, 2) }}
                        </span>
                    </td>
                    <td>
                        @if($item->status == 'completed')
                            <span class="badge bg-success">Completed</span>
                        @elseif($item->status == 'in_progress')
                            <span class="badge bg-warning">In Progress</span>
                        @else
                            <span class="badge bg-secondary">Pending</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('boq-items.show', $item) }}" class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('boq-items.edit', $item) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h4>No BOQ Items Found</h4>
                        <a href="{{ route('boq-items.create') }}" class="btn btn-primary">Create First Item</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $boqItems->links() }}
</div>
@endsection
