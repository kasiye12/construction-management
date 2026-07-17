@extends('layouts.app')

@section('title', '30 Column Budget Report - CMS')

@push('styles')
<style>
    .report-container { overflow-x: auto; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
    .report-table { font-size: 9px; width: 100%; border-collapse: collapse; white-space: nowrap; min-width: 2500px; }
    .report-table th, .report-table td { border: 1px solid #d1d5db; padding: 4px 5px; text-align: center; vertical-align: middle; }
    .report-table thead th { background: #4472C4; color: white; font-weight: 600; font-size: 7px; text-transform: uppercase; }
    .report-table .category-header td { background: #D6E4F0; font-weight: 700; text-align: left; font-size: 10px; color: #1a237e; }
    .report-table .item-row td { background: #fff; font-size: 8px; }
    .report-table .sub-row td { background: #f9fafb; font-size: 7px; color: #666; }
    .report-table .total-row td { background: #E2EFDA; font-weight: 700; font-size: 8px; }
    .report-table .grand-total td { background: #002060; color: white; font-weight: 700; font-size: 10px; }
    .report-table .profit { color: #059669; font-weight: 700; }
    .report-table .loss { color: #dc2626; font-weight: 700; }
    .report-table .amount-cell { text-align: right; }
    .filter-bar { background: white; padding: 16px; border-radius: 8px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
    .filter-bar .form-label { font-weight: 600; font-size: 0.7rem; color: #4b5563; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
    @media print {
        body { background: white !important; }
        .sidebar, .topbar, .btn, .filter-bar, .no-print { display: none !important; }
        .main-content { margin: 0 !important; padding: 3px !important; }
        .report-container { box-shadow: none !important; border: 1px solid #000 !important; }
        .report-table thead th { background: #4472C4 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        @page { size: A2 landscape; margin: 8mm; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h2>📊 30-Column Budget & Cost Breakdown Report</h2>
            <p class="text-muted small mb-0">
                @if($selectedProject) Project: <strong>{{ $selectedProject->name }}</strong> @else All Projects @endif
                @if(request('category_id')) | Category Filtered @endif
                @if(request('status')) | Status: {{ ucfirst(request('status')) }} @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.30-column.excel', request()->query()) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Excel</a>
            <a href="{{ route('reports.30-column.pdf', request()->query()) }}" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> PDF</a>
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm"><i class="fas fa-print me-1"></i> Print</button>
        </div>
    </div>

    <!-- FILTERS -->
    <div class="filter-bar no-print">
        <form method="GET" action="{{ route('reports.30-column') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">📁 Project</label>
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ Str::limit($p->name, 25) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">📂 Cost Category</label>
                <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @if($selectedProject)
                        @foreach($selectedProject->costCategories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->code ?? '' }} {{ $cat->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">📊 Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>In Progress</option>
                    <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">📅 Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2">
                <label class="form-label">📅 Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-1">
                <a href="{{ route('reports.30-column') }}" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-times"></i> Clear</a>
            </div>
        </form>
    </div>

    <!-- REPORT TABLE -->
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
                    <th colspan="5" style="background:#5B9BD5;">LABOUR</th>
                    <th colspan="4" style="background:#ED7D31;">MATERIAL</th>
                    <th colspan="6" style="background:#A5A5A5;">EQUIPMENT</th>
                    <th rowspan="2">TOTAL<br>BUDGET<br>AMOUNT</th>
                    <th rowspan="2">PROFIT/<br>LOSS</th>
                    <th rowspan="2">Profit<br>%</th>
                    <th rowspan="2">STATUS</th>
                </tr>
                <tr>
                    <th>TRADE</th><th>NUMBER</th><th>TOTAL<br>HOUR</th><th>WAGE/<br>DAY</th><th>AMOUNT</th>
                    <th>Description</th><th>UNIT</th><th>QUANTITY</th><th>UNIT<br>RATE</th>
                    <th>DESCRIPTION</th><th>DURATION</th><th>NUMBER</th><th>TOTAL<br>HOUR</th><th>RATE</th><th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @php $grandRevenue = 0; $grandBudget = 0; @endphp

                @forelse($groupedItems as $categoryName => $categoryItems)
                    @php
                        $catRevenue = $categoryItems->sum('revenue_amount');
                        $catBudget = $categoryItems->sum(fn($i) => $i->total_budget_cost);
                        $grandRevenue += $catRevenue; $grandBudget += $catBudget;
                    @endphp
                    
                    <tr class="category-header">
                        <td colspan="29">
                            <strong>{{ $categoryName }}</strong>
                            <span style="float:right;font-weight:400;">
                                Revenue: {{ number_format($catRevenue,2) }} | Budget: {{ number_format($catBudget,2) }}
                            </span>
                        </td>
                    </tr>
                    
                    @foreach($categoryItems as $item)
                        @php
                            $labor = $item->laborResources;
                            $material = $item->materialResources;
                            $equipment = $item->equipmentResources;
                            $maxRows = max($labor->count(), $material->count(), $equipment->count(), 1);
                        @endphp
                        
                        @for($i = 0; $i < $maxRows; $i++)
                            <tr class="{{ $i == 0 ? 'item-row' : 'sub-row' }}">
                                @if($i == 0)
                                    <td rowspan="{{ $maxRows }}"><strong>{{ $item->costCategory->code ?? '' }}</strong></td>
                                    <td rowspan="{{ $maxRows }}">{{ $item->item_number }}</td>
                                    <td rowspan="{{ $maxRows }}" style="text-align:left;">{{ $item->description }}</td>
                                    <td rowspan="{{ $maxRows }}">{{ $item->unit }}</td>
                                    <td rowspan="{{ $maxRows }}" class="amount-cell">{{ number_format($item->unit_rate,2) }}</td>
                                    <td rowspan="{{ $maxRows }}" class="amount-cell">{{ number_format($item->quantity,2) }}</td>
                                    <td rowspan="{{ $maxRows }}" class="amount-cell">{{ number_format($item->revenue_amount,2) }}</td>
                                    <td rowspan="{{ $maxRows }}">{{ $item->duration_days ?? '-' }}</td>
                                    <td rowspan="{{ $maxRows }}">{{ $item->planned_start_date ? $item->planned_start_date->format('m/d/Y') : '-' }}</td>
                                    <td rowspan="{{ $maxRows }}">{{ $item->planned_end_date ? $item->planned_end_date->format('m/d/Y') : '-' }}</td>
                                @endif
                                
                                {{-- LABOUR --}}
                                @if(isset($labor[$i]))
                                    <td style="text-align:left;">{{ $labor[$i]->trade_name }}</td>
                                    <td class="amount-cell">{{ number_format($labor[$i]->number_of_workers,2) }}</td>
                                    <td class="amount-cell">{{ number_format($labor[$i]->total_hours,2) }}</td>
                                    <td class="amount-cell">{{ number_format($labor[$i]->wage_per_day,2) }}</td>
                                    <td class="amount-cell">{{ number_format($labor[$i]->amount,2) }}</td>
                                @else<td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>@endif
                                
                                {{-- MATERIAL --}}
                                @if(isset($material[$i]))
                                    <td style="text-align:left;">{{ $material[$i]->description }}</td>
                                    <td>{{ $material[$i]->unit }}</td>
                                    <td class="amount-cell">{{ number_format($material[$i]->quantity,2) }}</td>
                                    <td class="amount-cell">{{ number_format($material[$i]->unit_rate,2) }}</td>
                                @else<td>-</td><td>-</td><td>-</td><td>-</td>@endif
                                
                                {{-- EQUIPMENT --}}
                                @if(isset($equipment[$i]))
                                    <td style="text-align:left;">{{ $equipment[$i]->description }}</td>
                                    <td class="amount-cell">{{ number_format($equipment[$i]->duration_days,2) }}</td>
                                    <td>{{ $equipment[$i]->number_of_units }}</td>
                                    <td class="amount-cell">{{ number_format($equipment[$i]->total_hours,2) }}</td>
                                    <td class="amount-cell">{{ number_format($equipment[$i]->rate_per_hour,2) }}</td>
                                    <td class="amount-cell">{{ number_format($equipment[$i]->amount,2) }}</td>
                                @else<td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>@endif
                                
                                @if($i == 0)
                                    <td rowspan="{{ $maxRows }}" class="amount-cell"><strong>{{ number_format($item->total_budget_cost,2) }}</strong></td>
                                    <td rowspan="{{ $maxRows }}" class="amount-cell {{ $item->profit_loss>=0?'profit':'loss' }}"><strong>{{ number_format($item->profit_loss,2) }}</strong></td>
                                    <td rowspan="{{ $maxRows }}" class="{{ $item->profit_margin_percentage>=0?'profit':'loss' }}">{{ number_format($item->profit_margin_percentage,1) }}%</td>
                                    <td rowspan="{{ $maxRows }}"><span class="badge {{ $item->profit_loss_status=='PROFIT'?'bg-success':'bg-danger' }}">{{ $item->profit_loss_status }}</span></td>
                                @endif
                            </tr>
                        @endfor
                    @endforeach
                    
                    <tr class="total-row">
                        <td colspan="6" style="text-align:right;">Subtotal - {{ $categoryName }}:</td>
                        <td class="amount-cell">{{ number_format($catRevenue,2) }}</td>
                        <td colspan="18"></td>
                        <td class="amount-cell">{{ number_format($catBudget,2) }}</td>
                        <td class="amount-cell {{ ($catRevenue-$catBudget)>=0?'profit':'loss' }}">{{ number_format($catRevenue-$catBudget,2) }}</td>
                        <td></td><td></td>
                    </tr>
                @empty
                    <tr><td colspan="29" style="text-align:center;padding:40px;"><h5>No data found</h5></td></tr>
                @endforelse
                
                @if($groupedItems->count() > 0)
                <tr class="grand-total">
                    <td colspan="6" style="text-align:right;">GRAND TOTAL:</td>
                    <td class="amount-cell">{{ number_format($grandRevenue,2) }}</td>
                    <td colspan="18"></td>
                    <td class="amount-cell">{{ number_format($grandBudget,2) }}</td>
                    <td class="amount-cell {{ ($grandRevenue-$grandBudget)>=0?'profit':'loss' }}">{{ number_format($grandRevenue-$grandBudget,2) }}</td>
                    <td class="{{ ($grandRevenue-$grandBudget)>=0?'profit':'loss' }}">{{ $grandRevenue>0?number_format(($grandRevenue-$grandBudget)/$grandRevenue*100,1):0 }}%</td>
                    <td><span class="badge {{ ($grandRevenue-$grandBudget)>=0?'bg-success':'bg-danger' }}">{{ ($grandRevenue-$grandBudget)>=0?'PROFIT':'LOSS' }}</span></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
