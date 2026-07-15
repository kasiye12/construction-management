@extends('layouts.app')

@section('title', 'Budget vs Actual Variance Report - CMS')

@section('content')
<div class="page-header">
    <h2>📊 Budget vs Actual Variance Report</h2>
</div>

@foreach($groupedItems as $categoryName => $items)
    <div class="table-card mb-4">
        <h5>{{ $categoryName }}</h5><hr>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th>Budget</th>
                    <th>Actual</th>
                    <th>Variance</th>
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
                    <td class="text-end {{ $variance >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($variance, 2) }}
                    </td>
                    <td>
                        @if($pct > 100)
                            <span class="badge bg-danger">Over Budget ({{ number_format($pct,0) }}%)</span>
                        @elseif($pct > 80)
                            <span class="badge bg-warning">{{ number_format($pct,0) }}% Used</span>
                        @else
                            <span class="badge bg-success">{{ number_format($pct,0) }}% Used</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach
@endsection
