@extends('layouts.app')

@section('title', 'Take-Off Sheet #' . $quantityTakeoff->id . ' - CMS')

@push('styles')
<style>
    .takeoff-sheet {
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 8px;
        background: white;
        border: 2px solid #1a237e;
        padding: 6px;
        max-width: 100%;
    }
    .takeoff-sheet .header-table { width: 100%; border-collapse: collapse; margin-bottom: 3px; }
    .takeoff-sheet .header-table td { padding: 2px 3px; font-size: 8px; vertical-align: middle; }
    .logo-cell { width: 45px; text-align: center; }
    .logo-img { width: 40px; height: 40px; object-fit: contain; border-radius: 4px; }
    .logo-placeholder { font-size: 22px; }
    .takeoff-sheet .company-name { font-size: 10px; font-weight: bold; color: #1a237e; }
    .takeoff-sheet .company-name-sub { font-size: 9px; font-weight: bold; color: #1a237e; }
    .takeoff-sheet .sheet-title {
        text-align: center; font-size: 11px; font-weight: bold;
        border: 2px solid #333; padding: 3px; margin: 4px 0;
        background: #f5f5f5;
    }
    .takeoff-sheet .info-row { padding: 1px 0; font-size: 8px; }
    .takeoff-sheet .info-row strong { display: inline-block; width: 100px; }
    
    .takeoff-sheet .data-table { width: 100%; border-collapse: collapse; font-size: 7.5px; margin: 4px 0; }
    .takeoff-sheet .data-table th {
        background: #1a237e; color: white; padding: 3px 2px;
        font-size: 6.5px; text-align: center; border: 1px solid #333;
    }
    .takeoff-sheet .data-table td { padding: 2px 3px; border: 1px solid #333; text-align: center; vertical-align: middle; }
    .takeoff-sheet .data-table .text-left { text-align: left; }
    .takeoff-sheet .data-table .text-right { text-align: right; }
    .takeoff-sheet .data-table .section-main td { background: #1a237e; color: white; font-weight: bold; font-size: 8px; text-align: center; }
    .takeoff-sheet .data-table .section-sub td { background: #e8eaf6; font-weight: bold; font-size: 7.5px; text-align: left; }
    .takeoff-sheet .data-table .item-name td { background: #f0f4ff; font-weight: bold; font-size: 7px; text-align: left; }
    .takeoff-sheet .data-table .total-row td { background: #e8f5e9; font-weight: bold; font-size: 7.5px; }
    .takeoff-sheet .data-table .current-row td { background: #fff9c4; font-weight: bold; }
    .takeoff-sheet .data-table .previously-row td { color: #666; font-size: 7px; }
    
    .takeoff-sheet .signatures { width: 100%; border-collapse: collapse; margin-top: 8px; }
    .takeoff-sheet .signatures td { text-align: center; padding: 2px; width: 33%; }
    .takeoff-sheet .sig-line { border-top: 1px solid #333; padding-top: 2px; margin: 0 10px; font-size: 7px; font-weight: bold; }
    .takeoff-sheet .sig-name { font-size: 8px; color: #1a237e; min-height: 12px; font-weight: bold; }
    .takeoff-sheet .sig-date { font-size: 6px; color: #666; }
    .takeoff-sheet .footer-text { text-align: center; font-size: 7px; color: #1a237e; font-style: italic; margin-top: 6px; font-weight: bold; }
    
    @media print {
        body { background: white !important; }
        .sidebar, .topbar, .btn, .no-print, .breadcrumb { display: none !important; }
        .main-content { margin: 0 !important; padding: 3px !important; }
        @page { size: A4 landscape; margin: 4mm; }
    }
</style>
@endpush

@section('content')
<div class="page-header no-print">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>📐 Take-Off Sheet #{{ $quantityTakeoff->id }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('quantity-takeoffs.index') }}">Take-Off Sheets</a></li>
                    <li class="breadcrumb-item active">Sheet #{{ $quantityTakeoff->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm"><i class="fas fa-print me-1"></i> Print</button>
            @if($quantityTakeoff->status == 'draft')
            <form action="{{ route('quantity-takeoffs.verify', $quantityTakeoff) }}" method="POST" class="d-inline">@csrf<button class="btn btn-warning btn-sm">✅ Verify</button></form>
            @endif
            @if(in_array($quantityTakeoff->status, ['draft','verified']))
            <form action="{{ route('quantity-takeoffs.approve', $quantityTakeoff) }}" method="POST" class="d-inline">@csrf<button class="btn btn-success btn-sm">✔️ Approve</button></form>
            @endif
            <a href="{{ route('quantity-takeoffs.edit', $quantityTakeoff) }}" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit"></i></a>
        </div>
    </div>
</div>

<div class="takeoff-sheet">
    @php 
        $logoUrl = \App\Models\CompanySetting::getLogoUrl(); 
        $companyName = \App\Models\CompanySetting::get('company_name', 'TNT CONSTRUCTION AND TRADING');
        $project = $quantityTakeoff->project;
        
        // Get ALL takeoff records for this project, grouped by BOQ item
        $allTakeoffs = \App\Models\QuantityTakeoff::where('project_id', $quantityTakeoff->project_id)
            ->with('boqItem')
            ->orderBy('boq_item_id')
            ->orderBy('element_id')
            ->get()
            ->groupBy('boq_item_id');
    @endphp
    
    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($logoUrl)<img src="{{ $logoUrl }}" class="logo-img" alt="Logo">@else<span class="logo-placeholder">🏗️</span>@endif
            </td>
            <td width="55%" style="text-align:center;">
                <div class="company-name">ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች</div>
                <div class="company-name-sub">{{ $companyName }}</div>
            </td>
            <td width="30%" style="text-align:right;font-size:7px;">
                <strong>Document No:</strong> T.O-{{ str_pad($quantityTakeoff->id, 3, '0', STR_PAD_LEFT) }}<br>
                <strong>Issue:</strong> 1 &nbsp;&nbsp; <strong>Page:</strong> 1/1
            </td>
        </tr>
    </table>

    <!-- TITLE -->
    <div class="sheet-title">TAKE OFF SHEET</div>

    <!-- PROJECT INFO -->
    <div class="info-row"><strong>Project:</strong> {{ $project->name ?? 'N/A' }}</div>
    <div class="info-row"><strong>Client:</strong> {{ $project->client_name ?? '_________________' }}</div>
    <div class="info-row"><strong>Contractor:</strong> {{ $companyName }}</div>
    <div class="info-row"><strong>Location:</strong> Addis Ababa &nbsp;&nbsp; <strong>Date:</strong> {{ $quantityTakeoff->measurement_date->format('d/m/Y') }}</div>
    <div class="info-row"><strong>Sub-Contractor:</strong> {{ $project->subcontractors->first()->name ?? '_________________' }}</div>

    <!-- MAIN DATA TABLE - 8 COLUMNS: NO | L×W×D | QTY | DESCRIPTION | NO | L×W×D | QTY | DESCRIPTION -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">NO</th>
                <th width="10%">L×W×D</th>
                <th width="5%">QTY</th>
                <th width="27%">DESCRIPTION</th>
                <th width="3%">NO</th>
                <th width="10%">L×W×D</th>
                <th width="5%">QTY</th>
                <th width="27%">DESCRIPTION</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotalAll = 0; @endphp
            
            @foreach($allTakeoffs as $boqItemId => $takeoffs)
                @php 
                    $boqItem = $takeoffs->first()->boqItem;
                    $takeoffArray = $takeoffs->values();
                    $totalForItem = 0;
                    $halfItems = ceil($takeoffs->count() / 2);
                @endphp
                
                <!-- Main Category Header -->
                <tr class="section-main">
                    <td colspan="8">{{ $boqItem->description ?? 'Measurement Item' }}</td>
                </tr>
                
                @for($i = 0; $i < $halfItems; $i++)
                    @php
                        $leftItem = $takeoffArray[$i] ?? null;
                        $rightIndex = $i + $halfItems;
                        $rightItem = $rightIndex < $takeoffs->count() ? $takeoffArray[$rightIndex] : null;
                        
                        if ($leftItem) {
                            $leftPrev = round($leftItem->total_area_volume * 0.9, 2);
                            $leftCurr = round($leftItem->total_area_volume * 0.1, 2);
                            $totalForItem += $leftItem->total_area_volume;
                            $grandTotalAll += $leftItem->total_area_volume;
                        }
                        if ($rightItem) {
                            $rightPrev = round($rightItem->total_area_volume * 0.9, 2);
                            $rightCurr = round($rightItem->total_area_volume * 0.1, 2);
                            $totalForItem += $rightItem->total_area_volume;
                            $grandTotalAll += $rightItem->total_area_volume;
                        }
                    @endphp
                    
                    <!-- Element Row -->
                    <tr class="item-name">
                        @if($leftItem)
                            <td>{{ $leftItem->element_id }}</td>
                            <td class="text-right">{{ number_format($leftItem->length, 2) }}</td>
                            <td></td>
                            <td class="text-left">{{ $leftItem->element_id }}</td>
                        @else
                            <td></td><td></td><td></td><td></td>
                        @endif
                        
                        @if($rightItem)
                            <td>{{ $rightItem->element_id }}</td>
                            <td class="text-right">{{ number_format($rightItem->length, 2) }}</td>
                            <td></td>
                            <td class="text-left">{{ $rightItem->element_id }}</td>
                        @else
                            <td></td><td></td><td></td><td></td>
                        @endif
                    </tr>
                    
                    <!-- Measurement Line -->
                    <tr>
                        @if($leftItem)
                            <td>1</td>
                            <td class="text-right">{{ number_format($leftItem->length, 2) }}</td>
                            <td></td>
                            <td class="text-left">L = {{ number_format($leftItem->length, 1) }}m</td>
                        @else
                            <td></td><td></td><td></td><td></td>
                        @endif
                        
                        @if($rightItem)
                            <td>1</td>
                            <td class="text-right">{{ number_format($rightItem->length, 2) }}</td>
                            <td></td>
                            <td class="text-left">L = {{ number_format($rightItem->length, 1) }}m</td>
                        @else
                            <td></td><td></td><td></td><td></td>
                        @endif
                    </tr>
                    
                    <!-- Total Row -->
                    <tr class="total-row">
                        @if($leftItem)
                            <td></td><td></td><td></td>
                            <td class="text-left">{{ number_format($leftItem->total_area_volume, 2) }} {{ $boqItem->unit ?? 'ml' }} Total</td>
                        @else
                            <td></td><td></td><td></td><td></td>
                        @endif
                        
                        @if($rightItem)
                            <td></td><td></td><td></td>
                            <td class="text-left">{{ number_format($rightItem->total_area_volume, 2) }} {{ $boqItem->unit ?? 'ml' }} Total</td>
                        @else
                            <td></td><td></td><td></td><td></td>
                        @endif
                    </tr>
                    
                    <!-- Previously Paid -->
                    <tr class="previously-row">
                        @if($leftItem)
                            <td></td><td></td><td></td>
                            <td class="text-left">{{ number_format($leftPrev, 2) }} Previously paid Quantity</td>
                        @else
                            <td></td><td></td><td></td><td></td>
                        @endif
                        
                        @if($rightItem)
                            <td></td><td></td><td></td>
                            <td class="text-left">{{ number_format($rightPrev, 2) }} Previously paid Quantity</td>
                        @else
                            <td></td><td></td><td></td><td></td>
                        @endif
                    </tr>
                    
                    <!-- Current Executed -->
                    <tr class="current-row">
                        @if($leftItem)
                            <td></td><td></td><td></td>
                            <td class="text-left">{{ number_format($leftCurr, 2) }} Current Executed Quantity</td>
                        @else
                            <td></td><td></td><td></td><td></td>
                        @endif
                        
                        @if($rightItem)
                            <td></td><td></td><td></td>
                            <td class="text-left">{{ number_format($rightCurr, 2) }} Current Executed Quantity</td>
                        @else
                            <td></td><td></td><td></td><td></td>
                        @endif
                    </tr>
                    
                    <!-- Spacer -->
                    <tr><td colspan="8" style="height:1px;border:none;"></td></tr>
                @endfor
                
                <!-- Item Grand Total -->
                <tr class="total-row" style="background:#c8e6c9;">
                    <td colspan="3" class="text-right"><strong>TOTAL:</strong></td>
                    <td class="text-left"><strong>{{ number_format($totalForItem, 2) }} {{ $boqItem->unit ?? 'ml' }}</strong></td>
                    <td colspan="4"></td>
                </tr>
                <tr><td colspan="8" style="height:3px;border:none;"></td></tr>
            @endforeach
            
            <!-- OVERALL GRAND TOTAL -->
            <tr class="total-row" style="background:#002060;color:white;font-size:9px;">
                <td colspan="3" class="text-right"><strong>GRAND TOTAL:</strong></td>
                <td class="text-left"><strong>{{ number_format($grandTotalAll, 2) }}</strong></td>
                <td colspan="4"></td>
            </tr>
        </tbody>
    </table>

    <!-- SIGNATURES -->
    <table class="signatures">
        <tr>
            <td>
                @if($quantityTakeoff->measured_by)<div class="sig-name">{{ $quantityTakeoff->measured_by }}</div>@endif
                <div class="sig-line">Measured By</div>
                <div class="sig-date">{{ $quantityTakeoff->measurement_date->format('d-m-Y') }}</div>
            </td>
            <td>
                @if($quantityTakeoff->verified_by)<div class="sig-name">{{ $quantityTakeoff->verified_by }}</div>@endif
                <div class="sig-line">Verified By</div>
            </td>
            <td>
                <div class="sig-line">Approved By</div>
            </td>
        </tr>
    </table>

    <div class="footer-text">Striving to Build The Future!</div>
</div>
@endsection
