@extends('layouts.app')

@section('title', '30 Column Budget Report - CMS')

@push('styles')
<style>
    .report-container { overflow-x: auto; background: white; border-radius: 8px; border: 1px solid #e5e7eb; }
    .report-table { font-size: 8px; width: 100%; border-collapse: collapse; white-space: nowrap; min-width: 2800px; }
    .report-table th, .report-table td { border: 0.5px solid #999; padding: 3px 4px; text-align: center; vertical-align: middle; }
    .report-table thead th { background: #4472C4; color: white; font-weight: 600; font-size: 7px; }
    .report-table .category-header td { background: #D6E4F0; font-weight: 700; text-align: left; font-size: 9px; }
    .report-table .item-row td { background: #fff; font-size: 8px; }
    .report-table .sub-row td { background: #f9fafb; font-size: 7px; color: #666; }
    .report-table .total-row td { background: #E2EFDA; font-weight: 700; font-size: 8px; }
    .report-table .grand-total td { background: #002060; color: white; font-weight: 700; font-size: 9px; }
    .report-table .profit { color: #006100; font-weight: 700; }
    .report-table .loss { color: #9C0006; font-weight: 700; }
    .report-table .amount-cell { text-align: right; }
    .report-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding: 10px; background: white; border-radius: 8px; border: 1px solid #e5e7eb; }
    .report-logo { width: 55px; height: 55px; border-radius: 6px; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
    .report-logo img { width: 100%; height: 100%; object-fit: contain; }
    .report-logo .logo-placeholder { font-size: 28px; }
    .report-company { flex: 1; }
    .report-company h4 { font-size: 12px; font-weight: 700; color: #1a237e; margin: 0; }
    .report-company p { font-size: 8px; color: #555; margin: 1px 0; }
    .filter-bar { background: white; padding: 14px; border-radius: 8px; margin-bottom: 14px; border: 1px solid #e5e7eb; }
    @media print {
        body { background: white !important; }
        .sidebar, .topbar, .btn, .filter-bar, .no-print { display: none !important; }
        .main-content { margin: 0 !important; padding: 3px !important; }
        @page { size: A2 landscape; margin: 8mm; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div><h2>📊 30-Column Budget & Cost Breakdown Report</h2></div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.30-column.excel', request()->query()) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Excel</a>
            <a href="{{ route('reports.30-column.pdf', request()->query()) }}" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> PDF</a>
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm"><i class="fas fa-print me-1"></i> Print</button>
        </div>
    </div>

    <!-- DYNAMIC COMPANY HEADER -->
    <div class="report-header">
        @php $logoUrl = \App\Models\CompanySetting::getLogoUrl(); @endphp
        <div class="report-logo">
            @if($logoUrl)<img src="{{ $logoUrl }}" alt="Logo">@else<span class="logo-placeholder">🏗️</span>@endif
        </div>
        <div class="report-company">
            <h4>{{ \App\Models\CompanySetting::get('company_name', 'TNT Construction and Trading') }}</h4>
            <p>{{ \App\Models\CompanySetting::get('company_tagline', 'General Contractor & Engineering Services') }}</p>
            <p>📞 {{ \App\Models\CompanySetting::get('company_phone', '+251-000-000000') }} | 📧 {{ \App\Models\CompanySetting::get('company_email', 'info@tnt-constructions.com') }} | 📍 {{ \App\Models\CompanySetting::get('company_address', 'Addis Ababa, Ethiopia') }}</p>
        </div>
    </div>

    <!-- Rest of the report table... -->
    <div class="filter-bar no-print">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2"><select name="project_id" class="form-select form-select-sm"><option value="">All Projects</option>@foreach($projects as $p)<option value="{{ $p->id }}" {{ request('project_id')==$p->id?'selected':'' }}>{{ Str::limit($p->name,25) }}</option>@endforeach</select></div>
            <div class="col-md-2"><select name="category_id" class="form-select form-select-sm"><option value="">All Categories</option>@if($selectedProject)@foreach($selectedProject->costCategories as $cat)<option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->code??'' }} {{ $cat->name }}</option>@endforeach @endif</select></div>
            <div class="col-md-1"><select name="status" class="form-select form-select-sm"><option value="">All</option><option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option><option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>In Progress</option><option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option></select></div>
            <div class="col-md-2"><input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}"></div>
            <div class="col-md-2"><input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}"></div>
            <div class="col-md-1"><button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter"></i></button></div>
            @if(request()->anyFilled(['project_id','category_id','status','date_from','date_to']))<div class="col-md-1"><a href="{{ route('reports.30-column') }}" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-times"></i></a></div>@endif
        </form>
    </div>

    <div class="report-container">
        <table class="report-table">
            <thead>
                <tr>
                    <th rowspan="2">Cost<br>Category</th><th rowspan="2">Item<br>No.</th><th rowspan="2">ITEM DESCRIPTION</th><th rowspan="2">UNIT</th><th rowspan="2">BOQ<br>Rate</th><th rowspan="2">Quantity</th><th rowspan="2">REVENUE<br>AMOUNT</th><th rowspan="2">Duration</th><th rowspan="2">Start<br>date</th><th rowspan="2">End<br>Date</th>
                    <th colspan="5" style="background:#5B9BD5;">LABOUR</th><th colspan="4" style="background:#ED7D31;">MATERIAL</th><th colspan="6" style="background:#A5A5A5;">EQUIPMENT</th>
                    <th rowspan="2">TOTAL<br>BUDGET<br>AMOUNT</th><th rowspan="2">PROFIT/<br>LOSS</th><th rowspan="2">Profit<br>%</th><th rowspan="2">STATUS</th>
                </tr>
                <tr>
                    <th>TRADE</th><th>NUMBER</th><th>TOTAL<br>HOUR</th><th>WAGE/<br>DAY</th><th>AMOUNT</th>
                    <th>Description</th><th>UNIT</th><th>QUANTITY</th><th>UNIT<br>RATE</th>
                    <th>DESCRIPTION</th><th>DURATION</th><th>NUMBER</th><th>TOTAL<br>HOUR</th><th>RATE</th><th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @php $gr = 0; $gb = 0; @endphp
                @forelse($groupedItems as $cat => $items)
                    @php $cr = $items->sum('revenue_amount'); $cb = $items->sum(fn($i)=>$i->total_budget_cost); $gr+=$cr; $gb+=$cb; @endphp
                    <tr class="category-header"><td colspan="29">{{ $cat }} <span style="float:right;font-weight:400;">Rev: {{ number_format($cr,2) }} | Bud: {{ number_format($cb,2) }}</span></td></tr>
                    @foreach($items as $item)
                        @php $l=$item->laborResources; $m=$item->materialResources; $e=$item->equipmentResources; $mx=max($l->count(),$m->count(),$e->count(),1); @endphp
                        @for($i=0;$i<$mx;$i++)
                            <tr class="{{ $i==0?'item-row':'sub-row' }}">
                                @if($i==0)<td rowspan="{{ $mx }}"><strong>{{ $item->costCategory->code??'' }}</strong></td><td rowspan="{{ $mx }}">{{ $item->item_number }}</td><td rowspan="{{ $mx }}" style="text-align:left;">{{ $item->description }}</td><td rowspan="{{ $mx }}">{{ $item->unit }}</td><td rowspan="{{ $mx }}" class="amount-cell">{{ number_format($item->unit_rate,2) }}</td><td rowspan="{{ $mx }}" class="amount-cell">{{ number_format($item->quantity,2) }}</td><td rowspan="{{ $mx }}" class="amount-cell">{{ number_format($item->revenue_amount,2) }}</td><td rowspan="{{ $mx }}">{{ $item->duration_days??'-' }}</td><td rowspan="{{ $mx }}">{{ $item->planned_start_date?$item->planned_start_date->format('m/d/Y'):'-' }}</td><td rowspan="{{ $mx }}">{{ $item->planned_end_date?$item->planned_end_date->format('m/d/Y'):'-' }}</td>@endif
                                @if(isset($l[$i]))<td style="text-align:left;">{{ $l[$i]->trade_name }}</td><td class="amount-cell">{{ number_format($l[$i]->number_of_workers,2) }}</td><td class="amount-cell">{{ number_format($l[$i]->total_hours,2) }}</td><td class="amount-cell">{{ number_format($l[$i]->wage_per_day,2) }}</td><td class="amount-cell">{{ number_format($l[$i]->amount,2) }}</td>@else<td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>@endif
                                @if(isset($m[$i]))<td style="text-align:left;">{{ $m[$i]->description }}</td><td>{{ $m[$i]->unit }}</td><td class="amount-cell">{{ number_format($m[$i]->quantity,2) }}</td><td class="amount-cell">{{ number_format($m[$i]->unit_rate,2) }}</td>@else<td>-</td><td>-</td><td>-</td><td>-</td>@endif
                                @if(isset($e[$i]))<td style="text-align:left;">{{ $e[$i]->description }}</td><td class="amount-cell">{{ number_format($e[$i]->duration_days,2) }}</td><td>{{ $e[$i]->number_of_units }}</td><td class="amount-cell">{{ number_format($e[$i]->total_hours,2) }}</td><td class="amount-cell">{{ number_format($e[$i]->rate_per_hour,2) }}</td><td class="amount-cell">{{ number_format($e[$i]->amount,2) }}</td>@else<td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>@endif
                                @if($i==0)<td rowspan="{{ $mx }}" class="amount-cell"><strong>{{ number_format($item->total_budget_cost,2) }}</strong></td><td rowspan="{{ $mx }}" class="amount-cell {{ $item->profit_loss>=0?'profit':'loss' }}"><strong>{{ number_format($item->profit_loss,2) }}</strong></td><td rowspan="{{ $mx }}" class="{{ $item->profit_margin_percentage>=0?'profit':'loss' }}">{{ number_format($item->profit_margin_percentage,1) }}%</td><td rowspan="{{ $mx }}"><span class="badge {{ $item->profit_loss_status=='PROFIT'?'bg-success':'bg-danger' }}">{{ $item->profit_loss_status }}</span></td>@endif
                            </tr>
                        @endfor
                    @endforeach
                    <tr class="total-row"><td colspan="6" style="text-align:right;">Subtotal:</td><td class="amount-cell">{{ number_format($cr,2) }}</td><td colspan="18"></td><td class="amount-cell">{{ number_format($cb,2) }}</td><td class="amount-cell {{ ($cr-$cb)>=0?'profit':'loss' }}">{{ number_format($cr-$cb,2) }}</td><td></td><td></td></tr>
                @empty
                    <tr><td colspan="29" style="text-align:center;padding:40px;"><h5>No data found</h5></td></tr>
                @endforelse
                @if($groupedItems->count()>0)
                <tr class="grand-total"><td colspan="6" style="text-align:right;">GRAND TOTAL:</td><td class="amount-cell">{{ number_format($gr,2) }}</td><td colspan="18"></td><td class="amount-cell">{{ number_format($gb,2) }}</td><td class="amount-cell {{ ($gr-$gb)>=0?'profit':'loss' }}">{{ number_format($gr-$gb,2) }}</td><td class="{{ ($gr-$gb)>=0?'profit':'loss' }}">{{ $gr>0?number_format(($gr-$gb)/$gr*100,1):0 }}%</td><td><span class="badge {{ ($gr-$gb)>=0?'bg-success':'bg-danger' }}">{{ ($gr-$gb)>=0?'PROFIT':'LOSS' }}</span></td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
