<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Certificate - {{ $ipc->ipc_number }}</title>
    <style>
        @page { size: A4; margin: 15mm 20mm; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.5; }
        
        .letterhead {
            border-bottom: 3px solid #1a237e;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .company-name { font-size: 18px; font-weight: bold; color: #1a237e; text-transform: uppercase; }
        .company-details { font-size: 8px; color: #666; text-align: right; }
        
        .cert-title {
            text-align: center; border: 2px solid #1a237e; padding: 10px; margin: 20px 0; background: #f5f5f5;
        }
        .cert-title h2 { margin: 0; color: #1a237e; font-size: 14px; letter-spacing: 2px; }
        .cert-title .cert-number { font-size: 10px; color: #666; }
        
        .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .info-table td { padding: 5px 8px; border: 1px solid #ddd; }
        .info-table .label { background: #f5f5f5; font-weight: bold; width: 150px; }
        
        .items-table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 9px; }
        .items-table th { background: #1a237e; color: white; padding: 6px; font-size: 8px; text-align: center; }
        .items-table td { padding: 5px; border: 1px solid #ddd; text-align: center; }
        .items-table .text-left { text-align: left; }
        .items-table .text-right { text-align: right; }
        .items-table .total-row { font-weight: bold; background: #e8eaf6; }
        
        .calc-table { width: 55%; float: right; border-collapse: collapse; margin: 15px 0; }
        .calc-table td { padding: 5px 10px; border: 1px solid #ddd; }
        .calc-table .label { font-weight: bold; }
        .calc-table .amount { text-align: right; font-weight: bold; }
        .calc-table .total { background: #1a237e; color: white; font-size: 12px; }
        
        .amount-words {
            border: 1px solid #1a237e; padding: 10px; margin: 15px 0;
            font-style: italic; font-size: 10px; background: #fffde7;
        }
        
        .signatures { margin-top: 50px; display: flex; justify-content: space-between; }
        .sig-box { text-align: center; width: 30%; }
        .sig-line { border-top: 1px solid #333; margin-top: 50px; padding-top: 8px; font-size: 9px; }
        .sig-name { font-weight: bold; font-size: 10px; color: #1a237e; margin-bottom: 2px; }
        .sig-date { font-size: 7px; color: #666; }
        
        .stamp {
            position: absolute; right: 50px; top: 350px;
            border: 3px solid #28a745; color: #28a745; padding: 8px 15px;
            border-radius: 50%; font-weight: bold; font-size: 12px;
            transform: rotate(-15deg); opacity: 0.6;
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
            Certificate No: {{ $ipc->ipc_number }} | 
            Payment No: {{ $ipc->issue_number ?? $ipc->payment_number ?? '01' }} |
            Date: {{ optional($ipc->ipc_date)->format('F d, Y') ?? date('F d, Y') }}
        </div>
    </div>
    
    <!-- Information Table -->
    <table class="info-table">
        <tr>
            <td class="label">Project:</td>
            <td colspan="3">{{ $ipc->project->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Subcontractor:</td>
            <td>{{ $ipc->subcontractor->name ?? 'N/A' }}</td>
            <td class="label">Period:</td>
            <td>
                {{ optional($ipc->period_start_date)->format('M d, Y') ?? 'N/A' }} - 
                {{ optional($ipc->period_end_date)->format('M d, Y') ?? 'N/A' }}
            </td>
        </tr>
        <tr>
            <td class="label">Status:</td>
            <td><strong>{{ strtoupper($ipc->status) }}</strong></td>
            <td class="label">Retention:</td>
            <td>{{ $ipc->retention_percentage ?? 5 }}%</td>
        </tr>
    </table>
    
    <!-- Work Items Table -->
    <h4 style="color:#1a237e;margin:15px 0 5px;">WORK EXECUTED</h4>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:5%;">S.No</th>
                <th style="width:35%;">Description</th>
                <th style="width:8%;">Unit</th>
                <th style="width:10%;">Contract Qty</th>
                <th style="width:10%;">Rate</th>
                <th style="width:10%;">Previous</th>
                <th style="width:10%;">Current</th>
                <th style="width:12%;">Amount (ETB)</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($ipc->ipcItems as $index => $item)
            @php 
                $amount = $item->current_amount ?? ($item->current_quantity * ($item->boqItem->unit_rate ?? 0)); 
                $total += $amount; 
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ $item->boqItem->description ?? $item->description ?? 'N/A' }}</td>
                <td>{{ $item->unit ?? $item->boqItem->unit ?? '-' }}</td>
                <td class="text-right">{{ number_format($item->contract_quantity, 2) }}</td>
                <td class="text-right">{{ number_format($item->contract_rate ?? $item->boqItem->unit_rate ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($item->previous_quantity, 2) }}</td>
                <td class="text-right">{{ number_format($item->current_quantity, 2) }}</td>
                <td class="text-right">{{ number_format($amount, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="7" style="text-align:right;">TOTAL WORK DONE:</td>
                <td class="text-right">{{ number_format($total, 2) }}</td>
            </tr>
        </tbody>
    </table>
    
    <!-- Financial Calculation -->
    <div class="clearfix">
        <table class="calc-table">
            @php
                $netAmount = $total;
                $vatPct = 15;
                $vatAmount = $netAmount * ($vatPct/100);
                $grossAmount = $netAmount + $vatAmount;
                $retPct = $ipc->retention_percentage ?? 5;
                $retAmount = $netAmount * ($retPct/100);
                $prevPayment = $ipc->total_previous_amount ?? 0;
                $totalDeductions = $prevPayment + $retAmount;
                $netDue = $grossAmount - $totalDeductions;
            @endphp
            <tr>
                <td class="label">A. Net Work Done</td>
                <td class="amount">{{ number_format($netAmount, 2) }}</td>
            </tr>
            <tr>
                <td class="label">B. VAT ({{ $vatPct }}%)</td>
                <td class="amount">{{ number_format($vatAmount, 2) }}</td>
            </tr>
            <tr>
                <td class="label">C. Gross Amount (A+B)</td>
                <td class="amount">{{ number_format($grossAmount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2" style="background:#f5f5f5;font-weight:bold;">DEDUCTIONS:</td>
            </tr>
            <tr>
                <td class="label">D1. Previous Payment</td>
                <td class="amount text-danger">- {{ number_format($prevPayment, 2) }}</td>
            </tr>
            <tr>
                <td class="label">D2. Retention ({{ $retPct }}%)</td>
                <td class="amount text-danger">- {{ number_format($retAmount, 2) }}</td>
            </tr>
            <tr>
                <td class="label">D. Total Deductions</td>
                <td class="amount text-danger">- {{ number_format($totalDeductions, 2) }}</td>
            </tr>
            <tr class="total">
                <td>NET SUM DUE (C-D)</td>
                <td class="amount">{{ number_format($netDue, 2) }} ETB</td>
            </tr>
        </table>
    </div>
    
    <!-- Amount in Words -->
    <div class="amount-words" style="clear:both; margin-top:120px;">
        <strong>Amount in Words:</strong> 
        @php
            function numberToWords($num) {
                if ($num <= 0) return 'Zero';
                $f = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
                return ucfirst($f->format($num));
            }
        @endphp
        {{ numberToWords($netDue) }} Ethiopian Birr Only
    </div>
    
    <!-- Stamp -->
    @if($ipc->status == 'approved')
    <div class="stamp">APPROVED</div>
    @endif
    
    <!-- Signatures with ACTUAL NAMES from workflow -->
    <div class="signatures">
        <!-- Prepared By -->
        <div class="sig-box">
            @if($ipc->prepared_by)
                <div class="sig-name">👤 {{ $ipc->prepared_by }}</div>
            @endif
            <div class="sig-line">Prepared By</div>
            @if($ipc->prepared_at)
                <div class="sig-date">{{ \Carbon\Carbon::parse($ipc->prepared_at)->format('M d, Y') }}</div>
            @endif
        </div>
        
        <!-- Checked By -->
        <div class="sig-box">
            @if($ipc->checked_by)
                <div class="sig-name">👤 {{ $ipc->checked_by }}</div>
            @endif
            <div class="sig-line">Checked By</div>
            @if($ipc->checked_at)
                <div class="sig-date">{{ \Carbon\Carbon::parse($ipc->checked_at)->format('M d, Y') }}</div>
            @endif
        </div>
        
        <!-- Approved By -->
        <div class="sig-box">
            @if($ipc->approved_by)
                <div class="sig-name">👤 {{ $ipc->approved_by }}</div>
            @endif
            <div class="sig-line">Approved By</div>
            @if($ipc->approved_at)
                <div class="sig-date">{{ \Carbon\Carbon::parse($ipc->approved_at)->format('M d, Y') }}</div>
            @endif
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        Generated by Construction Management System | This is a computer-generated document | {{ date('F d, Y H:i') }}
    </div>
</body>
</html>
