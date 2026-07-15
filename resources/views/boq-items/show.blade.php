@extends('layouts.app')

@section('title', 'BOQ Item - ' . $boqItem->item_number)

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📦 {{ $boqItem->item_number }} - {{ Str::limit($boqItem->description, 60) }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('boq-items.index') }}">BOQ Items</a></li>
                    <li class="breadcrumb-item active">{{ $boqItem->item_number }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('boq-items.edit', $boqItem) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Item Details -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">📋 Item Information</h5></div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <tr><th width="150">Item Number</th><td>{{ $boqItem->item_number }}</td></tr>
                    <tr><th>Description</th><td>{{ $boqItem->description }}</td></tr>
                    <tr><th>Project</th><td>{{ $boqItem->project->name ?? 'N/A' }}</td></tr>
                    <tr><th>Category</th><td>{{ $boqItem->costCategory->code ?? '' }} - {{ $boqItem->costCategory->name ?? 'N/A' }}</td></tr>
                    <tr><th>Unit</th><td>{{ $boqItem->unit }}</td></tr>
                    <tr><th>Quantity</th><td>{{ number_format($boqItem->quantity, 4) }}</td></tr>
                    <tr><th>Unit Rate</th><td>{{ number_format($boqItem->unit_rate, 2) }} ETB</td></tr>
                    <tr><th>Revenue</th><td><strong>{{ number_format($boqItem->revenue_amount, 2) }} ETB</strong></td></tr>
                    <tr><th>Duration</th><td>{{ $boqItem->duration_days ?? 'N/A' }} days</td></tr>
                    <tr><th>Period</th><td>
                        {{ $boqItem->planned_start_date ? $boqItem->planned_start_date->format('M d, Y') : 'N/A' }} - 
                        {{ $boqItem->planned_end_date ? $boqItem->planned_end_date->format('M d, Y') : 'N/A' }}
                    </td></tr>
                    <tr><th>Status</th><td>
                        <span class="badge bg-{{ $boqItem->status == 'completed' ? 'success' : ($boqItem->status == 'in_progress' ? 'warning' : 'secondary') }}">
                            {{ ucfirst(str_replace('_', ' ', $boqItem->status)) }}
                        </span>
                    </td></tr>
                </table>
            </div>
        </div>

        <!-- Resource Breakdown -->
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header"><h6 class="mb-0">👷 Labor ({{ number_format($boqItem->laborResources->sum('amount'), 2) }} ETB)</h6></div>
                    <div class="card-body p-0">
                        @forelse($boqItem->laborResources as $labor)
                        <div class="border-bottom p-2">
                            <strong>{{ $labor->trade_name }}</strong>
                            <div class="row small text-muted">
                                <div class="col-6">Workers: {{ $labor->number_of_workers }}</div>
                                <div class="col-6">Hours: {{ $labor->total_hours }}</div>
                                <div class="col-6">Wage/Day: {{ number_format($labor->wage_per_day, 2) }}</div>
                                <div class="col-6">Amount: <strong>{{ number_format($labor->amount, 2) }}</strong></div>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted p-2 mb-0">No labor resources</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header"><h6 class="mb-0">🧱 Material ({{ number_format($boqItem->materialResources->sum('amount'), 2) }} ETB)</h6></div>
                    <div class="card-body p-0">
                        @forelse($boqItem->materialResources as $material)
                        <div class="border-bottom p-2">
                            <strong>{{ $material->description }}</strong>
                            <div class="row small text-muted">
                                <div class="col-6">Qty: {{ number_format($material->quantity, 2) }} {{ $material->unit }}</div>
                                <div class="col-6">Rate: {{ number_format($material->unit_rate, 2) }}</div>
                                <div class="col-12">Amount: <strong>{{ number_format($material->amount, 2) }}</strong></div>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted p-2 mb-0">No material resources</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header"><h6 class="mb-0">🚜 Equipment ({{ number_format($boqItem->equipmentResources->sum('amount'), 2) }} ETB)</h6></div>
                    <div class="card-body p-0">
                        @forelse($boqItem->equipmentResources as $equipment)
                        <div class="border-bottom p-2">
                            <strong>{{ $equipment->description }}</strong>
                            <div class="row small text-muted">
                                <div class="col-6">Units: {{ $equipment->number_of_units }}</div>
                                <div class="col-6">Hours: {{ $equipment->total_hours }}</div>
                                <div class="col-6">Rate/Hr: {{ number_format($equipment->rate_per_hour, 2) }}</div>
                                <div class="col-6">Amount: <strong>{{ number_format($equipment->amount, 2) }}</strong></div>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted p-2 mb-0">No equipment resources</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">💰 Financial Summary</h5></div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Revenue</small>
                    <h4>{{ number_format($boqItem->revenue_amount, 2) }} ETB</h4>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Total Budget Cost</small>
                    <h4>{{ number_format($boqItem->total_budget_cost, 2) }} ETB</h4>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Profit / Loss</small>
                    <h4 class="{{ $boqItem->profit_loss >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($boqItem->profit_loss, 2) }} ETB
                    </h4>
                </div>
                <div>
                    <small class="text-muted">Profit Margin</small>
                    <h4>{{ number_format($boqItem->profit_margin_percentage, 2) }}%</h4>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">📊 Status</h5></div>
            <div class="card-body text-center">
                <span class="badge {{ $boqItem->profit_loss_status == 'PROFIT' ? 'bg-success' : 'bg-danger' }} fs-5 px-4 py-2">
                    {{ $boqItem->profit_loss_status }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
