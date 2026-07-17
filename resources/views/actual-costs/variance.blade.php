@extends('layouts.app')

@section('title', 'Budget vs Actual Variance - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📊 Budget vs Actual Variance Report</h2>
            <p class="text-muted">
                @if($selectedProject) Project: <strong>{{ $selectedProject->name }}</strong> @endif
            </p>
        </div>
        <a href="{{ route('actual-costs.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Costs
        </a>
    </div>
</div>

@foreach($groupedItems as $categoryName => $items)
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">{{ $categoryName }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th class="text-end">Budget</th>
                    <th class="text-end">Actual</th>
                    <th class="text-end">Variance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                @php
                    $budget = $item->total_budget_cost;
                    $actual = $item->actualCosts->sum('amount');
                    $variance = $budget - $actual;
                    $pct = $budget > 0 ? ($actual/$budget)*100 : 0;
                @endphp
                <tr>
                    <td>{{ $item->item_number }} - {{ Str::limit($item->description, 50) }}</td>
                    <td class="text-end">{{ number_format($budget, 2) }}</td>
                    <td class="text-end">{{ number_format($actual, 2) }}</td>
                    <td class="text-end {{ $variance >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                        {{ number_format($variance, 2) }}
                    </td>
                    <td>
                        @if($pct > 100)
                            <span class="badge bg-danger">Over Budget ({{ number_format($pct, 0) }}%)</span>
                        @elseif($pct > 80)
                            <span class="badge bg-warning text-dark">{{ number_format($pct, 0) }}% Used</span>
                        @elseif($pct > 0)
                            <span class="badge bg-success">{{ number_format($pct, 0) }}% Used</span>
                        @else
                            <span class="badge bg-secondary">No Costs</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach
@endsection
