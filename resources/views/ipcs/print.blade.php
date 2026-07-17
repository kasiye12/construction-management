<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Certificate - {{ $ipc->ipc_number }}</title>
    <style>
        @page { size: A4; margin: 12mm 15mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.5; }
        
        .letterhead {
            border-bottom: 3px solid #1a237e; padding-bottom: 12px; margin-bottom: 18px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .company-logo { font-size: 28px; font-weight: 800; color: #1a237e; }
        .company-info { text-align: right; font-size: 8px; color: #555; }
        
        .cert-title {
            text-align: center; border: 2px solid #1a237e; padding: 8px;
            margin: 15px 0; background: #f8f9fa;
        }
        .cert-title h1 { font-size: 14px; color: #1a237e; letter-spacing: 2px; margin: 0; }
        .cert-title .subtitle { font-size: 9px; color: #666; margin-top: 3px; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; border: 1px solid #ddd; margin: 12px 0; }
        .info-item { padding: 6px 10px; border-bottom: 1px solid #eee; display: flex; }
        .info-item .label { font-weight: 600; width: 110px; color: #555; font-size: 9px; text-transform: uppercase; }
        .info-item .value { flex: 1; font-size: 10px; }
        
        .items-table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 9px; }
        .items-table th { background: #1a237e; color: white; padding: 6px; font-size: 8px; text-transform: uppercase; }
        .items-table td { padding: 5px 6px; border: 1px solid #ddd; }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }
        .items-table .total-row { font-weight: bold; background: #e8eaf6; }
        
        .calc-box { border: 2px solid #1a237e; padding: 12px; margin: 15px 0; float: right; width: 280px; }
        .calc-box table { width: 100%; font-size: 9px; }
        .calc-box td { padding: 3px 5px; }
        .calc-box .total-row td { font-weight: bold; font-size: 11px; border-top: 2px solid #1a237e; padding-top: 5px; }
        
        .amount-words {
            clear: both; border: 1px solid #f59e0b; background: #fffde7;
            padding: 10px; margin: 15px 0; font-style: italic; font-size: 10px;
        }
        
        .signatures { margin-top: 50px; display: flex; justify-content: space-between; gap: 15px; }
        .sig-box {
            flex: 1; text-align: center; border: 1px solid #ddd;
            padding: 12px 8px; border-radius: 6px; background: #fafafa;
        }
        .sig-title {
            font-size: 7px; text-transform: uppercase; color: #888; letter-spacing: 1px; margin-bottom: 6px;
        }
        .sig-name { font-weight: 700; font-size: 10px; color: #1a237e; min-height: 15px; }
        .sig-line { border-top: 1px solid #999; margin: 8px 15px 0; padding-top: 4px; font-size: 7px; color: #999; }
        .sig-date { font-size: 7px; color: #999; margin-top: 2px; }
        
        .stamp {
            position: absolute; right: 30px; top: 280px;
            border: 3px solid #10b981; color: #10b981; padding: 6px 12px;
            border-radius: 50%; font-weight: 700; font-size: 11px;
            transform: rotate(-15deg); opacity: 0.6;
        }
        
        .footer {
            position: fixed; bottom: 0; left: 0; right: 0;
            text-align: center; font-size: 7px; color: #999;
            border-top: 1px solid #ddd; padding-top: 5px;
        }
        
        .print-btn {
            position: fixed; top: 20px; right: 20px;
            background: #1a237e; color: white; border: none;
            padding: 12px 25px; border-radius: 8px; cursor: pointer;
            font-size: 14px; font-weight: 600; z-index: 1000;
        }
        .print-btn:hover { background: #283593; }
        
        @media print { .print-btn { display: none !important; } }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ Print Certificate</button>

    <div class="letterhead">
        <div>
            <div class="company-logo">TNT</div>
            <div style="font-size:9px;color:#555;">CONSTRUCTION & TRADING</div>
        </div>
        <div class="company-info">
            <div>📞 +251-000-000000 | 📧 info@tntconstruction.com</div>
            <div>📍 Addis Ababa, Ethiopia | TIN: 000000000</div>
        </div>
    </div>

    <div class="cert-title">
        <h1>Interim Payment Certificate</h1>
        <div class="subtitle">
            Certificate No: <strong>{{ $ipc->ipc_number }}</strong> | 
            Date: <strong>{{ optional($ipc->ipc_date)->format('F d, Y') ?? date('F d, Y') }}</strong> |
            Status: <strong>{{ strtoupper($ipc->status) }}</strong>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-item"><span class="label">Project:</span><span class="value">{{ $ipc->project->name ?? 'N/A' }}</span></div>
        <div class="info-item"><span class="label">Subcontractor:</span><span class="value">{{ $ipc->subcontractor->name ?? 'N/A' }}</span></div>
        <div class="info-item"><span class="label">Period:</span><span class="value">{{ optional($ipc->period_start_date)->format('M d, Y') }} - {{ optional($ipc->period_end_date)->format('M d, Y') }}</span></div>
        <div class="info-item"><span class="label">Retention:</span><span class="value">{{ $ipc->retention_percentage ?? 5 }}% | VAT: 15%</span></div>
    </div>

    @if($ipc->status == 'approved')<div class="stamp">APPROVED</div>@endif
    @if($ipc->status == 'paid')<div class="stamp" style="border-color:#3b82f6;color:#3b82f6;">PAID</div>@endif

    <table class="items-table">
        <thead>
            <tr><th>#</th><th>Description of Work</th><th>Unit</th><th>Contract Qty</th><th>Rate</th><th>Previous</th><th>Current</th><th>Amount (ETB)</th></tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($ipc->ipcItems as $i => $item)
            @php $amt = $item->current_amount ?? ($item->current_quantity * ($item->boqItem->unit_rate ?? 0)); $total += $amt; @endphp
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $item->boqItem->description ?? 'N/A' }}</td>
                <td class="text-center">{{ $item->boqItem->unit ?? '-' }}</td>
                <td class="text-right">{{ number_format($item->contract_quantity, 2) }}</td>
                <td class="text-right">{{ number_format($item->contract_rate ?? $item->boqItem->unit_rate, 2) }}</td>
                <td class="text-right">{{ number_format($item->previous_quantity, 2) }}</td>
                <td class="text-right">{{ number_format($item->current_quantity, 2) }}</td>
                <td class="text-right">{{ number_format($amt, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="7" style="text-align:right;">TOTAL WORK DONE:</td>
                <td class="text-right">{{ number_format($total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @php
        $vat = $total * 0.15; $gross = $total + $vat;
        $ret = $total * (($ipc->retention_percentage ?? 5) / 100);
        $prev = $ipc->total_previous_amount ?? 0;
        $ded = $prev + $ret; $due = $gross - $ded;
    @endphp
    
    <div class="calc-box">
        <table>
            <tr><td>A. Net Work Done</td><td class="text-right">{{ number_format($total, 2) }}</td></tr>
            <tr><td>B. VAT (15%)</td><td class="text-right">{{ number_format($vat, 2) }}</td></tr>
            <tr><td>C. Gross Amount</td><td class="text-right">{{ number_format($gross, 2) }}</td></tr>
            <tr><td colspan="2"><hr></td></tr>
            <tr><td>D1. Previous Payment</td><td class="text-right">-{{ number_format($prev, 2) }}</td></tr>
            <tr><td>D2. Retention ({{ $ipc->retention_percentage ?? 5 }}%)</td><td class="text-right">-{{ number_format($ret, 2) }}</td></tr>
            <tr><td>D. Total Deductions</td><td class="text-right">-{{ number_format($ded, 2) }}</td></tr>
            <tr class="total-row"><td>NET SUM DUE</td><td class="text-right">{{ number_format($due, 2) }} ETB</td></tr>
        </table>
    </div>

    <div class="amount-words">
        <strong>Amount in Words:</strong> 
        {{ \App\Helpers\NumberToWordsHelper::convert($due) }} Ethiopian Birr Only
    </div>

    <!-- SIGNATURES - CLEARLY SHOWING WHO DID WHAT -->
    <div class="signatures">
        <div class="sig-box">
            @if($ipc->prepared_by)
                <div class="sig-name">👤 {{ $ipc->prepared_by }}</div>
            @endif
            <div class="sig-line">Prepared By</div>
            @if($ipc->prepared_at)
                <div class="sig-date">{{ \Carbon\Carbon::parse($ipc->prepared_at)->format('M d, Y') }}</div>
            @endif
        </div>
        <div class="sig-box">
            @if($ipc->checked_by)
                <div class="sig-name">👤 {{ $ipc->checked_by }}</div>
            @endif
            <div class="sig-line">Checked By</div>
            @if($ipc->checked_at)
                <div class="sig-date">{{ \Carbon\Carbon::parse($ipc->checked_at)->format('M d, Y') }}</div>
            @endif
        </div>
        <div class="sig-box">
            @if($ipc->approved_by)
                <div class="sig-name">👤 {{ $ipc->approved_by }}</div>
            @endif
            <div class="sig-line">Approved By</div>
            @if($ipc->approved_at)
                <div class="sig-date">{{ \Carbon\Carbon::parse($ipc->approved_at)->format('M d, Y') }}</div>
            @endif
        </div>
        <div class="sig-box">
            @if($ipc->paid_by)
                <div class="sig-name">👤 {{ $ipc->paid_by }}</div>
            @endif
            <div class="sig-line">Paid By</div>
            @if($ipc->paid_at)
                <div class="sig-date">{{ \Carbon\Carbon::parse($ipc->paid_at)->format('M d, Y') }}</div>
            @endif
        </div>
    </div>

    <div class="footer">
        This is a computer-generated document | Printed: {{ date('F d, Y H:i') }} | Construction Management System
    </div>
</body>
</html>
