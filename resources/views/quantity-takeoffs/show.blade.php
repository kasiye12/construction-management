@extends('layouts.app')

@section('title', 'Waterproofing Take-Off Sheet - CMS')

@push('styles')
<style>
    .takeoff-sheet { 
        background: white; 
        font-family: 'Segoe UI', Arial, sans-serif; 
        font-size: 9px; 
        border: 2px solid #1a237e; 
        padding: 6px 8px;
        max-width: 100%;
    }
    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    .header-table td { padding: 2px 4px; vertical-align: middle; }
    .logo-cell { width: 50px; text-align: center; }
    .logo-img { width: 44px; height: 44px; object-fit: contain; border-radius: 4px; }
    .logo-placeholder { font-size: 24px; }
    .company-name { font-size: 10px; font-weight: bold; color: #1a237e; }
    .company-sub { font-size: 7px; color: #555; }
    .title-bar { background: #1a237e; color: white; padding: 4px 8px; font-weight: bold; font-size: 10px; text-align: center; letter-spacing: 1px; }
    .info-table { width: 100%; border-collapse: collapse; margin: 3px 0; }
    .info-table td { padding: 1px 4px; font-size: 7.5px; }
    .info-label { font-weight: bold; width: 90px; font-size: 7px; }
    .data-table { width: 100%; border-collapse: collapse; margin: 4px 0; }
    .data-table th, .data-table td { border: 0.5px solid #666; padding: 2px 3px; text-align: center; font-size: 7px; vertical-align: middle; }
    .data-table th { background: #e8eaf6; font-weight: bold; font-size: 6.5px; }
    .data-table .text-left { text-align: left; }
    .data-table .text-right { text-align: right; }
    .data-table .section-main td { background: #f0f4ff; font-weight: bold; font-size: 8px; text-align: left; }
    .data-table .section-sub td { background: #f8fafc; font-weight: bold; font-size: 7px; text-align: left; font-style: italic; }
    .data-table .element-row td { background: #fff; font-weight: bold; font-size: 7px; }
    .data-table .calc-row td { font-size: 7px; }
    .data-table .total-section td { background: #e8f5e9; font-weight: bold; font-size: 8px; }
    .signatures { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .signatures td { text-align: center; padding: 3px; width: 33%; }
    .sig-box { border: 1px solid #ddd; padding: 6px 4px; min-height: 30px; }
    .sig-name { font-size: 8px; color: #1a237e; font-weight: bold; min-height: 10px; }
    .sig-line { border-top: 1px solid #333; padding-top: 3px; margin: 0 10px; font-size: 7px; font-weight: bold; }
    .sig-date { font-size: 6px; color: #666; }
    .footer { text-align: center; font-size: 6px; color: #999; margin-top: 6px; border-top: 1px solid #ddd; padding-top: 3px; }
    
    .print-btn { position: fixed; top: 10px; right: 10px; background: #1a237e; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-size: 12px; font-weight: 600; z-index: 1000; }
    @media print { 
        .print-btn, .no-print, .sidebar, .topbar, .breadcrumb, .btn { display: none !important; } 
        .main-content { margin: 0 !important; padding: 3px !important; }
        @page { size: A4 landscape; margin: 5mm; }
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
            <button onclick="window.print()" class="btn btn-outline-dark"><i class="fas fa-print me-1"></i> Print</button>
            @if($quantityTakeoff->status == 'draft')
            <form action="{{ route('quantity-takeoffs.verify', $quantityTakeoff) }}" method="POST" class="d-inline">@csrf<button class="btn btn-warning btn-sm">✅ Verify</button></form>
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
        $companyTagline = \App\Models\CompanySetting::get('company_tagline', 'General Contractor & Engineering Services');
    @endphp
    
    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($logoUrl)<img src="{{ $logoUrl }}" class="logo-img" alt="Logo">@else<span class="logo-placeholder">🏗️</span>@endif
            </td>
            <td width="55%" style="text-align:center;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-sub">{{ $companyTagline }}</div>
            </td>
            <td width="30%" style="text-align:right;font-size:7px;">
                <strong>Document No:</strong> T.O-{{ str_pad($quantityTakeoff->id, 3, '0', STR_PAD_LEFT) }}<br>
                <strong>Issue:</strong> 1 &nbsp; <strong>Page:</strong> 1/1
            </td>
        </tr>
    </table>

    <!-- TITLE -->
    <div class="title-bar">TAKE OFF SHEET - WATERPROOFING</div>

    <!-- INFO -->
    <table class="info-table">
        <tr>
            <td class="info-label">LOCATION:</td>
            <td>{{ $quantityTakeoff->location_axis ?? 'ADDIS ABABA' }}</td>
            <td class="info-label" width="60px">DATE:</td>
            <td width="100px">{{ $quantityTakeoff->measurement_date->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">CONTRACTOR:</td>
            <td>{{ $companyName }}</td>
            <td class="info-label">SHEET NO:</td>
            <td>T.O-{{ str_pad($quantityTakeoff->id, 3, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td class="info-label">SUB-CONTRACTOR:</td>
            <td><strong>AMARE WATER PROOFING PLC</strong></td>
            <td class="info-label">PAGE NO:</td>
            <td>1 of 1</td>
        </tr>
    </table>

    <!-- MAIN DATA TABLE -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="6%">Qty</th>
                <th width="10%">Size (L×W×H)</th>
                <th width="10%">Product</th>
                <th width="35%">Description</th>
                <th width="4%">No</th>
                <th width="6%">Qty</th>
                <th width="10%">Size (L×W×H)</th>
                <th width="15%">Product</th>
            </tr>
        </thead>
        <tbody>
            <!-- Section Header -->
            <tr class="section-main"><td colspan="9">BITUMINOUS DAMP PROOFING</td></tr>
            <tr class="section-sub"><td colspan="9">a) For Foundation Footing - on Foundation Footing Pad</td></tr>
            
            @php
                // Get all takeoff records for this BOQ item
                $allTakeoffs = \App\Models\QuantityTakeoff::where('boq_item_id', $quantityTakeoff->boq_item_id)
                    ->where('project_id', $quantityTakeoff->project_id)
                    ->orderBy('element_id')
                    ->get();
                
                $grandTotal = 0;
                $halfCount = ceil($allTakeoffs->count() / 2);
            @endphp
            
            @if($allTakeoffs->count() > 0)
                @foreach($allTakeoffs as $index => $tf)
                    @php
                        $area = $tf->total_area_volume;
                        $grandTotal += $area;
                        $leftIndex = $index;
                        $rightIndex = $index + $halfCount;
                    @endphp
                    
                    <tr>
                        {{-- LEFT SIDE --}}
                        @if($leftIndex < $halfCount)
                            @php $ltf = $allTakeoffs[$leftIndex]; @endphp
                            <td class="element-row">{{ $ltf->element_id }}</td>
                            <td class="text-right">{{ $ltf->quantity_count }}</td>
                            <td class="text-right">{{ number_format($ltf->length, 2) }}</td>
                            <td></td>
                            <td class="text-left"></td>
                        @else
                            <td></td><td></td><td></td><td></td><td></td>
                        @endif
                        
                        {{-- RIGHT SIDE --}}
                        @if($rightIndex < $allTakeoffs->count())
                            @php $rtf = $allTakeoffs[$rightIndex]; @endphp
                            <td class="element-row">{{ $rtf->element_id }}</td>
                            <td class="text-right">{{ $rtf->quantity_count }}</td>
                            <td class="text-right">{{ number_format($rtf->length, 2) }}</td>
                            <td></td>
                        @else
                            <td></td><td></td><td></td><td></td>
                        @endif
                    </tr>
                    
                    {{-- Second row for area calculation --}}
                    <tr class="calc-row">
                        @if($leftIndex < $halfCount)
                            @php $ltf = $allTakeoffs[$leftIndex]; @endphp
                            <td></td><td></td>
                            <td class="text-right">{{ number_format($ltf->length, 2) }}</td>
                            <td></td><td></td>
                        @else
                            <td></td><td></td><td></td><td></td><td></td>
                        @endif
                        
                        @if($rightIndex < $allTakeoffs->count())
                            @php $rtf = $allTakeoffs[$rightIndex]; @endphp
                            <td></td><td></td>
                            <td class="text-right">{{ number_format($rtf->length, 2) }}</td>
                            <td></td>
                        @else
                            <td></td><td></td><td></td><td></td>
                        @endif
                    </tr>
                    
                    {{-- Area row --}}
                    <tr class="calc-row">
                        @if($leftIndex < $halfCount)
                            @php $ltf = $allTakeoffs[$leftIndex]; @endphp
                            <td></td><td></td><td></td>
                            <td class="text-right"><strong>{{ number_format($ltf->total_area_volume, 2) }}</strong></td>
                            <td></td>
                        @else
                            <td></td><td></td><td></td><td></td><td></td>
                        @endif
                        
                        @if($rightIndex < $allTakeoffs->count())
                            @php $rtf = $allTakeoffs[$rightIndex]; @endphp
                            <td></td><td></td><td></td>
                            <td class="text-right"><strong>{{ number_format($rtf->total_area_volume, 2) }}</strong></td>
                        @else
                            <td></td><td></td><td></td><td></td>
                        @endif
                    </tr>
                    
                    {{-- Empty row for spacing --}}
                    <tr class="calc-row">
                        <td></td><td></td><td></td><td></td><td></td>
                        <td></td><td></td><td></td><td></td>
                    </tr>
                @endforeach
            @else
                {{-- Show single record if no grouped data --}}
                <tr class="element-row">
                    <td>{{ $quantityTakeoff->element_id ?? 'F1' }}</td>
                    <td class="text-right">{{ $quantityTakeoff->quantity_count }}</td>
                    <td class="text-right">{{ number_format($quantityTakeoff->length, 2) }}</td>
                    <td></td><td></td>
                    <td></td><td></td><td></td><td></td>
                </tr>
                <tr class="calc-row">
                    <td></td><td></td>
                    <td class="text-right">{{ number_format($quantityTakeoff->length, 2) }}</td>
                    <td></td><td></td>
                    <td></td><td></td><td></td><td></td>
                </tr>
                <tr class="calc-row">
                    <td></td><td></td><td></td>
                    <td class="text-right"><strong>{{ number_format($quantityTakeoff->total_area_volume, 2) }}</strong></td>
                    <td></td>
                    <td></td><td></td><td></td><td></td>
                </tr>
            @endif
            
            <!-- Grand Total -->
            <tr class="total-section">
                <td colspan="4" class="text-right"><strong>TOTAL WATERPROOFING AREA:</strong></td>
                <td class="text-right"><strong>{{ number_format($grandTotal > 0 ? $grandTotal : $quantityTakeoff->total_area_volume, 2) }} m²</strong></td>
                <td colspan="4"></td>
            </tr>
        </tbody>
    </table>

    <!-- SIGNATURES -->
    <table class="signatures">
        <tr>
            <td>
                <div class="sig-box">
                    @if($quantityTakeoff->measured_by)<div class="sig-name">{{ $quantityTakeoff->measured_by }}</div>@endif
                    <div class="sig-line">Measured By</div>
                    <div class="sig-date">{{ $quantityTakeoff->measurement_date->format('d-m-Y') }}</div>
                </div>
            </td>
            <td>
                <div class="sig-box">
                    @if($quantityTakeoff->verified_by)<div class="sig-name">{{ $quantityTakeoff->verified_by }}</div>@endif
                    <div class="sig-line">Verified By</div>
                    @if($quantityTakeoff->status == 'verified')<div class="sig-date" style="color:green;">✅ Verified</div>@endif
                </div>
            </td>
            <td>
                <div class="sig-box">
                    <div class="sig-line">Approved By</div>
                    @if($quantityTakeoff->status == 'approved')<div class="sig-date" style="color:green;">✅ Approved</div>@endif
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        {{ $companyName }} | Generated by CMS | {{ date('d-m-Y H:i') }}
    </div>
</div>
@endsection
