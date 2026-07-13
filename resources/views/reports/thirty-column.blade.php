@extends('layouts.app')

@section('title', '30 Column Budget Report - CMS')

@push('styles')
<style>
    .report-table {
        font-size: 10px;
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
    }
    .report-table th, .report-table td {
        border: 1px solid #000;
        padding: 4px 6px;
        text-align: center;
        vertical-align: middle;
    }
    .report-table thead th {
        background-color: #4472C4;
        color: white;
        font-weight: bold;
        font-size: 9px;
    }
    .report-table .category-header {
        background-color: #D6E4F0;
        font-weight: bold;
        text-align: left;
        font-size: 11px;
    }
    .report-table .item-row {
        background-color: #FFF;
    }
    .report-table .sub-row {
        background-color: #F5F5F5;
        font-size: 9px;
    }
    .report-table .total-row {
        background-color: #E2EFDA;
        font-weight: bold;
        font-size: 10px;
    }
    .report-table .profit {
        color: green;
        font-weight: bold;
    }
    .report-table .loss {
        color: red;
        font-weight: bold;
    }
    .report-table .amount-cell {
        text-align: right;
        font-family: 'Courier New', monospace;
    }
    .report-container {
        overflow-x: auto;
        margin: 0;
        padding: 10px;
    }
    .filter-bar {
        background: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>📊 30-Column Budget & Cost Breakdown Report</h2>
            <p class="text-muted">
                @if($selectedProject)
                    Project: <strong>{{ $selectedProject->name }}</strong>
                @else
                    All Projects Summary
                @endif
            </p>
        </div>
        <div>
            <a href="{{ route('reports.30-column.pdf', ['project_id' => $projectId]) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf me-2"></i>Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-secondary ms-2">
                <i class="fas fa-print me-2"></i>Print
            </button>
        </div>
    </div>

    <!-- Filter -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('reports.30-column') }}" class="row align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">Select Project</label>
                <select name="project_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- All Projects --</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ $projectId == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if($projectId)
            <div class="col-md-4">
                <a href="{{ route('reports.30-column') }}" class="btn btn-outline-secondary mt-4">
                    Clear Filter
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Report Table -->
    <div class="report-container">
        <table class="report-table">
            <thead>
                <tr>
                    <th rowspan="2">Cost<br>Category</th>
                    <th rowspan="2">Item<br>No.</th>
                    <th rowspan="2">ITEM DESCRIPTION</th>
                    <th rowspan="2">UNIT</th>
                    <th rowspan="2">BOQ<br>Rate</th>
                    <th rowspan="2">Quantity</th>
                    <th rowspan="2">REVENUE<br>AMOUNT</th>
                    <th rowspan="2">Duration</th>
                    <th rowspan="2">Start<br>Date</th>
                    <th rowspan="2">End<br>Date</th>
                    <th colspan="5" style="background-color: #5B9BD5;">LABOUR</th>
                    <th colspan="4" style="background-color: #ED7D31;">MATERIAL</th>
                    <th colspan="6" style="background-color: #A5A5A5;">EQUIPMENT</th>
                    <th rowspan="2">TOTAL<br>BUDGET<br>AMOUNT</th>
                    <th rowspan="2">PROFIT/<br>LOSS</th>
                    <th rowspan="2">Profit<br>%</th>
                    <th rowspan="2">STATUS</th>
                </tr>
                <tr>
                    <th>TRADE</th>
                    <th>NUMBER</th>
                    <th>TOTAL<br>HOUR</th>
                    <th>WAGE/<br>DAY</th>
                    <th>AMOUNT</th>
                    <th>Description</th>
                    <th>UNIT</th>
                    <th>QUANTITY</th>
                    <th>UNIT<br>RATE</th>
                    <th>DESCRIPTION</th>
                    <th>DURATION</th>
                    <th>NUMBER</th>
                    <th>TOTAL<br>HOUR</th>
                    <th>RATE</th>
                    <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotalRevenue = 0; $grandTotalBudget = 0; @endphp

                @forelse($groupedItems as $categoryName => $categoryItems)
                    @php
                        $catRevenue = $categoryItems->sum('revenue_amount');
                        $catBudget = $categoryItems->sum(function($i) { return $i->total_budget_cost; });
                        $grandTotalRevenue += $catRevenue;
                        $grandTotalBudget += $catBudget;
                    @endphp
                    
                    <tr class="category-header">
                        <td colspan="30">
                            <strong>{{ $categoryName }}</strong>
                            <span class="float-end">
                                Revenue: {{ number_format($catRevenue, 2) }} | Budget: {{ number_format($catBudget, 2) }}
                            </span>
                        </td>
                    </tr>
                    
                    @foreach($categoryItems as $item)
                        @php
                            $laborResources = $item->laborResources;
                            $materialResources = $item->materialResources;
                            $equipmentResources = $item->equipmentResources;
                            $maxRows = max($laborResources->count(), $materialResources->count(), $equipmentResources->count(), 1);
                            $itemBudget = $item->total_budget_cost;
                            $itemProfitLoss = $item->profit_loss;
                        @endphp
                        
                        @for($i = 0; $i < $maxRows; $i++)
                            <tr class="{{ $i == 0 ? 'item-row' : 'sub-row' }}">
                                @if($i == 0)
                                    <td rowspan="{{ $maxRows }}"><strong>{{ $item->costCategory->code ?? '-' }}</strong></td>
                                    <td rowspan="{{ $maxRows }}">{{ $item->item_number }}</td>
                                    <td rowspan="{{ $maxRows }}" style="text-align: left;">{{ $item->description }}</td>
                                    <td rowspan="{{ $maxRows }}">{{ $item->unit }}</td>
                                    <td rowspan="{{ $maxRows }}" class="amount-cell">{{ number_format($item->unit_rate, 2) }}</td>
                                    <td rowspan="{{ $maxRows }}" class="amount-cell">{{ number_format($item->quantity, 2) }}</td>
                                    <td rowspan="{{ $maxRows }}" class="amount-cell">{{ number_format($item->revenue_amount, 2) }}</td>
                                    <td rowspan="{{ $maxRows }}">{{ $item->duration_days ?? '-' }}</td>
                                    <td rowspan="{{ $maxRows }}">{{ $item->planned_start_date ? $item->planned_start_date->format('m/d/Y') : '-' }}</td>
                                    <td rowspan="{{ $maxRows }}">{{ $item->planned_end_date ? $item->planned_end_date->format('m/d/Y') : '-' }}</td>
                                @endif
                                
                                <!-- Labor -->
                                @if(isset($laborResources[$i]))
                                    <td>{{ $laborResources[$i]->trade_name }}</td>
                                    <td class="amount-cell">{{ number_format($laborResources[$i]->number_of_workers, 2) }}</td>
                                    <td class="amount-cell">{{ number_format($laborResources[$i]->total_hours, 2) }}</td>
                                    <td class="amount-cell">{{ number_format($laborResources[$i]->wage_per_day, 2) }}</td>
                                    <td class="amount-cell">{{ number_format($laborResources[$i]->amount, 2) }}</td>
                                @else
                                    <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                                @endif
                                
                                <!-- Material -->
                                @if(isset($materialResources[$i]))
                                    <td style="text-align: left;">{{ $materialResources[$i]->description }}</td>
                                    <td>{{ $materialResources[$i]->unit }}</td>
                                    <td class="amount-cell">{{ number_format($materialResources[$i]->quantity, 2) }}</td>
                                    <td class="amount-cell">{{ number_format($materialResources[$i]->unit_rate, 2) }}</td>
                                @else
                                    <td>-</td><td>-</td><td>-</td><td>-</td>
                                @endif
                                
                                <!-- Equipment -->
                                @if(isset($equipmentResources[$i]))
                                    <td style="text-align: left;">{{ $equipmentResources[$i]->description }}</td>
                                    <td class="amount-cell">{{ number_format($equipmentResources[$i]->duration_days, 2) }}</td>
                                    <td>{{ $equipmentResources[$i]->number_of_units }}</td>
                                    <td class="amount-cell">{{ number_format($equipmentResources[$i]->total_hours, 2) }}</td>
                                    <td class="amount-cell">{{ number_format($equipmentResources[$i]->rate_per_hour, 2) }}</td>
                                    <td class="amount-cell">{{ number_format($equipmentResources[$i]->amount, 2) }}</td>
                                @else
                                    <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                                @endif
                                
                                @if($i == 0)
                                    <td rowspan="{{ $maxRows }}" class="amount-cell"><strong>{{ number_format($itemBudget, 2) }}</strong></td>
                                    <td rowspan="{{ $maxRows }}" class="amount-cell {{ $itemProfitLoss >= 0 ? 'profit' : 'loss' }}">
                                        <strong>{{ number_format($itemProfitLoss, 2) }}</strong>
                                    </td>
                                    <td rowspan="{{ $maxRows }}" class="{{ $item->profit_margin_percentage >= 0 ? 'profit' : 'loss' }}">
                                        {{ number_format($item->profit_margin_percentage, 1) }}%
                                    </td>
                                    <td rowspan="{{ $maxRows }}">
                                        <span class="badge {{ $item->profit_loss_status == 'PROFIT' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $item->profit_loss_status }}
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @endfor
                    @endforeach
                    
                    <!-- Category Subtotal -->
                    <tr class="total-row">
                        <td colspan="6" style="text-align: right;"><strong>Subtotal - {{ $categoryName }}:</strong></td>
                        <td class="amount-cell"><strong>{{ number_format($catRevenue, 2) }}</strong></td>
                        <td colspan="14"></td>
                        <td class="amount-cell"><strong>{{ number_format($catBudget, 2) }}</strong></td>
                        <td class="amount-cell {{ ($catRevenue - $catBudget) >= 0 ? 'profit' : 'loss' }}">
                            <strong>{{ number_format($catRevenue - $catBudget, 2) }}</strong>
                        </td>
                        <td class="{{ $catRevenue > 0 ? (($catRevenue - $catBudget)/$catRevenue*100 >= 0 ? 'profit' : 'loss') : '' }}">
                            {{ $catRevenue > 0 ? number_format(($catRevenue - $catBudget)/$catRevenue*100, 1) : 0 }}%
                        </td>
                        <td></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="30" class="text-center py-4">
                            <h4>No data found</h4>
                            <p>Please add BOQ items with resources to generate the report.</p>
                        </td>
                    </tr>
                @endforelse
                
                @if($groupedItems->count() > 0)
                <!-- GRAND TOTAL -->
                <tr style="background-color: #002060; color: white; font-weight: bold; font-size: 11px;">
                    <td colspan="6" style="text-align: right;">GRAND TOTAL:</td>
                    <td class="amount-cell">{{ number_format($grandTotalRevenue, 2) }}</td>
                    <td colspan="19"></td>
                    <td class="amount-cell">{{ number_format($grandTotalBudget, 2) }}</td>
                    <td class="amount-cell {{ ($grandTotalRevenue - $grandTotalBudget) >= 0 ? 'profit' : 'loss' }}">
                        {{ number_format($grandTotalRevenue - $grandTotalBudget, 2) }}
                    </td>
                    <td class="{{ ($grandTotalRevenue - $grandTotalBudget) >= 0 ? 'profit' : 'loss' }}">
                        {{ $grandTotalRevenue > 0 ? number_format(($grandTotalRevenue - $grandTotalBudget)/$grandTotalRevenue*100, 1) : 0 }}%
                    </td>
                    <td>
                        <span class="badge {{ ($grandTotalRevenue - $grandTotalBudget) >= 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ ($grandTotalRevenue - $grandTotalBudget) >= 0 ? 'PROFIT' : 'LOSS' }}
                        </span>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
