<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take-Off Sheet - Print</title>
    <style>
        @page { size: A4 portrait; margin: 5mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 6.5px; color: #1a1a1a; line-height: 1.3; }
        .sheet { width: 100%; border: 2px solid #1a237e; padding: 4px 5px; }
        
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
        .header-table td { padding: 1px 3px; font-size: 7px; vertical-align: middle; }
        .logo-cell { width: 40px; text-align: center; }
        .logo-img { width: 35px; height: 35px; object-fit: contain; border-radius: 3px; }
        .logo-placeholder { font-size: 18px; }
        .company-amh { font-size: 9px; font-weight: bold; color: #1a237e; }
        .company-eng { font-size: 8px; font-weight: bold; color: #1a237e; }
        .doc-info { text-align: right; font-size: 6px; }
        
        .sheet-title {
            text-align: center; font-size: 10px; font-weight: bold;
            border: 1.5px solid #333; padding: 2px; margin: 2px 0; background: #f5f5f5;
        }
        
        .info-row { padding: 1px 0; font-size: 7px; }
        .info-row strong { display: inline-block; width: 90px; font-size: 6.5px; }
        
        .data-table { width: 100%; border-collapse: collapse; font-size: 6px; margin: 3px 0; }
        .data-table th {
            background: #1a237e; color: white; padding: 2px;
            font-size: 5.5px; text-align: center; border: 0.5px solid #333;
        }
        .data-table td { padding: 1.5px 2px; border: 0.5px solid #333; text-align: center; vertical-align: middle; }
        .data-table .text-left { text-align: left; }
        .data-table .text-right { text-align: right; }
        .data-table .item-header td { background: #1a237e; color: white; font-weight: bold; font-size: 6.5px; }
        .data-table .desc-header td { background: #e8eaf6; font-weight: bold; font-size: 6px; }
        .data-table .total-row td { background: #e8f5e9; font-weight: bold; }
        .data-table .grand-total td { background: #002060; color: white; font-weight: bold; font-size: 7px; }
        
        .signatures { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .signatures td { text-align: center; padding: 2px; width: 33%; }
        .sig-line { border-top: 1px solid #333; padding-top: 2px; margin: 0 8px; font-size: 6.5px; font-weight: bold; }
        .sig-name { font-size: 7px; color: #1a237e; min-height: 10px; font-weight: bold; }
        .sig-date { font-size: 5.5px; color: #666; }
        
        .footer-text { text-align: center; font-size: 6.5px; color: #1a237e; font-style: italic; margin-top: 4px; font-weight: bold; }
        .print-btn { position: fixed; top: 8px; right: 8px; background: #1a237e; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: 600; z-index: 1000; }
        @media print { .print-btn { display: none !important; } }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ Print</button>

    @php
        $logoUrl = \App\Models\CompanySetting::getLogoUrl();
        $companyName = \App\Models\CompanySetting::get('company_name', 'TNT CONSTRUCTION AND TRADING');
        $project = $sheet->project;
        $grandTotalAll = 0;
    @endphp

    <div class="sheet">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if($logoUrl)<img src="{{ $logoUrl }}" class="logo-img" alt="Logo">@else<span class="logo-placeholder">🏗️</span>@endif
                </td>
                <td width="55%" style="text-align:center;">
                    <div class="company-amh">ቲኤንቲ ኮንስትራክሽንና ንግድ ሥራዎች</div>
                    <div class="company-eng">{{ $companyName }}</div>
                </td>
                <td width="30%" class="doc-info">
                    <strong>Sheet No:</strong> {{ $sheet->sheet_number }}<br>
                    <strong>Page:</strong> {{ $sheet->page_no ?? 1 }}
                </td>
            </tr>
        </table>

        <div class="sheet-title">TAKE OFF SHEET</div>

        <div class="info-row"><strong>Project:</strong> {{ $project->name ?? 'N/A' }}</div>
        <div class="info-row"><strong>Client:</strong> {{ $project->client_name ?? '_________________' }}</div>
        <div class="info-row"><strong>Contractor:</strong> {{ $companyName }}</div>
        <div class="info-row"><strong>Location:</strong> Addis Ababa &nbsp; <strong>Date:</strong> {{ optional($sheet->measurement_date)->format('d/m/Y') ?? date('d/m/Y') }}</div>
        <div class="info-row"><strong>Division:</strong> {{ $sheet->division ?? 'N/A' }}</div>

        @if($sheet->items->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th width="4%">Qty</th><th width="10%">L × W × H</th>
                    <th width="8%">Product</th><th width="28%">Description</th>
                    <th width="4%">Qty</th><th width="10%">L × W × H</th>
                    <th width="8%">Product</th><th width="28%">Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sheet->items as $item)
                    @php
                        $leftDescs = collect();
                        $rightDescs = collect();
                        foreach($item->descriptions as $desc) {
                            if($desc->side == 'left') $leftDescs->push($desc);
                            else $rightDescs->push($desc);
                        }
                        $maxRows = max($leftDescs->count(), $rightDescs->count());
                        $itemTotal = 0;
                    @endphp
                    
                    <tr class="item-header">
                        <td colspan="8">Item {{ $item->item_number }}: {{ $item->description ?? $item->boqItem->description ?? '' }}</td>
                    </tr>
                    
                    @for($i = 0; $i < $maxRows; $i++)
                        @php
                            $ld = $leftDescs->values()->get($i);
                            $rd = $rightDescs->values()->get($i);
                            $lms = $ld ? $ld->measurements : collect();
                            $rms = $rd ? $rd->measurements : collect();
                            $maxM = max($lms->count(), $rms->count(), 1);
                        @endphp
                        
                        <tr class="desc-header">
                            <td colspan="4" class="text-left">{{ $ld->description ?? '' }}</td>
                            <td colspan="4" class="text-left">{{ $rd->description ?? '' }}</td>
                        </tr>
                        
                        @for($m = 0; $m < $maxM; $m++)
                            @php
                                $lm = $lms->values()->get($m); 
                                $rm = $rms->values()->get($m);
                                if($lm){ $la=$lm->total_area_volume; $grandTotalAll+=$la; $itemTotal+=$la; }
                                if($rm){ $ra=$rm->total_area_volume; $grandTotalAll+=$ra; $itemTotal+=$ra; }
                                
                                $lDims = ''; $rDims = '';
                                if($lm){
                                    $lDims = number_format($lm->length, 2);
                                    if($lm->width != 1) $lDims .= '<br>' . number_format($lm->width, 2);
                                    if($lm->height_depth != 1) $lDims .= '<br>' . number_format($lm->height_depth, 2);
                                }
                                if($rm){
                                    $rDims = number_format($rm->length, 2);
                                    if($rm->width != 1) $rDims .= '<br>' . number_format($rm->width, 2);
                                    if($rm->height_depth != 1) $rDims .= '<br>' . number_format($rm->height_depth, 2);
                                }
                            @endphp
                            <tr>
                                @if($lm)
                                    <td class="text-right">{{ $lm->quantity_count }}</td>
                                    <td class="text-right">{!! $lDims !!}</td>
                                    <td class="text-right"><strong>{{ number_format($la ?? 0, 2) }}</strong></td>
                                    <td class="text-left">{{ $lm->description }}</td>
                                @else
                                    <td colspan="4"></td>
                                @endif
                                @if($rm)
                                    <td class="text-right">{{ $rm->quantity_count }}</td>
                                    <td class="text-right">{!! $rDims !!}</td>
                                    <td class="text-right"><strong>{{ number_format($ra ?? 0, 2) }}</strong></td>
                                    <td class="text-left">{{ $rm->description }}</td>
                                @else
                                    <td colspan="4"></td>
                                @endif
                            </tr>
                        @endfor
                    @endfor
                    
                    <tr class="total-row">
                        <td colspan="3" class="text-right"><strong>Item {{ $item->item_number }} Total:</strong></td>
                        <td class="text-right"><strong>{{ number_format($itemTotal, 2) }}</strong></td>
                        <td colspan="4"></td>
                    </tr>
                    <tr><td colspan="8" style="height:2px;border:none;"></td></tr>
                @endforeach
                
                <tr class="grand-total">
                    <td colspan="3" class="text-right"><strong>GRAND TOTAL:</strong></td>
                    <td class="text-right"><strong>{{ number_format($grandTotalAll, 2) }}</strong></td>
                    <td colspan="4"></td>
                </tr>
            </tbody>
        </table>
        
        <table class="signatures">
            <tr>
                <td><div class="sig-name">{{ $sheet->measured_by ?? '' }}</div><div class="sig-line">Measured By</div><div class="sig-date">{{ optional($sheet->measurement_date)->format('d-m-Y') ?? '' }}</div></td>
                <td><div class="sig-name">{{ $sheet->verified_by ?? '' }}</div><div class="sig-line">Verified By</div></td>
                <td><div class="sig-name">{{ $sheet->approved_by ?? '' }}</div><div class="sig-line">Approved By</div></td>
            </tr>
        </table>
        @else
        <div style="text-align:center;padding:20px;color:#999;font-size:10px;">
            <p><strong>No measurement data found.</strong></p>
        </div>
        @endif

        <div class="footer-text">Striving to Build The Future!</div>
    </div>
</body>
</html>
