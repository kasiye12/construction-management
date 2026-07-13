@extends('layouts.app')

@section('title', 'BOQ Item Details - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📦 BOQ Item Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('boq-items.index') }}">BOQ Items</a></li>
                    <li class="breadcrumb-item active">{{ $boqItem->item_number }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('boq-items.edit', $boqItem) }}" class="btn btn-warning btn-custom">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
        </div>
    </div>
</div>

<!-- Item Overview -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="table-card">
            <h5>Item Information</h5>
            <hr>
            <table class="table table-bordered">
                <tr>
                    <th width="25%">Item Number</th>
                    <td>{{ $boqItem->item_number }}</td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td>{{ $boqItem->description }}</td>
                </tr>
                <tr>
                    <th>Project</th>
                    <td>{{ $boqItem->project->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Category</th>
                    <td>{{ $boqItem->costCategory->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Unit</th>
                    <td>{{ $boqItem->unit }}</td>
                </tr>
                <tr>
                    <th>Quantity</th>
                    <td>{{ number_format($boqItem->quantity, 4) }}</td>
                </tr>
                <tr>
                    <th>Unit Rate</th>
                    <td>{{ number_format($boqItem->unit_rate, 2) }} ETB</td>
                </tr>
                <tr>
                    <th>Revenue Amount</th>
                    <td><strong>{{ number_format($boqItem->revenue_amount, 2) }} ETB</strong></td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="table-card mb-3">
            <h5>Financial Summary</h5>
            <hr>
            <div class="mb-3">
                <label class="text-muted">Total Budget Cost</label>
                <h4>{{ number_format($totalBudgetCost, 2) }} ETB</h4>
            </div>
            <div class="mb-3">
                <label class="text-muted">Profit/Loss</label>
                <h4 class="{{ $profitLoss >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($profitLoss, 2) }} ETB
                </h4>
            </div>
            <div>
                <label class="text-muted">Profit Margin</label>
                <h4>{{ number_format($profitMargin, 2) }}%</h4>
            </div>
        </div>
        
        <div class="table-card">
            <h5>Status</h5>
            <hr>
            <span class="badge {{ $profitLossStatus == 'PROFIT' ? 'profit-badge' : 'loss-badge' }} fs-6">
                {{ $profitLossStatus }}
            </span>
        </div>
    </div>
</div>

<!-- Resource Breakdown -->
<div class="row">
    <div class="col-md-4">
        <div class="table-card">
            <h5>👷 Labor Resources</h5>
            <hr>
            @forelse($boqItem->laborResources as $labor)
            <div class="resource-card">
                <strong>{{ $labor->trade_name }}</strong>
                <div class="row mt-2">
                    <div class="col-6"><small class="text-muted">Workers:</small><br>{{ $labor->number_of_workers }}</div>
                    <div class="col-6"><small class="text-muted">Hours:</small><br>{{ $labor->total_hours }}</div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Amount:</small>
                    <strong class="float-end">{{ number_format($labor->amount, 2) }} ETB</strong>
                </div>
            </div>
            @empty
            <p class="text-muted">No labor resources</p>
            @endforelse
            <div class="mt-3 pt-3 border-top">
                <strong>Total Labor: {{ number_format($boqItem->laborResources->sum('amount'), 2) }} ETB</strong>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="table-card">
            <h5>🧱 Material Resources</h5>
            <hr>
            @forelse($boqItem->materialResources as $material)
            <div class="resource-card">
                <strong>{{ $material->description }}</strong>
                <div class="row mt-2">
                    <div class="col-6"><small class="text-muted">Qty:</small><br>{{ number_format($material->quantity, 2) }} {{ $material->unit }}</div>
                    <div class="col-6"><small class="text-muted">Rate:</small><br>{{ number_format($material->unit_rate, 2) }}</div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Amount:</small>
                    <strong class="float-end">{{ number_format($material->amount, 2) }} ETB</strong>
                </div>
            </div>
            @empty
            <p class="text-muted">No material resources</p>
            @endforelse
            <div class="mt-3 pt-3 border-top">
                <strong>Total Material: {{ number_format($boqItem->materialResources->sum('amount'), 2) }} ETB</strong>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="table-card">
            <h5>🚜 Equipment Resources</h5>
            <hr>
            @forelse($boqItem->equipmentResources as $equipment)
            <div class="resource-card">
                <strong>{{ $equipment->description }}</strong>
                <div class="row mt-2">
                    <div class="col-6"><small class="text-muted">Units:</small><br>{{ $equipment->number_of_units }}</div>
                    <div class="col-6"><small class="text-muted">Hours:</small><br>{{ $equipment->total_hours }}</div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Amount:</small>
                    <strong class="float-end">{{ number_format($equipment->amount, 2) }} ETB</strong>
                </div>
            </div>
            @empty
            <p class="text-muted">No equipment resources</p>
            @endforelse
            <div class="mt-3 pt-3 border-top">
                <strong>Total Equipment: {{ number_format($boqItem->equipmentResources->sum('amount'), 2) }} ETB</strong>
            </div>
        </div>
    </div>
</div>
@endsection
