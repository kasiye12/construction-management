<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Certificate - {{ $ipc->ipc_number }}</title>
    <style>
        @page { size: A4; margin: 15mm 18mm; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #1a1a1a; line-height: 1.5; }
        
        .letterhead {
            border-bottom: 3px solid #1a237e; padding-bottom: 12px; margin-bottom: 18px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .company-name { font-size: 18px; font-weight: bold; color: #1a237e; text-transform: uppercase; }
        .company-details { font-size: 8px; color: #555; text-align: right; }
        
        .cert-title {
            text-align: center; border: 2px solid #1a237e; padding: 10px;
            margin: 15px 0; background: #f8f9fa;
        }
        .cert-title h2 { margin: 0; color: #1a237e; font-size: 14px; letter-spacing: 2px; }
        .cert-number { font-size: 9px; color: #666; margin-top: 3px; }
        
        .info-table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        .info-table td { padding: 5px 8px; border: 1px solid #ddd; }
        .info-label { background: #f5f5f5; font-weight: bold; width: 130px; font-size: 9px; }
        
        .items-table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 9px; }
        .items-table th { background: #1a237e; color: white; padding: 6px; font-size: 8px; text-align: center; text-transform: uppercase; }
        .items-table td { padding: 4px 5px; border: 1px solid #ddd; }
        .items-table .text-left { text-align: left; }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }
        .items-table .total-row { font-weight: bold; background: #e8eaf6; }
        
        .calc-box {
            border: 2px solid #1a237e; padding: 10px; margin: 15px 0;
            float: right; width: 280px;
        }
        .calc-box table { width: 100%; font-size: 9px; }
        .calc-box td { padding: 3px 5px; }
        .calc-box .total-row td { font-weight: bold; font-size: 11px; border-top: 2px solid #1a237e; padding-top: 5px; }
        
        .amount-words {
            clear: both; border: 1px solid #f59e0b; background: #fffde7;
            padding: 10px; margin: 15px 0; font-style: italic; font-size: 10px;
        }
        
        /* SIGNATURES - CLEARLY SHOWING WHO DID WHAT */
        .signatures {
            margin-top: 40px; display: flex; justify-content: space-between; gap: 15px;
        }
        .sig-box {
            flex: 1; text-align: center;
            border: 1px solid #ddd; padding: 15px 10px; border-radius: 8px;
            background: #fafafa;
        }
        .sig-title {
            font-size: 8px; text-transform: uppercase; color: #666;
            letter-spacing: 1px; margin-bottom: 8px;
        }
        .sig-name {
            font-weight: 700; font-size: 11px; color: #1a237e;
            min-height: 16px; padding: 4px 0;
        }
        .sig-name.filled { color: #10b981; }
        .sig-line {
            border-top: 1px solid #999; margin: 8px 20px 0;
            padding-top: 4px; font-size: 7px; color: #999;
        }
        .sig-date { font-size: 7px; color: #999; margin-top: 2px; }
        
        /* STATUS STAMP */
        .stamp {
            position: absolute; right: 40px; top: 300px;
            border: 3px solid #10b981; color: #10b981; padding: 6px 14px;
            border-radius: 50%; font-weight: 700; font-size: 11px;
            transform: rotate(-15deg); opacity: 0.6; letter-spacing: 1px;
        }
        .stamp-rejected {
            position: absolute; right: 40px; top: 300px;
            border: 3px solid #ef4444; color: #ef4444; padding: 6px 14px;
            border-radius: 50%; font-weight: 700; font-size: 11px;
            transform: rotate(-15deg); opacity: 0.6; letter-spacing: 1px;
        }
        
        .footer {
            position: fixed; bottom: 0; width: 100%; text-align: center;
            font-size: 7px; color: #999; border-top: 1px solid #ddd; padding-top: 5px;
        }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>
    <!-- Letterhead -->
    <div class="letterhead">
        <div>
            <div class="company-name">TNT Construction & Trading</div>
            <div style="font-size:9px;">General Contractor & Engineering Services</div>
        </div>
        <div class="company-details">
            <div>📞 +251-000-000000 | 📧 info@tntconstruction.com</div>
            <div>📍 Addis Ababa, Ethiopia | TIN: 000000000</div>
        </div>
    </div>
    
    <!-- Certificate Title -->
    <div class="cert-title">
        <h2>INTERIM PAYMENT CERTIFICATE</h2>
        <div class="cert-number">
            Certificate No: <strong>{{ $ipc->ipc_number }}</strong> | 
            Date: <strong>{{ optional($ipc->ipc_date)->format('F d, Y') ?? date('F d, Y') }}</strong> |
            Status: <strong>{{ strtoupper($ipc->status) }}</strong>
        </div>
    </div>
    
    <!-- Info -->
    <table class="info-table">
        <tr>
            <td class="info-label">Project:</td>
            <td colspan="3">{{ $ipc->project->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="info-label">Subcontractor:</td>
            <td>{{ $ipc->subcontractor->name ?? 'N/A' }}</td>
            <td class="info-label">Period:</td>
            <td>{{ optional($ipc->period_start_date)->format('M d, Y') ?? 'N/A' }} - {{ optional($ipc->period_end_date)->format('M d, Y') ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="info-label">Retention:</td>
            <td>{{ $ipc->retention_percentage ?? 5 }}%</td>
            <td class="info-label">VAT:</td>
            <td>15%</td>
        </tr>
    </table>
    
    <!-- Status Stamp -->
    @if($ipc->status == 'approved')<div class="stamp">APPROVED</div>@endif
    @if($ipc->status == 'paid')<div class="stamp" style="border-color:#3b82f6;color:#3b82f6;">PAID</div>@endif
    @if($ipc->status == 'rejected')<div class="stamp-rejected">REJECTED</div>@endif
    
    <!-- Work Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th>#</th><th>Description of Work</th><th>Unit</th>
                <th>Contract Qty</th><th>Rate</th><th>Previous</th>
                <th>Current</th><th>Amount (ETB)</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($ipc->ipcItems as $i => $item)
            @php $amt = $item->current_amount ?? ($item->current_quantity * ($item->boqItem->unit_rate ?? 0)); $total += $amt; @endphp
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td class="text-left">{{ $item->boqItem->description ?? 'N/A' }}</td>
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
    
    <!-- Calculation -->
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
            <tr><td>C. Gross Amount (A+B)</td><td class="text-right">{{ number_format($gross, 2) }}</td></tr>
            <tr><td colspan="2"><hr></td></tr>
            <tr><td>D1. Previous Payment</td><td class="text-right">-{{ number_format($prev, 2) }}</td></tr>
            <tr><td>D2. Retention ({{ $ipc->retention_percentage ?? 5 }}%)</td><td class="text-right">-{{ number_format($ret, 2) }}</td></tr>
            <tr><td>D. Total Deductions</td><td class="text-right">-{{ number_format($ded, 2) }}</td></tr>
            <tr class="total-row"><td>NET SUM DUE (C-D)</td><td class="text-right">{{ number_format($due, 2) }} ETB</td></tr>
        </table>
    </div>
    
    <!-- Amount in Words -->
    <div class="amount-words">
        <strong>Amount in Words:</strong> 
        {{ \App\Helpers\NumberToWordsHelper::convert($due) }} Ethiopian Birr Only
    </div>
    
    <!-- SIGNATURES - CLEARLY SHOWS WHO DID WHAT -->
    <div class="signatures" style="clear:both;">
        <!-- PREPARED BY -->
        <div class="sig-box">
            <div class="sig-title">Prepared By</div>
            <div class="sig-name {{ $ipc->prepared_by ? 'filled' : '' }}">
                @if($ipc->prepared_by)
                    👤 {{ $ipc->prepared_by }}
                @else
                    _________________
                @endif
            </div>
            @if($ipc->prepared_at)
                <div class="sig-date">📅 {{ \Carbon\Carbon::parse($ipc->prepared_at)->format('M d, Y') }}</div>
            @endif
        </div>
        
        <!-- CHECKED BY -->
        <div class="sig-box">
            <div class="sig-title">Checked By</div>
            <div class="sig-name {{ $ipc->checked_by ? 'filled' : '' }}">
                @if($ipc->checked_by)
                    👤 {{ $ipc->checked_by }}
                @else
                    _________________
                @endif
            </div>
            @if($ipc->checked_at)
                <div class="sig-date">📅 {{ \Carbon\Carbon::parse($ipc->checked_at)->format('M d, Y') }}</div>
            @endif
        </div>
        
        <!-- APPROVED BY -->
        <div class="sig-box">
            <div class="sig-title">Approved By</div>
            <div class="sig-name {{ $ipc->approved_by ? 'filled' : '' }}">
                @if($ipc->approved_by)
                    👤 {{ $ipc->approved_by }}
                @else
                    _________________
                @endif
            </div>
            @if($ipc->approved_at)
                <div class="sig-date">📅 {{ \Carbon\Carbon::parse($ipc->approved_at)->format('M d, Y') }}</div>
            @endif
        </div>
        
        <!-- PAID BY -->
        <div class="sig-box">
            <div class="sig-title">Payment Processed By</div>
            <div class="sig-name {{ $ipc->paid_by ? 'filled' : '' }}">
                @if($ipc->paid_by)
                    👤 {{ $ipc->paid_by }}
                @else
                    _________________
                @endif
            </div>
            @if($ipc->paid_at)
                <div class="sig-date">📅 {{ \Carbon\Carbon::parse($ipc->paid_at)->format('M d, Y') }}</div>
            @endif
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        This is a computer-generated document | Printed: {{ date('F d, Y H:i') }} | Construction Management System
    </div>
</body>
</html>
